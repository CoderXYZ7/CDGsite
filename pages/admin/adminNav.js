/**
 * Admin Navigation Script
 * Only loads on pages within the /admin/ directory
 * Requires:
 * - Font Awesome for icons
 * - #nav-placeholder element in HTML
 * - #admin-username element with current username (hidden)
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on an admin page
    const currentPath = window.location.pathname;
    if (!currentPath.includes('/admin/')) return;

    // Get username from hidden element (set by PHP)
    const usernameElement = document.getElementById('admin-username');
    const username = usernameElement ? usernameElement.textContent.trim() : 'Admin';

    // Calculate correct path for assets (works for any admin subdirectory depth)
    const pathSegments = currentPath.split('/').filter(Boolean);
    const adminDepth = pathSegments.indexOf('admin');
    const basePath = '../'.repeat(pathSegments.length - adminDepth - 1);

    // Admin Navigation HTML
    const navHtml = `
    <nav class="main-nav admin-nav">
        <!-- Logo Section -->
        <div class="logo">
            <img src="${basePath}static/images/LogoNoBG.png" alt="Admin Logo">
            <h1>Admin Dashboard</h1>
        </div>

        <!-- User Info Section -->
        <div class="admin-user-info">
            <span class="welcome-msg">Welcome, <strong>${username}</strong></span>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Main Navigation Links -->
        <ul class="nav-list">
            <li><a href="${basePath}admin/admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="${basePath}admin/adminHub.php"><i class="fas fa-cogs"></i> System Hub</a></li>
            <li><a href="${basePath}admin/adminFog.php"><i class="fas fa-file-pdf"></i> Foglietti</a></li>
            <li><a href="${basePath}admin/adminEve.php"><i class="fas fa-calendar-check"></i> Eventi</a></li>
            <li><a href="${basePath}admin/example1.php"><i class="fas fa-wrench"></i> Tools</a></li>
        </ul>

        <!-- Quick Actions Dropdown (mobile only) -->
        <div class="admin-quick-actions">
            <button class="quick-actions-btn">
                <i class="fas fa-bolt"></i> Quick Actions
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="quick-actions-menu">
                <a href="${basePath}admin/adminFog.php?action=new"><i class="fas fa-plus-circle"></i> New Foglietto</a>
                <a href="${basePath}admin/adminEve.php?action=new"><i class="fas fa-calendar-plus"></i> New Event</a>
                <a href="${basePath}admin/config.php"><i class="fas fa-sliders-h"></i> Configuration</a>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Toggle -->
    <button class="nav-toggle-btn" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>`;

    // Insert navigation into placeholder
    const navPlaceholder = document.getElementById('nav-placeholder');
    if (!navPlaceholder) return;
    
    navPlaceholder.innerHTML = navHtml;

    // Set active page highlight
    const currentPage = currentPath.split('/').pop() || 'admin.php';
    document.querySelectorAll('.nav-list a').forEach(link => {
        const linkPage = link.getAttribute('href').split('/').pop();
        if (linkPage === currentPage) {
            link.classList.add('active');
            link.parentElement.classList.add('active');
        }
    });

    // Mobile menu toggle functionality
    const toggleBtn = document.querySelector('.nav-toggle-btn');
    const nav = document.querySelector('.main-nav');
    if (toggleBtn && nav) {
        toggleBtn.addEventListener('click', () => {
            nav.classList.toggle('active');
            document.body.classList.toggle('nav-open');
            
            // Change icon
            const icon = toggleBtn.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
    }

    // Quick actions dropdown (mobile)
    const quickActionsBtn = document.querySelector('.quick-actions-btn');
    if (quickActionsBtn) {
        quickActionsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const menu = quickActionsBtn.nextElementSibling;
            const icon = quickActionsBtn.querySelector('.fa-chevron-down');
            
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            icon.style.transform = menu.style.display === 'block' ? 'rotate(180deg)' : 'rotate(0)';
        });

        // Close when clicking elsewhere
        document.addEventListener('click', () => {
            const menu = document.querySelector('.quick-actions-menu');
            if (menu) menu.style.display = 'none';
        });
    }

    // Close mobile menu when clicking a nav link
    document.querySelectorAll('.nav-list a').forEach(link => {
        link.addEventListener('click', () => {
            if (nav.classList.contains('active')) {
                nav.classList.remove('active');
                document.body.classList.remove('nav-open');
                toggleBtn.querySelector('i').classList.replace('fa-times', 'fa-bars');
            }
        });
    });

    // Add admin-specific styles dynamically
    const style = document.createElement('style');
    style.textContent = `
        .admin-nav {
            background-color: #2c3e50;
        }
        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
            padding: 0 20px;
        }
        .logout-btn {
            color: #ecf0f1;
            background-color: #e74c3c;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        .admin-quick-actions {
            display: none;
        }
        @media (max-width: 768px) {
            .admin-quick-actions {
                display: block;
                padding: 10px;
            }
            .quick-actions-btn {
                width: 100%;
                padding: 8px;
                background: #34495e;
                color: white;
                border: none;
                border-radius: 4px;
            }
            .quick-actions-menu {
                display: none;
                background: #34495e;
                border-radius: 4px;
                margin-top: 5px;
                overflow: hidden;
            }
            .quick-actions-menu a {
                display: block;
                padding: 8px 15px;
                color: white;
                border-bottom: 1px solid #2c3e50;
            }
        }
    `;
    document.head.appendChild(style);
});