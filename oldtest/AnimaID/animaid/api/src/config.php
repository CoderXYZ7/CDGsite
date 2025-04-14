<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'AnimaID');
define('DB_USER', 'root');
define('DB_PASS', '');

// JWT Configuration
define('JWT_SECRET', 'your-secret-key-here');
define('JWT_EXPIRE', 3600); // 1 hour
define('JWT_REFRESH_EXPIRE', 86400); // 24 hours

// API Settings
define('API_RATE_LIMIT', 100); // Requests per minute
define('ACTIVATION_CODE_EXPIRE', 86400); // 24 hours in seconds

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Require necessary libraries
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;