document.addEventListener('DOMContentLoaded', function() {
    // Navigation HTML
    const navHtml = `<nav class="main-nav">
        <div class="logo">
            <img src="https://placehold.co/60x60" alt="Logo">
            <h1>Collaborazione Pastorale</h1>
        </div>
        <ul class="nav-list">
            <li><a href="index.html"><i class="fas fa-home"></i>Home</a></li>
            <li><a href="eventi.html"><i class="fas fa-calendar"></i>Eventi</a></li>
            <li><a href="chi-siamo.html"><i class="fas fa-users"></i>Chi Siamo</a></li>
            <li><a href="contatti.html"><i class="fas fa-envelope"></i>Contatti</a></li>
        </ul>
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
                <p>&copy; 2025 Collaborazione Pastorale San Giorgio di Nogaro. Tutti i diritti riservati.</p>
            </div>
        </div>
    </footer>`;

    // Insert navigation
    const navPlaceholder = document.getElementById('nav-placeholder');
    if (navPlaceholder) {
        navPlaceholder.innerHTML = navHtml;
        
        // Highlight current page in navigation
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        const navLinks = document.querySelectorAll('.nav-list a');
        navLinks.forEach(link => {
            if (link.getAttribute('href').split('/').pop() === currentPage) {
                link.classList.add('active');
            }
        });

        // Mobile menu functionality
        const menuBtn = document.querySelector('.mobile-menu-btn');
        const nav = document.querySelector('.main-nav');
        menuBtn.addEventListener('click', () => {
            nav.classList.toggle('active');
        });
    }

    // Insert footer
    const footerPlaceholder = document.getElementById('footer-placeholder');
    if (footerPlaceholder) {
        footerPlaceholder.innerHTML = footerHtml;
    }
});
