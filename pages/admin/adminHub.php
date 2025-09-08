<?php 
include '../../config.php';
checkAuth();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../static/css/styles.css">
    <link rel="stylesheet" href="assets/adminHub.css">
</head>
<body>
    <div id="nav-placeholder"></div>
    <main class="main-wrapper">
        <div class="content">
            <section class="hero">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
                <p>This is your administrative hub. You can manage site content and settings from here.</p>
            </section>

            <div class="attention">
                <p><i class="fas fa-info-circle"></i> Notice: The Foglietto and Eventi pages are now available for use.</p>
            </div>

            <section class="card main-card">
                <div class="card-content">
                    <h3><i class="fas fa-th-large"></i> Available Modules</h3>
                    <div class="page-links">
                        <?php
                        $stmt = $db->prepare("SELECT * FROM pages");
                        $stmt->execute();
                        $pages = $stmt->fetchAll();
                        
                        foreach ($pages as $page) {
                            $allowed = explode(',', $page['allowed_tags']);
                            if (in_array($_SESSION['user_tag'], $allowed)) {
                                echo "<a href='{$page['path']}' class='button primary'>{$page['page_name']}</a>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </section>

            <a href="logout.php" class="button danger" style="margin-top: 1rem;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </main>
    <script src="assets/adminNav.js"></script>
</body>
</html>
