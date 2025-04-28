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
    <h1>Example Page 1</h1>
    <a href="hub.php">Back to Hub</a>
</body>
</html>