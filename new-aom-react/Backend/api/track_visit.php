<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'POST required.']);
    exit;
}

try {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS visitors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page VARCHAR(255) NOT NULL DEFAULT \'\',
            ip_hash CHAR(64) NOT NULL DEFAULT \'\',
            visited_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY visitors_visited_at (visited_at)
        )'
    );
} catch (PDOException $error) {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Visitor tracking is unavailable.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$page = substr(trim((string) ($input['page'] ?? '/')), 0, 255);
$ipHash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|new-aom');

$stmt = $pdo->prepare('INSERT INTO visitors (page, ip_hash) VALUES (:page, :ip)');
$stmt->execute([':page' => $page, ':ip' => $ipHash]);

echo json_encode(['status' => 'ok']);
