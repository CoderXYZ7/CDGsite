<?php
require_once __DIR__ . '/../config_app.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$subject = $data['subject'] ?? '';
$message = $data['message'] ?? '';

if (empty($name) || empty($email) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input. Please fill all fields correctly.']);
    exit();
}

$to = $contact_email;
$email_subject = "Nuovo messaggio dal sito: " . $subject;

$email_body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f4f4f4; padding: 10px; text-align: center; font-size: 1.2em; }
        .content { padding: 20px 0; }
        .footer { font-size: 0.9em; text-align: center; color: #777; }
        strong { color: #0056b3; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            Nuovo Messaggio dal Form di Contatto del Sito
        </div>
        <div class='content'>
            <p>Hai ricevuto un nuovo messaggio da:</p>
            <p><strong>Nome:</strong> " . htmlspecialchars($name) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
            <hr>
            <p><strong>Oggetto:</strong> " . htmlspecialchars($subject) . "</p>
            <p><strong>Messaggio:</strong></p>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
        </div>
        <div class='footer'>
            <p>Questo messaggio è stato inviato automaticamente dal sito della Collaborazione Pastorale.</p>
        </div>
    </div>
</body>
</html>
";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= 'From: <noreply@cpsangiorgio.it>' . "\r\n";
$headers .= 'Reply-To: ' . $email . "\r\n";

error_log("Contact form: attempting to send email to: " . $to);
if (mail($to, $email_subject, $email_body, $headers)) {
    error_log("Contact form: email sent successfully.");
    echo json_encode(['status' => 'success', 'message' => 'Messaggio inviato con successo!']);
} else {
    error_log("Contact form: mail() function failed. Check server's mail configuration.");
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Impossibile inviare il messaggio. Potrebbe esserci un problema di configurazione del server.']);
}
?>
