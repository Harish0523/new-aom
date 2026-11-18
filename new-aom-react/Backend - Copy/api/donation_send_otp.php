<?php
require_once __DIR__ . '/../config/db.php';

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

// cPanel / SMTP Mail
@mail($email, 'NEW AOM Donation Verification OTP', "Your verification OTP is: {$otp} (Valid for 10 minutes).");

echo json_encode([
    'status'  => 'ok',
    'otp_id'  => (int) $pdo->lastInsertId(),
    'message' => 'OTP sent to your email address.',
    // Local XAMPP சோதனைக்கு மட்டும் (Production-ல் நீக்கவும்):
    'dev_otp' => $otp,
]);