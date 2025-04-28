<?php 
include 'config.php';
checkAuth();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hub</title>
</head>
<body>
    <div id="nav-placeholder"></div>
    <h1>Welcome <?= htmlspecialchars($_SESSION['username']) ?></h1>
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
    <script src="adminNav.js"></script>
</body>
</html>