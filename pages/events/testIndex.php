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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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

        .pdf-scroll-container {
            width: 100%;
            height: 100%;
            overflow: auto;
            touch-action: auto;
            cursor: default;
        }
        
        .pdf-scroll-container.zoomed {
            cursor: move;
        }
        
        #pdf-viewer {
            width: 100%;
            height: 600px;
            position: relative;
            background-color: #525659;
            transition: all 0.3s ease;
            overflow: hidden;
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
            background-color: var(--accent-color);
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
        
        .pdf-card {
            display: none;
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .pdf-card-content {
            transition: transform 0.3s ease;
        }
        
        .pdf-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .pdf-card-title {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .pdf-card-actions {
            display: flex;
            gap: 8px;
            position: absolute;
            right: -100px;
            top: 0;
            height: 100%;
            align-items: center;
            transition: right 0.3s ease;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.05));
        }
        
        .pdf-card.swiped .pdf-card-content {
            transform: translateX(-100px);
        }
        
        .pdf-card.swiped .pdf-card-actions {
            right: 0;
        }
        
        .pdf-card-skeleton {
            display: none;
            background: #f5f5f5;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            height: 60px;
            position: relative;
            overflow: hidden;
        }
        
        .pdf-card-skeleton::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, 
                rgba(255,255,255,0) 0%, 
                rgba(255,255,255,0.8) 50%, 
                rgba(255,255,255,0) 100%);
            animation: shimmer 1.5s infinite;
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
        
        .search-container {
            position: relative;
            margin-bottom: 15px;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        #clear-search {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
        }
        
        .pull-to-refresh {
            text-align: center;
            padding: 10px;
            color: var(--primary-color);
            display: none;
        }
        
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .mobile-menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
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

        /* Mobile button text and fullscreen button control */
        @media (max-width: 768px) {
            #pdf-viewer {
                height: 500px;
            }
            
            .pdf-controls {
                position: sticky;
                top: 0;
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding: 8px 5px;
                scrollbar-width: none;
            }
            
            .pdf-controls::-webkit-scrollbar {
                display: none;
            }
            
            .pdf-controls button {
                flex-shrink: 0;
                padding: 8px;
                min-width: 36px;
                justify-content: center;
            }
            
            /* Hide button text on mobile, only show icons */
            .pdf-controls button .button-text {
                display: none;
            }
            
            .page-info {
                flex-shrink: 0;
                margin: 0 5px;
            }
            
            /* Mobile-specific PDF list styles */
            .pdf-list table {
                display: none;
            }
            
            .pdf-card {
                display: block;
            }
            
            .pdf-card-actions .button-text {
                display: none;
            }
            
            .pdf-card-actions .button {
                padding: 8px;
                min-width: 36px;
                justify-content: center;
            }
            
            .pdf-card-skeleton {
                display: block;
            }
            
            .pull-to-refresh {
                display: block;
            }
            
            .mobile-menu-btn {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 997;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: var(--primary-color);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            }
            
            .main-nav {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .main-nav.active {
                transform: translateX(0);
            }
        }
        
        @media (min-width: 769px) {
            .pdf-list table {
                display: table;
            }
            
            .pdf-card {
                display: none;
            }
            
            .pdf-card-skeleton {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .pdf-controls {
                padding: 5px;
            }
            
            .pdf-controls button {
                padding: 6px;
                font-size: 0.9em;
            }
            
            .pdf-list th, .pdf-list td {
                padding: 8px;
            }
            
            .pdf-card {
                padding: 12px;
            }
        }
        
        /* Zoom indicator */
        .zoom-level {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 100;
            pointer-events: none;
        }
        
        .zoom-level.visible {
            opacity: 1;
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

    <div class="mobile-menu-overlay"></div>

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
                                <button id="prev-page"><i class="fas fa-chevron-left"></i> <span class="button-text">Precedente</span></button>
                                <div class="page-info">
                                    Pagina <input type="number" id="current-page" min="1" value="1"> di <span id="page-count">0</span>
                                </div>
                                <button id="next-page"><span class="button-text">Successiva</span> <i class="fas fa-chevron-right"></i></button>
                                <button id="zoom-in"><i class="fas fa-search-plus"></i> <span class="button-text">Zoom In</span></button>
                                <button id="zoom-out"><i class="fas fa-search-minus"></i> <span class="button-text">Zoom Out</span></button>
                                <button id="reset-zoom"><i class="fas fa-sync-alt"></i> <span class="button-text">Reset Zoom</span></button>
                                <button id="fullscreen-btn"><i class="fas fa-expand"></i> <span class="button-text">Schermo intero</span></button>
                            </div>
                            <div id="pdf-viewer">
                                <div class="loading-spinner" id="loading-spinner"></div>
                                <div class="pdf-scroll-container">
                                    <canvas id="pdf-canvas"></canvas>
                                </div>
                                <div class="zoom-level" id="zoom-level">Zoom: 100%</div>
                            </div>
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
                    <div class="search-container">
                        <input type="text" id="pdf-search" placeholder="Cerca documenti..." class="search-input">
                        <button id="clear-search"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="pull-to-refresh" id="pull-to-refresh">
                        <i class="fas fa-sync-alt"></i> Trascina verso il basso per aggiornare
                    </div>
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
                            
                            <!-- Mobile PDF List Cards -->
                            <div class="pdf-cards">
                                <?php foreach ($pdf_files as $pdf): ?>
                                    <?php $file_name = basename($pdf); ?>
                                    <div class="pdf-card">
                                        <div class="pdf-card-content">
                                            <div class="pdf-card-header">
                                                <div class="pdf-card-title"><?php echo str_replace('.pdf', '', $file_name); ?></div>
                                            </div>
                                        </div>
                                        <div class="pdf-card-actions">
                                            <a href="javascript:void(0)" onclick="loadPDF('<?php echo $pdf; ?>')" class="button primary small">
                                                <i class="fas fa-eye"></i> <span class="button-text">Visualizza</span>
                                            </a>
                                            <a href="<?php echo $pdf; ?>" download class="button primary small">
                                                <i class="fas fa-download"></i> <span class="button-text">Scarica</span>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Skeleton loading cards -->
                                <div class="pdf-card-skeleton"></div>
                                <div class="pdf-card-skeleton"></div>
                                <div class="pdf-card-skeleton"></div>
                            </div>
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
            isMobile = window.innerWidth <= 768,
            pdfViewer = document.getElementById('pdf-viewer'),
            zoomLevel = document.getElementById('zoom-level'),
            fullscreenBtn = document.getElementById('fullscreen-btn');
            
        // Touch and mouse variables for zoom and pan
        let initialPinchDistance = 0;
        let lastX = 0;
        let lastY = 0;
        let isDragging = false;
        let isMouseDown = false;
        let viewportTransform = {
            offsetX: 0,
            offsetY: 0,
            scale: 1.0
        };
        
        // Minimum and maximum zoom limits
        const MIN_SCALE = 1.0;  // 100%
        const MAX_SCALE = 5.0;  // 500%
        
        // Check if device is mobile
        function checkMobile() {
            isMobile = window.innerWidth <= 768;
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
            viewportTransform = {
                offsetX: 0,
                offsetY: 0,
                scale: 1.0
            };
            pdfViewer.scrollLeft = 0;
            pdfViewer.scrollTop = 0;
            
            // Get document
            pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
                pdfDoc = pdfDoc_;
                document.getElementById('page-count').textContent = pdfDoc.numPages;
                document.getElementById('current-page').value = pageNum;
                document.getElementById('current-page').max = pdfDoc.numPages;
                
                // Initial/first page rendering
                renderPage(pageNum);
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
                // Always apply the current scale to the page viewport
                const viewport = page.getViewport({ scale: scale });
                
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                // Center the canvas if smaller than viewport
                centerCanvasInViewport();
                
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
            // Reset scroll position and zoom when changing pages
            resetZoom();
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
            // Reset scroll position and zoom when changing pages
            resetZoom();
            queueRenderPage(pageNum);
        }

        /**
         * Zoom in
         */
        function zoomIn() {
            // Limit maximum zoom to 500%
            if (scale >= MAX_SCALE) return;
            
            const oldScale = scale;
            scale = Math.min(MAX_SCALE, scale + 0.2);
            
            // Re-render at the new scale
            queueRenderPage(pageNum);
            
            // Update zoom indicator
            updateZoomIndicator();
            updateCursorForZoom();
        }

        /**
         * Zoom out
         */
        function zoomOut() {
            // Limit minimum zoom to 100%
            if (scale <= MIN_SCALE) return;
            
            const oldScale = scale;
            scale = Math.max(MIN_SCALE, scale - 0.2);
            
            // Re-render at the new scale
            queueRenderPage(pageNum);
            
            // Update zoom indicator
            updateZoomIndicator();
            updateCursorForZoom();
        }
        
        /**
         * Reset zoom to 100%
         */
        function resetZoom() {
            scale = 1.0;
            viewportTransform.offsetX = 0;
            viewportTransform.offsetY = 0;
            viewportTransform.scale = 1.0;
            pdfViewer.scrollLeft = 0;
            pdfViewer.scrollTop = 0;
            
            queueRenderPage(pageNum);
            updateZoomIndicator();
            updateCursorForZoom();
        }
        
        /**
         * Update zoom level indicator
         */
        function updateZoomIndicator() {
            const percentage = Math.round(scale * 100);
            zoomLevel.textContent = `Zoom: ${percentage}%`;
            zoomLevel.classList.add('visible');
            
            // Hide zoom indicator after 2 seconds
            clearTimeout(window.zoomTimeout);
            window.zoomTimeout = setTimeout(() => {
                zoomLevel.classList.remove('visible');
            }, 2000);
        }
        
        /**
         * Update cursor based on zoom level
         */
        function updateCursorForZoom() {
            const scrollContainer = document.querySelector('.pdf-scroll-container');
            if (scale > 1) {
                scrollContainer.classList.add('zoomed');
                scrollContainer.style.cursor = isMouseDown ? 'grabbing' : 'move';
            } else {
                scrollContainer.classList.remove('zoomed');
                scrollContainer.style.cursor = 'default';
            }
        }
        
        /**
         * Center canvas in viewport
         */
        function centerCanvasInViewport() {
            const pdfViewerWidth = pdfViewer.clientWidth;
            const pdfViewerHeight = pdfViewer.clientHeight;
            
            // Calculate margins for centering
            const marginX = Math.max(0, (pdfViewerWidth - canvas.width) / 2);
            const marginY = Math.max(0, (pdfViewerHeight - canvas.height) / 2);
            
            // Apply margins to canvas
            canvas.style.margin = `${marginY}px ${marginX}px`;
        }
        
        /**
         * Toggle fullscreen mode
         */
        function toggleFullscreen() {
            const container = document.getElementById('pdf-container');
            
            if (!isFullscreen) {
                // Enter fullscreen
                container.classList.add('fullscreen-container');
                isFullscreen = true;
                
                // Change button icon to "exit fullscreen"
                fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i> <span class="button-text">Esci da schermo intero</span>';
                
                // Adjust viewport after transition
                setTimeout(() => {
                    centerCanvasInViewport();
                }, 300);
                
            } else {
                // Exit fullscreen
                container.classList.remove('fullscreen-container');
                isFullscreen = false;
                
                // Change button icon back to "fullscreen"
                fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i> <span class="button-text">Schermo intero</span>';
                
                // Adjust viewport after transition
                setTimeout(() => {
                    centerCanvasInViewport();
                }, 300);
            }
        }
        
        // Calculate distance between two touch points
        function getPinchDistance(e) {
            return Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
        }
        
        // Handle touch start event
        function handleTouchStart(e) {
            if (e.touches.length === 2) {
                // Pinch gesture starts
                e.preventDefault();
                initialPinchDistance = getPinchDistance(e);
            } else if (e.touches.length === 1) {
                // Single touch for panning
                lastX = e.touches[0].clientX;
                lastY = e.touches[0].clientY;
                isDragging = true;
            }
        }
        
        // Handle touch move event
        function handleTouchMove(e) {
            if (e.touches.length === 2) {
                // Pinch gesture (zooming)
                e.preventDefault();
                const currentDistance = getPinchDistance(e);
                
                if (initialPinchDistance > 0) {
                    // Calculate new scale factor
                    const pinchRatio = currentDistance / initialPinchDistance;
                    const newScale = Math.max(MIN_SCALE, Math.min(MAX_SCALE, scale * pinchRatio));
                    
                    // Only update if scale actually changed
                    if (newScale !== scale) {
                        scale = newScale;
                        queueRenderPage(pageNum);
                        updateZoomIndicator();
                        updateCursorForZoom();
                    }
                    
                    // Reset initial distance for smoother zooming
                    initialPinchDistance = currentDistance;
                }
            } else if (e.touches.length === 1 && isDragging && scale > 1) {
                // Single touch panning (only when zoomed in)
                const currentX = e.touches[0].clientX;
                const currentY = e.touches[0].clientY;
                
                // Calculate drag distance
                const deltaX = currentX - lastX;
                const deltaY = currentY - lastY;
                
                // Update scroll position
                pdfViewer.scrollLeft -= deltaX;
                pdfViewer.scrollTop -= deltaY;
                
                // Update last position
                lastX = currentX;
                lastY = currentY;
            }
        }
        
        // Handle touch end event
        function handleTouchEnd() {
            initialPinchDistance = 0;
            isDragging = false;
        }
        
        // Handle mouse down event for PC drag
        function handleMouseDown(e) {
            e.preventDefault();
            isMouseDown = true;
            lastX = e.clientX;
            lastY = e.clientY;
            
            // Only change cursor to grabbing if we're zoomed in
            if (scale > 1) {
                pdfViewer.style.cursor = 'grabbing';
            }
        }
        
        // Handle mouse move event for PC drag
        function handleMouseMove(e) {
            if (!isMouseDown) return;
            
            const deltaX = e.clientX - lastX;
            const deltaY = e.clientY - lastY;
            
            // Only enable drag functionality when zoomed in
            if (scale > 1) {
                pdfViewer.scrollLeft -= deltaX;
                pdfViewer.scrollTop -= deltaY;
            }
            
            lastX = e.clientX;
            lastY = e.clientY;
        }
        
        // Handle mouse up event
        function handleMouseUp() {
            isMouseDown = false;
            // Only set cursor to move if zoomed in, otherwise return to default
            pdfViewer.style.cursor = scale > 1 ? 'move' : 'default';
        }
        
        // Handle mouse leave event
        function handleMouseLeave() {
            if (isMouseDown) {
                isMouseDown = false;
                // Only set cursor to move if zoomed in, otherwise return to default
                pdfViewer.style.cursor = scale > 1 ? 'move' : 'default';
            }
        }
        
        // Setup mouse event listeners for PC
        pdfViewer.addEventListener('mousedown', handleMouseDown);
        pdfViewer.addEventListener('mousemove', handleMouseMove);
        pdfViewer.addEventListener('mouseup', handleMouseUp);
        pdfViewer.addEventListener('mouseleave', handleMouseLeave);
        
        // Setup touch event listeners for mobile
        pdfViewer.addEventListener('touchstart', handleTouchStart, { passive: false });
        pdfViewer.addEventListener('touchmove', handleTouchMove, { passive: false });
        pdfViewer.addEventListener('touchend', handleTouchEnd);
        pdfViewer.addEventListener('touchcancel', handleTouchEnd);

        // Handle mouse wheel zoom
        pdfViewer.addEventListener('wheel', function(e) {
            // Only zoom with Ctrl key pressed (standard zoom behavior)
            if (e.ctrlKey) {
                e.preventDefault();
                
                // Zoom in or out based on wheel direction
                if (e.deltaY < 0) {
                    // Wheel up - zoom in
                    if (scale < MAX_SCALE) {
                        scale = Math.min(MAX_SCALE, scale + 0.1);
                        queueRenderPage(pageNum);
                        updateZoomIndicator();
                        updateCursorForZoom();
                    }
                } else {
                    // Wheel down - zoom out
                    if (scale > MIN_SCALE) {
                        scale = Math.max(MIN_SCALE, scale - 0.1);
                        queueRenderPage(pageNum);
                        updateZoomIndicator();
                        updateCursorForZoom();
                    }
                }
            }
        }, { passive: false });

        // Button events
        document.getElementById('prev-page').addEventListener('click', onPrevPage);
        document.getElementById('next-page').addEventListener('click', onNextPage);
        document.getElementById('zoom-in').addEventListener('click', zoomIn);
        document.getElementById('zoom-out').addEventListener('click', zoomOut);
        document.getElementById('reset-zoom').addEventListener('click', resetZoom);
        document.getElementById('fullscreen-btn').addEventListener('click', toggleFullscreen);
        
        // Page input
        document.getElementById('current-page').addEventListener('change', function() {
            const num = parseInt(this.value);
            if (num >= 1 && num <= pdfDoc.numPages) {
                pageNum = num;
                // Reset zoom when changing pages
                resetZoom();
                queueRenderPage(pageNum);
            } else {
                this.value = pageNum;
            }
        });

        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.main-nav').classList.toggle('active');
            document.querySelector('.mobile-menu-overlay').classList.toggle('active');
        });

        document.querySelector('.mobile-menu-overlay').addEventListener('click', function() {
            document.querySelector('.main-nav').classList.remove('active');
            this.classList.remove('active');
        });
        
        // Search functionality
        const pdfSearch = document.getElementById('pdf-search');
        const clearSearch = document.getElementById('clear-search');

        pdfSearch.addEventListener('input', () => {
            const searchTerm = pdfSearch.value.toLowerCase();
            
            if (searchTerm) {
                clearSearch.style.display = 'block';
            } else {
                clearSearch.style.display = 'none';
            }
            
            // Search in desktop table
            document.querySelectorAll('.pdf-list tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
            
            // Search in mobile cards
            document.querySelectorAll('.pdf-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        clearSearch.addEventListener('click', () => {
            pdfSearch.value = '';
            clearSearch.style.display = 'none';
            
            document.querySelectorAll('.pdf-list tbody tr, .pdf-card').forEach(el => {
                el.style.display = '';
            });
        });
        
        // Pull-to-refresh functionality
        const pullToRefresh = document.getElementById('pull-to-refresh');
        let startY = 0;

        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0) {
                startY = e.touches[0].clientY;
            }
        }, {passive: true});

        document.addEventListener('touchmove', (e) => {
            const y = e.touches[0].clientY;
            if (window.scrollY === 0 && y - startY > 50) {
                pullToRefresh.style.display = 'block';
            }
        }, {passive: true});

        document.addEventListener('touchend', () => {
            if (pullToRefresh.style.display === 'block') {
                location.reload();
            }
        }, {passive: true});
        
        // Swipe functionality for mobile cards
        document.querySelectorAll('.pdf-card').forEach(card => {
            let startX, moveX;
            const content = card.querySelector('.pdf-card-content');
            const actions = card.querySelector('.pdf-card-actions');
            
            card.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
            }, {passive: true});
            
            card.addEventListener('touchmove', (e) => {
                moveX = e.touches[0].clientX;
                const diff = startX - moveX;
                
                if (diff > 0 && diff < 100) {
                    content.style.transform = `translateX(-${diff}px)`;
                    actions.style.right = `-${100 - diff}px`;
                }
            }, {passive: true});
            
            card.addEventListener('touchend', () => {
                if (startX - moveX > 50) {
                    card.classList.add('swiped');
                } else {
                    card.classList.remove('swiped');
                    content.style.transform = '';
                    actions.style.right = '';
                }
            }, {passive: true});
        });
        
        // Hide skeletons when content loads
        window.addEventListener('load', () => {
            document.querySelectorAll('.pdf-card-skeleton').forEach(skeleton => {
                skeleton.style.display = 'none';
            });
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
            } else if (e.key === '0' && e.ctrlKey) {
                // Ctrl+0 to reset zoom (common shortcut)
                e.preventDefault();
                resetZoom();
            } else if (e.key === '+' && e.ctrlKey) {
                // Ctrl++ to zoom in (common shortcut)
                e.preventDefault();
                zoomIn();
            } else if (e.key === '-' && e.ctrlKey) {
                // Ctrl+- to zoom out (common shortcut)
                e.preventDefault();
                zoomOut();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (pdfDoc) {
                centerCanvasInViewport();
            }
        });
    </script>
</body>
</html>