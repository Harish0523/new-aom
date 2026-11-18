<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$name    = trim($input['name'] ?? '');
$email   = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone   = trim($input['phone'] ?? '');
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');

if ($name === '' || !$email || $subject === '' || $message === '') {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Name, valid email, subject, and message are required.']);
    exit;
}

if (!verify_recaptcha_token((string) ($input['recaptchaToken'] ?? ''))) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please complete the reCAPTCHA verification.']);
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO contact_messages (name, email, phone, subject, message)
     VALUES (:name, :email, :phone, :subject, :message)'
);
$stmt->execute([
    ':name'    => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
    ':email'   => $email,
    ':phone'   => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
    ':subject' => htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'),
    ':message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
]);
$messageId = (int) $pdo->lastInsertId();

send_site_mail(
    admin_notification_email($pdo),
    "New contact message: {$subject}",
    "A new contact message was submitted.\n\n"
    . "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\nSubject: {$subject}\n\n{$message}\n"
);

echo json_encode(['status' => 'ok', 'message_id' => $messageId]);