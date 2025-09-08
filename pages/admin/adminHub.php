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
        <h1>Welcome <?= htmlspecialchars($_SESSION['username']) ?></h1>
        <div class="attention">
            <p>Notice: Le pagine Foglietto e Eventi sono ora disponibili.</p>
        </div>
        <h2>Available Pages:</h2>
        <?php
        $stmt = $db->prepare("SELECT * FROM pages");
        $stmt->execute();
        $pages = $stmt->fetchAll();
        
        foreach ($pages as $page) {
            $allowed = explode(',', $page['allowed_tags']);
            if (in_array($_SESSION['user_tag'], $allowed)) {
                echo "<a href='{$page['path']}'>{$page['page_name']}</a><br>";
            }
        }
        ?>
        <a href="logout.php">Logout</a>
        <div id="admin-username" style="display: none;">
    </main>
    <script src="assets/adminNav.js"></script>
</body>
</html>
