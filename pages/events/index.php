<?php
// Get list of PDF files
$pdf_files = glob("pdfs/*.pdf");

// Sort PDFs by filename (date) in descending order
usort($pdf_files, function($a, $b) {
    return strcmp(basename($b), basename($a));
});

// Get the most recent PDF
$most_recent_pdf = !empty($pdf_files) ? $pdf_files[0] : null;
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="icon" type="image/png" href="../../static/images/LogoNoBG.png" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Viewer</title>
    <link rel="stylesheet" href="../../static/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- PDF.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf_viewer.min.css">
    <style>
        /* Additional styles specific to PDF viewer */
        .pdf-container {
            display: flex;
            flex-direction: column;
            margin: 10px 0;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #ddd;
            background-color: #f5f5f5;
            transition: all 0.3s ease;
        }
        
        #pdf-viewer {
            width: 100%;
            height: 600px;
            overflow: auto;
            background-color: #525659;
            position: relative;
            transition: all 0.3s ease;
        }
        
        #pdf-canvas {
            margin: 0 auto;
            display: block;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
        
        .pdf-controls {
            display: flex;
            padding: 10px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            z-index: 100;
        }
        
        .pdf-controls button {
            padding: 8px 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .pdf-controls button:hover {
            background-color: var(--primary-color-dark);
        }
        
        .pdf-controls .page-info {
            margin: 0 15px;
            font-weight: 500;
        }
        
        .pdf-controls input {
            width: 60px;
            padding: 6px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .pdf-list table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .pdf-list th, .pdf-list td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e1e4e8;
        }
        
        .pdf-list th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            border: 6px solid #f3f3f3;
            border-top: 6px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        /* Fullscreen styles */
        .fullscreen-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background-color: rgba(0, 0, 0, 0.9);
            display: flex;
            flex-direction: column;
        }
        
        .fullscreen-container .pdf-controls {
            background-color: rgba(0, 0, 0, 0.7);
            border-bottom: 1px solid #444;
            color: white;
        }
        
        .fullscreen-container #pdf-viewer {
            flex: 1;
            height: calc(100vh - 65px);
            background-color: #333;
        }
        
        .exit-fullscreen-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 10000;
        }
        
        .exit-fullscreen-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        @media (max-width: 768px) {
            #pdf-viewer {
                height: 500px;
            }
            
            .pdf-controls {
                padding: 8px;
            }
            
            /* On mobile, auto fullscreen when viewing PDFs */
            body.mobile-view .pdf-container {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 9999;
                margin: 0;
                border-radius: 0;
                border: none;
            }
            
            body.mobile-view #pdf-viewer {
                height: calc(100vh - 65px);
            }
            
            body.mobile-view .pdf-controls {
                background-color: rgba(0, 0, 0, 0.7);
                border-bottom: 1px solid #444;
                color: white;
            }
        }
        
        @media (max-width: 480px) {
            .pdf-controls {
                padding: 5px;
            }
            
            .pdf-controls button {
                padding: 6px 10px;
                font-size: 0.9em;
            }
            
            .pdf-list th, .pdf-list td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Side Navigation -->
    <nav class="main-nav">
        <div class="logo">
            <img src="../../static/images/LogoNoBG.png" alt="Logo">
            <h1>PDF Viewer</h1>
        </div>
        
        <ul class="nav-list">
            <li><a href="index.php" class="active"><i class="fas fa-file-pdf"></i> Visualizzatore PDF</a></li>
            <li><a href="admin.php"><i class="fas fa-lock"></i> Area Admin</a></li>
        </ul>
    </nav>

    <main class="main-wrapper">
        <div class="content">
            <section class="hero">
                <h1>Visualizzatore Documenti PDF</h1>
                <p>Consulta i documenti in formato PDF</p>
            </section>

            <?php if ($most_recent_pdf): ?>
                <section class="main-card">
                    <div class="card-content">
                        <h3><i class="fas fa-file-pdf"></i> Documento più recente (<?php echo str_replace('.pdf', '', basename($most_recent_pdf)); ?>)</h3>
                        
                        <div class="pdf-container" id="pdf-container">
                            <div class="pdf-controls" id="pdf-controls">
                                <button id="prev-page"><i class="fas fa-chevron-left"></i> Precedente</button>
                                <div class="page-info">
                                    Pagina <input type="number" id="current-page" min="1" value="1"> di <span id="page-count">0</span>
                                </div>
                                <button id="next-page">Successiva <i class="fas fa-chevron-right"></i></button>
                                <button id="zoom-in"><i class="fas fa-search-plus"></i> Zoom In</button>
                                <button id="zoom-out"><i class="fas fa-search-minus"></i> Zoom Out</button>
                                <button id="fullscreen-btn" class="desktop-only"><i class="fas fa-expand"></i> Schermo intero</button>
                            </div>
                            <div id="pdf-viewer">
                                <div class="loading-spinner" id="loading-spinner"></div>
                                <canvas id="pdf-canvas"></canvas>
                            </div>
                            <button class="exit-fullscreen-btn" id="exit-fullscreen-btn" style="display: none;"><i class="fas fa-times"></i></button>
                        </div>
                        
                        <div class="section-footer">
                            <a href="<?php echo $most_recent_pdf; ?>" download class="button primary">
                                <i class="fas fa-download"></i> Scarica Documento
                            </a>
                        </div>
                    </div>
                </section>
            <?php else: ?>
                <section class="main-card">
                    <div class="card-content">
                        <p>Nessun documento PDF disponibile al momento.</p>
                    </div>
                </section>
            <?php endif; ?>

            <section class="main-card">
                <div class="card-content">
                    <h3><i class="fas fa-list"></i> Tutti i Documenti</h3>
                    <?php if (!empty($pdf_files)): ?>
                        <div class="pdf-list">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pdf_files as $pdf): ?>
                                        <?php $file_name = basename($pdf); ?>
                                        <tr>
                                            <td><?php echo str_replace('.pdf', '', $file_name); ?></td>
                                            <td>
                                                <a href="javascript:void(0)" onclick="loadPDF('<?php echo $pdf; ?>')" class="button primary small">
                                                    <i class="fas fa-eye"></i> Visualizza
                                                </a>
                                                <a href="<?php echo $pdf; ?>" download class="button primary small">
                                                    <i class="fas fa-download"></i> Scarica
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Nessun documento disponibile.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Mobile menu button -->
    <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- PDF.js Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>
        // Set up PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
        
        let pdfDoc = null,
            pageNum = 1,
            pageRendering = false,
            pageNumPending = null,
            scale = 1.0,
            canvas = document.getElementById('pdf-canvas'),
            ctx = canvas.getContext('2d'),
            isFullscreen = false,
            isMobile = window.innerWidth <= 768;
            
        // Check if device is mobile
        function checkMobile() {
            isMobile = window.innerWidth <= 768;
            if (isMobile) {
                document.body.classList.add('mobile-view');
            } else {
                document.body.classList.remove('mobile-view');
            }
        }
        
        // Run on page load and window resize
        window.addEventListener('load', checkMobile);
        window.addEventListener('resize', checkMobile);

        // Initial PDF to load when page loads (most recent PDF)
        <?php if ($most_recent_pdf): ?>
            window.addEventListener('load', function() {
                loadPDF('<?php echo $most_recent_pdf; ?>');
            });
        <?php endif; ?>

        /**
         * Load a PDF document
         */
        function loadPDF(url) {
            document.getElementById('loading-spinner').style.display = 'block';
            
            // Reset variables
            pageNum = 1;
            scale = 1.0;
            
            // Get document
            pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
                pdfDoc = pdfDoc_;
                document.getElementById('page-count').textContent = pdfDoc.numPages;
                document.getElementById('current-page').value = pageNum;
                document.getElementById('current-page').max = pdfDoc.numPages;
                
                // Initial/first page rendering
                renderPage(pageNum);
                
                // If mobile, automatically go fullscreen
                if (isMobile && !isFullscreen) {
                    toggleFullscreen();
                }
            }).catch(function(error) {
                console.error('Error loading PDF:', error);
                document.getElementById('loading-spinner').style.display = 'none';
                alert('Errore nel caricamento del PDF. Riprova più tardi.');
            });
        }

        /**
         * Render the page
         */
        function renderPage(num) {
            pageRendering = true;
            document.getElementById('loading-spinner').style.display = 'block';
            
            // Get page
            pdfDoc.getPage(num).then(function(page) {
                const viewport = page.getViewport({ scale: scale });
                
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                // Render PDF page into canvas context
                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                
                const renderTask = page.render(renderContext);
                
                // Wait for rendering to finish
                renderTask.promise.then(function() {
                    pageRendering = false;
                    document.getElementById('loading-spinner').style.display = 'none';
                    
                    if (pageNumPending !== null) {
                        // New page rendering is pending
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                }).catch(function(error) {
                    console.error('Error rendering page:', error);
                    pageRendering = false;
                    document.getElementById('loading-spinner').style.display = 'none';
                });
            });
            
            // Update page counters
            document.getElementById('current-page').value = num;
        }

        /**
         * If another page rendering in progress, wait until the rendering is
         * finished. Otherwise, execute rendering immediately.
         */
        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        /**
         * Display previous page
         */
        function onPrevPage() {
            if (pageNum <= 1) {
                return;
            }
            pageNum--;
            queueRenderPage(pageNum);
        }

        /**
         * Display next page
         */
        function onNextPage() {
            if (pageNum >= pdfDoc.numPages) {
                return;
            }
            pageNum++;
            queueRenderPage(pageNum);
        }

        /**
         * Zoom in
         */
        function zoomIn() {
            scale += 0.2;
            queueRenderPage(pageNum);
        }

        /**
         * Zoom out
         */
        function zoomOut() {
            if (scale <= 0.5) return;
            scale -= 0.2;
            queueRenderPage(pageNum);
        }
        
        /**
         * Toggle fullscreen mode
         */
        function toggleFullscreen() {
            const container = document.getElementById('pdf-container');
            const exitBtn = document.getElementById('exit-fullscreen-btn');
            
            if (!isFullscreen) {
                // Enter fullscreen
                container.classList.add('fullscreen-container');
                exitBtn.style.display = 'flex';
                isFullscreen = true;
                
                // Rerender the current page to adjust to new container size
                setTimeout(() => {
                    queueRenderPage(pageNum);
                }, 300);
                
            } else {
                // Exit fullscreen
                container.classList.remove('fullscreen-container');
                exitBtn.style.display = 'none';
                isFullscreen = false;
                
                // Rerender the current page to adjust to original container size
                setTimeout(() => {
                    queueRenderPage(pageNum);
                }, 300);
            }
        }

        // Button events
        document.getElementById('prev-page').addEventListener('click', onPrevPage);
        document.getElementById('next-page').addEventListener('click', onNextPage);
        document.getElementById('zoom-in').addEventListener('click', zoomIn);
        document.getElementById('zoom-out').addEventListener('click', zoomOut);
        document.getElementById('fullscreen-btn').addEventListener('click', toggleFullscreen);
        document.getElementById('exit-fullscreen-btn').addEventListener('click', toggleFullscreen);
        
        // Page input
        document.getElementById('current-page').addEventListener('change', function() {
            const num = parseInt(this.value);
            if (num >= 1 && num <= pdfDoc.numPages) {
                pageNum = num;
                queueRenderPage(pageNum);
            } else {
                this.value = pageNum;
            }
        });

        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.main-nav').classList.toggle('active');
        });
        
        // Handle keyboard events for navigation
        document.addEventListener('keydown', function(e) {
            if (!pdfDoc) return;
            
            if (e.key === 'ArrowRight' || e.key === ' ') {
                onNextPage();
            } else if (e.key === 'ArrowLeft') {
                onPrevPage();
            } else if (e.key === 'Escape' && isFullscreen) {
                toggleFullscreen();
            }
        });
    </script>
</body>
</html>