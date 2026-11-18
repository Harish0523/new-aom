<?php
require_once __DIR__ . '/../config/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$otpId = (int) ($input['otp_id'] ?? 0);
$otp   = trim($input['otp'] ?? '');

$stmt = $pdo->prepare('SELECT * FROM donation_otps WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $otpId]);
$record = $stmt->fetch();

if (!$record || $record['attempts'] >= 5 || strtotime($record['expires_at']) < time()) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'OTP expired or maximum attempts exceeded.']);
    exit;
}

if (!password_verify($otp, $record['otp_hash'])) {
    $pdo->prepare('UPDATE donation_otps SET attempts = attempts + 1 WHERE id = :id')->execute([':id' => $otpId]);
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP code.']);
    exit;
}

$pdo->prepare('UPDATE donation_otps SET is_verified = 1 WHERE id = :id')->execute([':id' => $otpId]);

$bank = $pdo->query('SELECT bank_name, account_holder, account_number, branch_name, swift_code, qr_image_path FROM donation_bank_settings ORDER BY id DESC LIMIT 1')->fetch();

echo json_encode([
    'status' => 'verified',
    'bank'   => $bank,
]);