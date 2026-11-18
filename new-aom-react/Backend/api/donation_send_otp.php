<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$name     = trim($input['name'] ?? '');
$email    = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$project  = trim($input['project'] ?? 'General Donation');
$currency = trim($input['currency'] ?? 'LKR');

if ($name === '' || !$email) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Valid name and email are required.']);
    exit;
}

if (!verify_recaptcha_token((string) ($input['recaptchaToken'] ?? ''))) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please complete the reCAPTCHA verification.']);
    exit;
}

$recent = $pdo->prepare(
    'SELECT COUNT(*) FROM donation_otps
     WHERE donor_email = :email AND created_at >= (NOW() - INTERVAL 10 MINUTE)'
);
$recent->execute([':email' => $email]);
if ((int) $recent->fetchColumn() >= 3) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Too many OTP requests. Please wait 10 minutes and try again.']);
    exit;
}

$otp = (string) random_int(100000, 999999);
$otpHash = password_hash($otp, PASSWORD_DEFAULT);
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$stmt = $pdo->prepare(
    'INSERT INTO donation_otps (donor_name, donor_email, project_title, currency, otp_hash, expires_at)
     VALUES (:name, :email, :project, :currency, :hash, :expires)'
);
$stmt->execute([
    ':name'     => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
    ':email'    => $email,
    ':project'  => htmlspecialchars($project, ENT_QUOTES, 'UTF-8'),
    ':currency' => htmlspecialchars($currency, ENT_QUOTES, 'UTF-8'),
    ':hash'     => $otpHash,
    ':expires'  => $expiresAt,
]);

$mailSent = send_site_mail(
    $email,
    'NEW AOM Donation Verification OTP',
    "Your verification OTP is: {$otp}\n\nIt is valid for 10 minutes. If you did not request this, please ignore this email."
);

$response = [
    'status'  => 'ok',
    'otp_id'  => (int) $pdo->lastInsertId(),
    'message' => $mailSent ? 'OTP sent to your email address.' : 'OTP generated. Check your email address.',
];
if (APP_ENV !== 'production') {
    // Local XAMPP has no mail server, so the code is echoed for testing only.
    $response['dev_otp'] = $otp;
}
echo json_encode($response);
