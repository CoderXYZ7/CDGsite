<?php
require_once 'config.php';
require_once 'Database.php';
require_once 'Auth.php';

// CORS Headers - More secure configuration
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost"); // Be specific instead of *
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true"); // Important for sessions/cookies
header("Access-Control-Max-Age: 3600"); // Cache preflight response for 1 hour

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // 204 No Content is more appropriate for OPTIONS
    exit();
}

$db = new Database();
$auth = new Auth($db);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Add this to log CORS issues during development
error_log("Received request: " . $requestMethod . " " . $path);

try {
    // Route the request
    switch ($pathParts[0]) {
        case 'auth':
            handleAuthRoutes($pathParts, $requestMethod, $auth);
            break;
        case 'users':
            handleUserRoutes($pathParts, $requestMethod, $auth, $db);
            break;
        case 'tags':
            handleTagRoutes($pathParts, $requestMethod, $auth, $db);
            break;
        case 'ranks':
            handleRankRoutes($pathParts, $requestMethod, $auth, $db);
            break;
        case 'admin':
            handleAdminRoutes($pathParts, $requestMethod, $auth, $db);
            break;
        case 'connect':
            handleConnectionRoutes($pathParts, $requestMethod, $auth, $db);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleAuthRoutes($pathParts, $requestMethod, $auth) {
    if (count($pathParts) < 2) {
        http_response_code(404);
        echo json_encode(['error' => 'Auth endpoint not found']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    switch ($pathParts[1]) {
        case 'register':
            if ($requestMethod !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                return;
            }
            
            $userId = $auth->register(
                $data['username'],
                $data['email'],
                $data['password'],
                $data['activationCode']
            );
            
            echo json_encode(['userId' => $userId]);
            break;
            
        case 'login':
            if ($requestMethod !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                return;
            }
            
            $ip = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $token = $auth->login(
                $data['username'],
                $data['password'],
                $ip,
                $userAgent
            );
            
            echo json_encode($token);
            break;
            
        case 'logout':
            if ($requestMethod !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                return;
            }
            
            $token = getBearerToken();
            $auth->logout($token);
            echo json_encode(['success' => true]);
            break;
            
        case 'verify':
            if ($requestMethod !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                return;
            }
            
            $token = getBearerToken();
            $decoded = $auth->validateToken($token);
            
            if (!$decoded) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid token']);
                return;
            }
            
            echo json_encode(['valid' => true, 'userId' => $decoded['sub']]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Auth endpoint not found']);
    }
}

// Additional route handlers would be implemented similarly...

function getBearerToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}