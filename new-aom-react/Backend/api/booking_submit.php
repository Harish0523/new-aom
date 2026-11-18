<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$fullName = trim($input['fullName'] ?? '');
$email    = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone    = trim($input['phone'] ?? '');
$service  = trim($input['service'] ?? '');
$date     = trim($input['preferredDate'] ?? '');
$time     = trim($input['preferredTime'] ?? '');
$notes    = trim($input['notes'] ?? '');

if ($fullName === '' || !$email || $phone === '' || $service === '' || $date === '' || $time === '') {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'All required booking fields must be filled.']);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) === false) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Choose a valid preferred date.']);
    exit;
}

if (!verify_recaptcha_token((string) ($input['recaptchaToken'] ?? ''))) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please complete the reCAPTCHA verification.']);
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO bookings (full_name, email, phone, service_name, preferred_date, preferred_time, notes)
     VALUES (:name, :email, :phone, :service, :date, :time, :notes)'
);
$stmt->execute([
    ':name'    => htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
    ':email'   => $email,
    ':phone'   => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
    ':service' => htmlspecialchars($service, ENT_QUOTES, 'UTF-8'),
    ':date'    => $date,
    ':time'    => htmlspecialchars($time, ENT_QUOTES, 'UTF-8'),
    ':notes'   => htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'),
]);
$bookingId = (int) $pdo->lastInsertId();

$adminEmail = admin_notification_email($pdo);
$settings = settings_payload($pdo);
$siteTitle = $settings['siteTitle'] ?? 'NEW AOM';
send_site_mail(
    $adminEmail,
    "New booking request: {$service}",
    "A new booking request was submitted on {$siteTitle}.\n\n"
    . "Name: {$fullName}\nEmail: {$email}\nPhone: {$phone}\n"
    . "Service: {$service}\nPreferred date: {$date}\nPreferred time: {$time}\nNotes: {$notes}\n"
);

echo json_encode(['status' => 'ok', 'booking_id' => $bookingId]);