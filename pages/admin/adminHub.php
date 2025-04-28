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
</body>
</html>