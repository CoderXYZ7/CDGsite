<?php
include '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// All user operations require auth + admin tag
if (empty($_SESSION['user_id'])) {
    http_response_code(401); echo json_encode(['error' => 'Non autorizzato']); exit;
}
if (empty($_SESSION['user_tag']) || $_SESSION['user_tag'] !== 'admin') {
    http_response_code(403); echo json_encode(['error' => 'Accesso negato']); exit;
}
if ($db === null) {
    http_response_code(503); echo json_encode(['error' => 'Database non disponibile']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ── GET: list users ───────────────────────────────────────────────────────────
if ($method === 'GET') {
    try {
        $users = $db->query("SELECT id, username, tag FROM users ORDER BY username")
                    ->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['error' => 'Errore nel caricamento utenti']);
    }
    exit;
}

// ── POST: create user ─────────────────────────────────────────────────────────
if ($method === 'POST') {
    $data     = json_decode(file_get_contents('php://input'), true) ?? [];
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    $tag      = $data['tag'] ?? 'student';

    if (!$username) { http_response_code(400); echo json_encode(['error' => 'Username obbligatorio']); exit; }
    if (!$password) { http_response_code(400); echo json_encode(['error' => 'Password obbligatoria']); exit; }
    if (!in_array($tag, ['admin', 'student'])) { http_response_code(400); echo json_encode(['error' => 'Ruolo non valido']); exit; }

    try {
        $stmt = $db->prepare("INSERT INTO users (username, password, tag) VALUES (?, ?, ?)");
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $tag]);
        echo json_encode(['success' => true, 'id' => (int)$db->lastInsertId()]);
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'Duplicate') !== false || stripos($msg, 'UNIQUE') !== false) {
            http_response_code(409); echo json_encode(['error' => 'Username già in uso']);
        } else {
            http_response_code(500); echo json_encode(['error' => 'Errore nella creazione utente']);
        }
    }
    exit;
}

// ── PUT: update user ──────────────────────────────────────────────────────────
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = (int)($data['id'] ?? 0);

    if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID obbligatorio']); exit; }

    // Prevent self-demotion
    if ($id === (int)$_SESSION['user_id'] && isset($data['tag']) && $data['tag'] !== 'admin') {
        http_response_code(400); echo json_encode(['error' => 'Non puoi modificare il tuo ruolo']); exit;
    }

    $updates = [];
    $params  = [];

    if (isset($data['tag'])) {
        if (!in_array($data['tag'], ['admin', 'student'])) {
            http_response_code(400); echo json_encode(['error' => 'Ruolo non valido']); exit;
        }
        $updates[] = 'tag = ?';
        $params[]  = $data['tag'];
    }

    if (!empty($data['password'])) {
        $updates[] = 'password = ?';
        $params[]  = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    if (empty($updates)) { http_response_code(400); echo json_encode(['error' => 'Nessun campo da aggiornare']); exit; }

    $params[] = $id;
    try {
        $stmt = $db->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?");
        $stmt->execute($params);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['error' => "Errore nell'aggiornamento utente"]);
    }
    exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = (int)($data['id'] ?? 0);

    if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID obbligatorio']); exit; }

    if ($id === (int)$_SESSION['user_id']) {
        http_response_code(400); echo json_encode(['error' => 'Non puoi eliminare il tuo account']); exit;
    }

    try {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['error' => "Errore nell'eliminazione utente"]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Metodo non consentito']);
