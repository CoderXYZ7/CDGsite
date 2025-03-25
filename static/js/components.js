document.addEventListener('DOMContentLoaded', function() {
    // Navigation HTML
    const navHtml = `<nav class="main-nav">
        <div class="logo">
            <img src="../static/images/LogoNoBG.png" alt="Logo">
            <h1>Collaborazione Pastorale</h1>
        </div>
        <ul class="nav-list">
            <li><a href="index.html"><i class="fas fa-home"></i>Home</a></li>
            <li><a href="eventi.html"><i class="fas fa-calendar"></i>Eventi</a></li>
            <li><a href="chi-siamo.html"><i class="fas fa-users"></i>Chi Siamo</a></li>
            <li><a href="contatti.html"><i class="fas fa-envelope"></i>Contatti</a></li>
        </ul>
        <div class="attention">ATTENZIONE: ALCUNE PAGINE POTREBBERO CONTENERE DATI NON AGGIORNATI.</div>
        <a class="collapsible-list"><i class="fas fa-chevron-right collapsible-icon"></i>Parrochie</a>
        <div class="collapsible-content">
            <a class="card-link" href="www.cpsangiorgio.it/home.html">Tutte le parrochie</a>
            <a class="card-link" href="www.cpsangiorgio.it/sangiorgio">San Giorgio di Nogaro</a>
            <a class="card-link" href="www.cpsangiorgio.it/marano">Marano</a>
            <a class="card-link" href="www.cpsangiorgio.it/porpetto">Porpetto</a>
            <a class="card-link" href="www.cpsangiorgio.it/castello">Castello</a>
            <a class="card-link" href="www.cpsangiorgio.it/carlino">Carlino</a>
            <a class="card-link" href="www.cpsangiorgio.it/corgnolo">Corgnolo</a>
            <a class="card-link" href="www.cpsangiorgio.it/portonogaro">Porto Nogaro</a>
            <a class="card-link" href="www.cpsangiorgio.it/villanova">Villanova</a>
            <a class="card-link" href="www.cpsangiorgio.it/zellina">Zellina</a>
        </div>
    </nav>
    <button class="mobile-menu-btn" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>`;

    // Footer HTML
    const footerHtml = `<footer>
        <div class="footer-content">
            <div class="social-section">
                <h3>Seguici sui Social</h3>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <div class="footer-section">
                    <h3>Link Utili</h3>
                    <ul>
                        <li><a href="privacy.html">Privacy Policy</a></li>
                        <li><a href="contatti.html">Contattaci</a></li>
                        <li><a href="chi-siamo.html">Chi Siamo</a></li>
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
            if (link.getAttribute('href').split('/').pop() === currentPage) {
                link.classList.add('active');
                // Also highlight parent list item if exists
                if (link.parentElement.tagName === 'LI') {
                    link.parentElement.classList.add('active');
                }
            }
        });

        // Mobile menu functionality
        const menuBtn = document.querySelector('.mobile-menu-btn');
        const nav = document.querySelector('.main-nav');
        if (menuBtn && nav) {
            menuBtn.addEventListener('click', () => {
                nav.classList.toggle('active');
                menuBtn.setAttribute('aria-expanded', nav.classList.contains('active'));
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
            const menuBtn = document.querySelector('.mobile-menu-btn');
            if (nav && nav.classList.contains('active')) {
                nav.classList.remove('active');
                menuBtn.setAttribute('aria-expanded', 'false');
            }
        });
    });
});