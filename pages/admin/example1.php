<?php
include 'config.php';
checkAuth();
checkTag('admin,student'); // Allowed tags
?>
<!DOCTYPE html>
<html>
<head>
    <title>Example Page 1</title>
</head>
<body>
    <div id="nav-placeholder"></div>
    <h1>Example Page 1</h1>
    <a href="hub.php">Back to Hub</a>
    <div id="admin-username" style="display: none;">
    <script src="adminNav.js"></script>
</body>
</html>