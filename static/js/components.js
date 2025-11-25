document.addEventListener('DOMContentLoaded', function() {
    // Calculate base path for resources relative to current page
    const calculateBasePath = () => {
        const currentPath = window.location.pathname;
        
        // Count the number of directory levels from the project root
        // Ignore the filename at the end and any empty segments
        const segments = currentPath.split('/').filter(segment => segment.length > 0);
        const fileName = segments[segments.length - 1];
        const hasExtension = fileName && fileName.includes('.');
        
        // If the last segment is a file, don't count it as a directory level
        const directoryLevels = hasExtension ? segments.length - 1 : segments.length;
        
        // If we're at the project root, return './'
        if (directoryLevels <= 0) {
            return './';
        }
        
        // For each directory level, we need to go up one level
        return '../'.repeat(directoryLevels);
    };
    
    const basePath = calculateBasePath();
    

    // Navigation HTML with dynamic paths
    const navHtml = `<nav class="main-nav">
        <div class="logo">
            <img src="${basePath}static/images/LogoNoBG.png" alt="Logo">
            <h1>Collaborazione Pastorale</h1>
        </div>
        <ul class="nav-list">
            <li><a href="${basePath}index.html"><i class="fas fa-home"></i>Home</a></li>
            <li><a href="${basePath}pages/foglietto/index.php"><i class="fas fa-file"></i>Foglietto</a></li>
            <li><a href="${basePath}pages/eventi.html"><i class="fas fa-calendar"></i>Eventi</a></li>
            <li><a href="${basePath}pages/chi-siamo.html"><i class="fas fa-users"></i>Chi Siamo</a></li>
            <li><a href="${basePath}pages/segreteria.html"><i class="fas fa-address-book"></i>Segreteria</a></li>
            <li><a href="${basePath}pages/contatti.html"><i class="fas fa-envelope"></i>Contatti</a></li>
        </ul>
        <a class="collapsible-list"><i class="fas fa-chevron-right collapsible-icon"></i>Parrochie</a>
        <div class="collapsible-content">
            <a class="card-link" href="${basePath}pages/tutteLeParrocchie.html">Tutte le parrochie</a>
            <a class="card-link" href="${basePath}pages/sangiorgio.html">San Giorgio di Nogaro</a>
            <a class="card-link" href="${basePath}pages/marano.html">Marano</a>
            <a class="card-link" href="${basePath}pages/porpetto.html">Porpetto</a>
            <a class="card-link" href="${basePath}pages/castello.html">Castello</a>
            <a class="card-link" href="${basePath}pages/carlino.html">Carlino</a>
            <a class="card-link" href="${basePath}pages/corgnolo.html">Corgnolo</a>
            <a class="card-link" href="${basePath}pages/portonogaro.html">Porto Nogaro</a>
            <a class="card-link" href="${basePath}pages/villanova.html">Villanova</a>
            <a class="card-link" href="${basePath}pages/zellina.html">Zellina</a>
        </div>
    </nav>
    <button class="nav-toggle-btn" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>`;

    // Footer HTML with dynamic paths
    const footerHtml = `<footer>
        <div class="footer-content">
            <div class="social-section">
                <h3>Seguici sui Social</h3>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com/c/CpdiSanGiorgiodiNogaro" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <div class="footer-section">
                    <h3>Link Utili</h3>
                    <ul>
                        <li><a href="${basePath}pages/privacy.html">Privacy Policy</a></li>
                        <li><a href="${basePath}pages/contatti.html">Contattaci</a></li>
                        <li><a href="${basePath}pages/chi-siamo.html">Chi Siamo</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-info">
                <p>&copy; ${new Date().getFullYear()} Collaborazione Pastorale San Giorgio di Nogaro. Tutti i diritti riservati.</p>
            </div>
        </div>
    </footer>`;

    // Insert navigation
    const navPlaceholder = document.getElementById('nav-placeholder');
    if (navPlaceholder) {
        navPlaceholder.innerHTML = navHtml;
        
        // Highlight current page in navigation
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        const navLinks = document.querySelectorAll('.nav-list a, .card-link');
        navLinks.forEach(link => {
            const linkPath = link.getAttribute('href');
            if (linkPath && linkPath.split('/').pop() === currentPage) {
                link.classList.add('active');
                // Also highlight parent list item if exists
                if (link.parentElement.tagName === 'LI') {
                    link.parentElement.classList.add('active');
                }
            }
        });

        // Update toggle functionality for all screen sizes
        const toggleBtn = document.querySelector('.nav-toggle-btn');
        const nav = document.querySelector('.main-nav');
        if (toggleBtn && nav) {
            toggleBtn.addEventListener('click', () => {
                nav.classList.toggle('active');
                document.body.classList.toggle('nav-open');
                toggleBtn.setAttribute('aria-expanded', nav.classList.contains('active'));
                
                // Change the icon based on state
                const icon = toggleBtn.querySelector('i');
                if (nav.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }
    }

    // Insert footer
    const footerPlaceholder = document.getElementById('footer-placeholder');
    if (footerPlaceholder) {
        footerPlaceholder.innerHTML = footerHtml;
    }

    // Collapsible functionality with animation and arrow rotation
    const collapsibleLists = document.querySelectorAll('.collapsible-list');
    collapsibleLists.forEach(list => {
        const content = list.nextElementSibling;
        const icon = list.querySelector('.collapsible-icon');
        
        // Initialize collapsed state
        content.style.display = 'none';
        content.style.overflow = 'hidden';
        content.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
        
        list.addEventListener('click', () => {
            const isExpanded = content.style.display === 'block';
            
            if (isExpanded) {
                // Collapse
                content.style.maxHeight = '0';
                content.style.opacity = '0';
                setTimeout(() => {
                    content.style.display = 'none';
                }, 300);
            } else {
                // Expand
                content.style.display = 'block';
                const contentHeight = content.scrollHeight;
                content.style.maxHeight = '0';
                content.style.opacity = '0';
                
                // Trigger reflow
                void content.offsetHeight;
                
                content.style.maxHeight = `${contentHeight}px`;
                content.style.opacity = '1';
            }
            
            // Rotate icon
            if (icon) {
                icon.style.transition = 'transform 0.3s ease';
                icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(90deg)';
            }
        });
    });

    // Close mobile menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-list a, .card-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            const nav = document.querySelector('.main-nav');
            const toggleBtn = document.querySelector('.nav-toggle-btn');
            if (nav && nav.classList.contains('active')) {
                nav.classList.remove('active');
                document.body.classList.remove('nav-open');
                toggleBtn.setAttribute('aria-expanded', 'false');
                
                // Reset the icon
                const icon = toggleBtn.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    });
});