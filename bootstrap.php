<?php

use Dotenv\Dotenv;
require_once  __DIR__ . '/../TW/vendor/autoload.php';
// Încarcă .env dacă folosește php dotenv
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../TW');
    $dotenv->load();
}
// Configurare PDO
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'real_estate';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (Exception $e) {
    http_response_code(500);
    echo 'DB Connection failed: ' . htmlspecialchars($e->getMessage());
    exit;
}
?>