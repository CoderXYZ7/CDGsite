<?php
session_start();
$host = "cpsangiorgio.it";
$dbname = "ocpsange_login_system";
$username = "ocpsange_login_user";
$password = "vsHC2mK3BGYwx";

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
