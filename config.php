<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Development bypass for when database is not available
define('DB_BYPASS', getenv('DB_BYPASS') ?: true);

require_once __DIR__ . '/config_app.php';

session_start();

// Login database (MySQL)
$host = "cpsangiorgio.it";
$dbname = "ocpsange_login_system";
$username = "ocpsange_login_user";
$password = "vsHC2mK3BGYwx";

$db = null;
$db_error = null;

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $db_error = $e->getMessage();
    // Don't die here - let the application handle the error gracefully
}

// Events database (SQLite for now, can be switched to MySQL later)
$events_db_type = "sqlite"; // Change to "mysql" for remote DB

if ($events_db_type === "mysql") {
    // Use same MySQL for events
    $events_db = $db;
} elseif ($events_db_type === "sqlite") {
    $db_file = __DIR__ . "/events.db";
    try {
        $events_db = new PDO("sqlite:$db_file");
        $events_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Events database connection failed: " . $e->getMessage());
    }
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
