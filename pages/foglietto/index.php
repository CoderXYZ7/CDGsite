<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="icon" type="image/png" href="../../static/images/LogoNoBG.png" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Foglietto settimanale della Collaborazione Pastorale di San Giorgio di Nogaro. Consulta e scarica il foglietto delle domeniche e delle settimane precedenti.">
    <title>Foglietto Settimanale - Collaborazione Pastorale San Giorgio di Nogaro</title>
    <link rel="stylesheet" href="../../static/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- PDF.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf_viewer.min.css">
    <link rel="stylesheet" href="../../static/css/stylesViewer.css">
    <link rel="canonical" href="https://www.cpsangiorgio.it/pages/foglietto/">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-B2QXHE6MQX"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-B2QXHE6MQX');
    </script>
</head>
<body>
    <!-- Navigation placeholder -->
    <div id="nav-placeholder"></div>

    <main class="main-wrapper">
        <div class="content">
            <section class="hero">
                <h1>Visualizza qui il Foglietto settimanale</h1>
                <p>Consulta il foglietto di questa e delle settimane precedenti</p>
            </section>

            <!-- Viewer section: hidden until a PDF is selected -->
            <section class="main-card" id="viewer-section" style="display:none;">
                <div class="card-content">
                    <h3 id="viewer-title"><i class="fas fa-file-pdf"></i> </h3>

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
                        <button class="exit-fullscreen-btn" id="exit-fullscreen-btn" style="display: none;"><i class="fas fa-times"></i></button>
                    </div>

                    <div class="section-footer">
                        <a id="download-btn" href="#" download class="button primary">
                            <i class="fas fa-download"></i> Scarica Documento
                        </a>
                    </div>
                </div>
            </section>

            <!-- Shown when no PDFs are available -->
            <section class="main-card" id="no-pdfs-section" style="display:none;">
                <div class="card-content">
                    <p>Nessun documento PDF disponibile al momento.</p>
                </div>
            </section>

            <!-- PDF list -->
            <section class="main-card">
                <div class="card-content">
                    <h3><i class="fas fa-list"></i> Tutti i Documenti</h3>
                    <div id="pdf-list-loading" style="padding:1rem;color:#666">
                        <i class="fas fa-spinner fa-spin"></i> Caricamento…
                    </div>
                    <div class="pdf-list" id="pdf-list" style="display:none;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody id="pdf-list-body"></tbody>
                        </table>
                    </div>
                    <p id="pdf-list-empty" style="display:none;">Nessun documento disponibile.</p>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer placeholder -->
    <div id="footer-placeholder"></div>

    <!-- PDF.js Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script src="../../static/js/components.js"></script>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

        const FOG_API = '../../api/foglietto.php';

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
            zoomLevel = document.getElementById('zoom-level');

        let initialPinchDistance = 0,
            lastX = 0, lastY = 0,
            isDragging = false,
            isMouseDown = false,
            viewportTransform = { offsetX: 0, offsetY: 0, scale: 1.0 };

        const MIN_SCALE = 1.0, MAX_SCALE = 5.0;

        function checkMobile() { isMobile = window.innerWidth <= 768; }
        window.addEventListener('load', checkMobile);
        window.addEventListener('resize', checkMobile);

        // ── Foglietto list ────────────────────────────────────────────────────
        async function loadPdfList() {
            try {
                const res  = await fetch(FOG_API);
                const pdfs = await res.json();

                document.getElementById('pdf-list-loading').style.display = 'none';

                if (!pdfs.length) {
                    document.getElementById('pdf-list-empty').style.display = 'block';
                    document.getElementById('no-pdfs-section').style.display = 'block';
                    return;
                }

                const tbody = document.getElementById('pdf-list-body');
                pdfs.forEach(pdf => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${pdf.date}</td>
                        <td>
                            <a href="javascript:void(0)" onclick="selectPdf(${JSON.stringify(pdf).replace(/"/g, '&quot;')})"
                               class="button primary small">
                                <i class="fas fa-eye"></i> Visualizza
                            </a>
                            <a href="${pdf.url}" download="${pdf.filename}" class="button primary small">
                                <i class="fas fa-download"></i> Scarica
                            </a>
                        </td>`;
                    tbody.appendChild(tr);
                });

                document.getElementById('pdf-list').style.display = 'block';

                // Auto-load most recent
                selectPdf(pdfs[0]);

            } catch (e) {
                console.error('Errore caricamento foglietti:', e);
                document.getElementById('pdf-list-loading').textContent = 'Errore nel caricamento dei documenti.';
            }
        }

        function selectPdf(pdf) {
            document.getElementById('viewer-section').style.display = 'block';
            document.getElementById('viewer-title').innerHTML =
                `<i class="fas fa-file-pdf"></i> Documento più recente (${pdf.date})`;
            document.getElementById('download-btn').href         = pdf.url;
            document.getElementById('download-btn').download     = pdf.filename;
            loadPDF(pdf.url);
        }

        // ── PDF viewer ────────────────────────────────────────────────────────
        function loadPDF(url) {
            document.getElementById('loading-spinner').style.display = 'block';
            pageNum = 1;
            scale = 1.0;
            viewportTransform = { offsetX: 0, offsetY: 0, scale: 1.0 };
            pdfViewer.scrollLeft = 0;
            pdfViewer.scrollTop  = 0;

            pdfjsLib.getDocument(url).promise.then(function(doc) {
                pdfDoc = doc;
                document.getElementById('page-count').textContent   = pdfDoc.numPages;
                document.getElementById('current-page').value       = pageNum;
                document.getElementById('current-page').max         = pdfDoc.numPages;
                renderPage(pageNum);
            }).catch(function(err) {
                console.error('Errore caricamento PDF:', err);
                document.getElementById('loading-spinner').style.display = 'none';
            });
        }

        function renderPage(num) {
            pageRendering = true;
            document.getElementById('loading-spinner').style.display = 'block';
            pdfDoc.getPage(num).then(function(page) {
                const viewport = page.getViewport({ scale });
                canvas.height  = viewport.height;
                canvas.width   = viewport.width;
                centerCanvasInViewport();
                page.render({ canvasContext: ctx, viewport }).promise.then(function() {
                    pageRendering = false;
                    document.getElementById('loading-spinner').style.display = 'none';
                    if (pageNumPending !== null) {
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                }).catch(function() {
                    pageRendering = false;
                    document.getElementById('loading-spinner').style.display = 'none';
                });
            });
            document.getElementById('current-page').value = num;
        }

        function queueRenderPage(num) {
            if (pageRendering) { pageNumPending = num; } else { renderPage(num); }
        }

        function onPrevPage() {
            if (pageNum <= 1) return;
            pageNum--; resetZoom(); queueRenderPage(pageNum);
        }
        function onNextPage() {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++; resetZoom(); queueRenderPage(pageNum);
        }

        function zoomIn() {
            if (scale >= MAX_SCALE) return;
            scale = Math.min(MAX_SCALE, scale + 0.2);
            queueRenderPage(pageNum); updateZoomIndicator(); updateCursorForZoom();
        }
        function zoomOut() {
            if (scale <= MIN_SCALE) return;
            scale = Math.max(MIN_SCALE, scale - 0.2);
            queueRenderPage(pageNum); updateZoomIndicator(); updateCursorForZoom();
        }
        function resetZoom() {
            scale = 1.0;
            viewportTransform = { offsetX: 0, offsetY: 0, scale: 1.0 };
            pdfViewer.scrollLeft = 0; pdfViewer.scrollTop = 0;
            queueRenderPage(pageNum); updateZoomIndicator(); updateCursorForZoom();
        }

        function updateZoomIndicator() {
            zoomLevel.textContent = `Zoom: ${Math.round(scale * 100)}%`;
            zoomLevel.classList.add('visible');
            clearTimeout(window.zoomTimeout);
            window.zoomTimeout = setTimeout(() => zoomLevel.classList.remove('visible'), 2000);
        }
        function updateCursorForZoom() {
            const sc = document.querySelector('.pdf-scroll-container');
            if (scale > 1) { sc.classList.add('zoomed'); sc.style.cursor = isMouseDown ? 'grabbing' : 'move'; }
            else           { sc.classList.remove('zoomed'); sc.style.cursor = 'default'; }
        }
        function centerCanvasInViewport() {
            const mx = Math.max(0, (pdfViewer.clientWidth  - canvas.width)  / 2);
            const my = Math.max(0, (pdfViewer.clientHeight - canvas.height) / 2);
            canvas.style.margin = `${my}px ${mx}px`;
        }
        function toggleFullscreen() {
            const container = document.getElementById('pdf-container');
            const exitBtn   = document.getElementById('exit-fullscreen-btn');
            isFullscreen = !isFullscreen;
            container.classList.toggle('fullscreen-container', isFullscreen);
            exitBtn.style.display = isFullscreen ? 'flex' : 'none';
            setTimeout(centerCanvasInViewport, 300);
        }

        // Touch / mouse events (unchanged from original)
        function getPinchDistance(e) {
            return Math.hypot(e.touches[0].clientX - e.touches[1].clientX,
                              e.touches[0].clientY - e.touches[1].clientY);
        }
        function handleTouchStart(e) {
            if (e.touches.length === 2) { e.preventDefault(); initialPinchDistance = getPinchDistance(e); }
            else if (e.touches.length === 1) { lastX = e.touches[0].clientX; lastY = e.touches[0].clientY; isDragging = true; }
        }
        function handleTouchMove(e) {
            if (e.touches.length === 2) {
                e.preventDefault();
                const cur = getPinchDistance(e);
                if (initialPinchDistance > 0) {
                    const ns = Math.max(MIN_SCALE, Math.min(MAX_SCALE, scale * (cur / initialPinchDistance)));
                    if (ns !== scale) { scale = ns; queueRenderPage(pageNum); updateZoomIndicator(); updateCursorForZoom(); }
                    initialPinchDistance = cur;
                }
            } else if (e.touches.length === 1 && isDragging && scale > 1) {
                pdfViewer.scrollLeft -= e.touches[0].clientX - lastX;
                pdfViewer.scrollTop  -= e.touches[0].clientY - lastY;
                lastX = e.touches[0].clientX; lastY = e.touches[0].clientY;
            }
        }
        function handleTouchEnd()  { initialPinchDistance = 0; isDragging = false; }
        function handleMouseDown(e) { e.preventDefault(); isMouseDown = true; lastX = e.clientX; lastY = e.clientY; if (scale > 1) pdfViewer.style.cursor = 'grabbing'; }
        function handleMouseMove(e) {
            if (!isMouseDown) return;
            if (scale > 1) { pdfViewer.scrollLeft -= e.clientX - lastX; pdfViewer.scrollTop -= e.clientY - lastY; }
            lastX = e.clientX; lastY = e.clientY;
        }
        function handleMouseUp()    { isMouseDown = false; pdfViewer.style.cursor = scale > 1 ? 'move' : 'default'; }
        function handleMouseLeave() { if (isMouseDown) { isMouseDown = false; pdfViewer.style.cursor = scale > 1 ? 'move' : 'default'; } }

        pdfViewer.addEventListener('mousedown',  handleMouseDown);
        pdfViewer.addEventListener('mousemove',  handleMouseMove);
        pdfViewer.addEventListener('mouseup',    handleMouseUp);
        pdfViewer.addEventListener('mouseleave', handleMouseLeave);
        pdfViewer.addEventListener('touchstart', handleTouchStart, { passive: false });
        pdfViewer.addEventListener('touchmove',  handleTouchMove,  { passive: false });
        pdfViewer.addEventListener('touchend',   handleTouchEnd);
        pdfViewer.addEventListener('touchcancel',handleTouchEnd);
        pdfViewer.addEventListener('wheel', function(e) {
            if (!e.ctrlKey) return;
            e.preventDefault();
            if (e.deltaY < 0 && scale < MAX_SCALE) { scale = Math.min(MAX_SCALE, scale + 0.1); }
            else if (e.deltaY > 0 && scale > MIN_SCALE) { scale = Math.max(MIN_SCALE, scale - 0.1); }
            queueRenderPage(pageNum); updateZoomIndicator(); updateCursorForZoom();
        }, { passive: false });

        // Button events
        document.getElementById('prev-page').addEventListener('click', onPrevPage);
        document.getElementById('next-page').addEventListener('click', onNextPage);
        document.getElementById('zoom-in').addEventListener('click', zoomIn);
        document.getElementById('zoom-out').addEventListener('click', zoomOut);
        document.getElementById('reset-zoom').addEventListener('click', resetZoom);
        document.getElementById('fullscreen-btn').addEventListener('click', toggleFullscreen);
        document.getElementById('exit-fullscreen-btn').addEventListener('click', toggleFullscreen);
        document.getElementById('current-page').addEventListener('change', function() {
            const n = parseInt(this.value);
            if (n >= 1 && n <= pdfDoc.numPages) { pageNum = n; resetZoom(); queueRenderPage(pageNum); }
            else this.value = pageNum;
        });
        document.addEventListener('keydown', function(e) {
            if (!pdfDoc) return;
            if      (e.key === 'ArrowRight' || e.key === ' ') onNextPage();
            else if (e.key === 'ArrowLeft')                    onPrevPage();
            else if (e.key === 'Escape' && isFullscreen)       toggleFullscreen();
            else if (e.ctrlKey && e.key === '0') { e.preventDefault(); resetZoom(); }
            else if (e.ctrlKey && e.key === '+') { e.preventDefault(); zoomIn(); }
            else if (e.ctrlKey && e.key === '-') { e.preventDefault(); zoomOut(); }
        });
        window.addEventListener('resize', function() { if (pdfDoc) centerCanvasInViewport(); });

        // Init
        window.addEventListener('load', loadPdfList);
    </script>
</body>
</html>
