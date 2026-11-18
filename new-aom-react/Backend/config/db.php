<?php
declare(strict_types=1);

$corsOrigin = getenv('AOM_CORS_ORIGIN') ?: 'http://localhost:5173';
header("Access-Control-Allow-Origin: {$corsOrigin}");
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = getenv('AOM_DB_HOST') ?: '127.0.0.1';
$db   = getenv('AOM_DB_NAME') ?: 'new_aom_db';
$user = getenv('AOM_DB_USER') ?: 'root';
$passEnv = getenv('AOM_DB_PASS');
$pass = $passEnv === false ? '' : $passEnv;

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}