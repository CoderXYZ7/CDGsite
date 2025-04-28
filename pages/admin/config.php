<?php
session_start();
$host = "localhost";
$dbname = "tag_system";
$username = "tag_user";
$password = "secure_password";

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function checkTag($allowed_tags) {
    $user_tag = $_SESSION['user_tag'] ?? '';
    $allowed = explode(',', $allowed_tags);
    if (!in_array($user_tag, $allowed)) {
        header("Location: hub.php");
        exit();
    }
}
?>