<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include '../config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Fetch events
        $stmt = $events_db->query("SELECT * FROM events ORDER BY date, time");
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($events);
        break;

    case 'POST':
        // Add new event
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['title']) || !isset($data['date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            break;
        }

        $stmt = $events_db->prepare("INSERT INTO events (title, description, date, time, place, event_type, end_date, end_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['date'],
            $data['time'] ?? '',
            $data['place'] ?? '',
            $data['event_type'] ?? 'single',
            $data['end_date'] ?? null,
            $data['end_time'] ?? null
        ]);

        echo json_encode(['id' => $events_db->lastInsertId()]);
        break;

    case 'PUT':
        // Update event
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            break;
        }

        $stmt = $events_db->prepare("UPDATE events SET title = ?, description = ?, date = ?, time = ?, place = ?, event_type = ?, end_date = ?, end_time = ? WHERE id = ?");
        $stmt->execute([
            $data['title'] ?? '',
            $data['description'] ?? '',
            $data['date'] ?? '',
            $data['time'] ?? '',
            $data['place'] ?? '',
            $data['event_type'] ?? 'single',
            $data['end_date'] ?? null,
            $data['end_time'] ?? null,
            $data['id']
        ]);

        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Delete event
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            break;
        }

        $stmt = $events_db->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$data['id']]);

        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
