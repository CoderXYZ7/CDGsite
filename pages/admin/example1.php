<?php
include '../../config.php';
checkAuth();
checkTag('admin,student'); // Allowed tags
?>
<!DOCTYPE html>
<html>
<head>
    <title>Example Page 1</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../static/css/styles.css">
</head>
<body>
    <div id="nav-placeholder"></div>
    <main class="main-wrapper">
        <h1>Example Page 1</h1>
        <a href="adminHub.php">Back to Hub</a>
    </main>
    
    <!-- Hidden username element for JS -->
    <div id="admin-username" style="display: none;"><?= htmlspecialchars($_SESSION['username']) ?></div>
    
    <!-- Load admin navigation script -->
    <script src="assets/adminNav.js"></script>
</body>
</html>
