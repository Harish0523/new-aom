<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require_once __DIR__ . '/../../Backend/config/db.php';

function require_admin_login(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_admin_role(array $roles): void {
    require_admin_login();

    if (!in_array($_SESSION['admin_role'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}

function log_security_event(PDO $pdo, string $email, string $action): void {
    $stmt = $pdo->prepare(
        'INSERT INTO security_logs (user_email, action, ip_address) VALUES (:email, :action, :ip)'
    );
    $stmt->execute([
        ':email'  => $email,
        ':action' => $action,
        ':ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
}