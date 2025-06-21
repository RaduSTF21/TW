<?php
// bootstrap.php

// Autoload (de ex. pentru phpdotenv)
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
}

// Pornire sesiune și CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Funcție de verificare CSRF
function verify_csrf_token(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Config DB din environment sau valori implicite
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: getenv('DB_NAME') ?: 'real_estate';
$dbUser = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '';
$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// Funcție de obținere conexiune PDO
function get_db_connection(): PDO {
    global $dsn, $dbUser, $dbPass, $options;
    static $pdo;
    if ($pdo === null) {
        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'DB Connection failed']);
            exit;
        }
    }
    return $pdo;
}
