<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$PDFS_DIR = __DIR__ . '/../pages/foglietto/pdfs/';
$method   = $_SERVER['REQUEST_METHOD'];

function requireAuth() {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Non autorizzato']);
        exit;
    }
}

// ── GET: public list ──────────────────────────────────────────────────────────
if ($method === 'GET') {
    if (!is_dir($PDFS_DIR)) { echo json_encode([]); exit; }

    $files = glob($PDFS_DIR . '*.pdf') ?: [];
    usort($files, fn($a, $b) => strcmp(basename($b), basename($a)));

    echo json_encode(array_values(array_map(fn($f) => [
        'filename' => basename($f),
        'date'     => substr(basename($f), 0, -4),
        'size'     => filesize($f),
        'url'      => '/pages/foglietto/pdfs/' . basename($f),
    ], $files)));
    exit;
}

requireAuth();

// ── POST: upload ──────────────────────────────────────────────────────────────
if ($method === 'POST') {
    if (empty($_FILES['pdf'])) {
        http_response_code(400); echo json_encode(['error' => 'Nessun file ricevuto']); exit;
    }

    $file      = $_FILES['pdf'];
    $date      = trim($_POST['date'] ?? '');
    $overwrite = !empty($_POST['overwrite']) && $_POST['overwrite'] === 'true';

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(400); echo json_encode(['error' => 'Data non valida (formato AAAA-MM-GG)']); exit;
    }

    $upload_errors = [
        UPLOAD_ERR_INI_SIZE  => 'File troppo grande (limite del server)',
        UPLOAD_ERR_FORM_SIZE => 'File troppo grande (limite del form)',
        UPLOAD_ERR_PARTIAL   => 'Caricamento parziale',
        UPLOAD_ERR_NO_FILE   => 'Nessun file caricato',
        UPLOAD_ERR_NO_TMP_DIR => 'Cartella temporanea mancante',
        UPLOAD_ERR_CANT_WRITE => 'Errore di scrittura su disco',
    ];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => $upload_errors[$file['error']] ?? 'Errore di caricamento']);
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if ($finfo->file($file['tmp_name']) !== 'application/pdf') {
        http_response_code(400); echo json_encode(['error' => 'Solo file PDF sono accettati']); exit;
    }

    if ($file['size'] > 10 * 1024 * 1024) {
        http_response_code(400); echo json_encode(['error' => 'Il file supera il limite di 10 MB']); exit;
    }

    if (!is_dir($PDFS_DIR)) mkdir($PDFS_DIR, 0755, true);

    $filename = $date . '.pdf';
    $dest     = $PDFS_DIR . $filename;

    if (file_exists($dest) && !$overwrite) {
        http_response_code(409);
        echo json_encode(['error' => 'Esiste già un foglietto per questa data', 'exists' => true]);
        exit;
    }

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        http_response_code(500); echo json_encode(['error' => 'Errore nel salvataggio del file']); exit;
    }

    echo json_encode(['success' => true, 'filename' => $filename]);
    exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $data     = json_decode(file_get_contents('php://input'), true) ?? [];
    $filename = basename($data['filename'] ?? '');

    if (!$filename || !preg_match('/^\d{4}-\d{2}-\d{2}\.pdf$/', $filename)) {
        http_response_code(400); echo json_encode(['error' => 'Nome file non valido']); exit;
    }

    $path = $PDFS_DIR . $filename;
    if (!file_exists($path)) {
        http_response_code(404); echo json_encode(['error' => 'File non trovato']); exit;
    }

    if (!unlink($path)) {
        http_response_code(500); echo json_encode(['error' => "Errore durante l'eliminazione"]); exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Metodo non consentito']);
