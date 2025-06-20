<?php

// 1. Load Composer and .env
require_once __DIR__ . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->load();

// 2. JSON error helper
function json_error_response(string $message, int $code = 500): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $message]);
    exit;
}

// 3. PDO factory
function get_db_connection(): ?PDO {
    static $pdo;
    if ($pdo) return $pdo;
    try {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'],
            $_ENV['DB_NAME']
        );
        $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (Exception $e) {
        json_error_response('Database connection failed', 500);
    }
    return null; // Ensures all code paths return a value
}

// 4. Session + CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
function verify_csrf_token(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}