<?php
// DEBUG VERSION of register.php

// 1) Enable HTML errors temporarily so we can capture them
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Bootstrap & session
require __DIR__ . '/../bootstrap.php';
session_start();

// 3) Bring PDO into scope
$pdo = $GLOBALS['pdo'] ?? null;

// 4) Always respond JSON
header('Content-Type: application/json; charset=utf-8');

try {
    // 5) Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Metodă nepermisă');
    }

    // 6) Decode JSON payload
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        throw new Exception('JSON invalid: ' . json_last_error_msg());
    }

    // 7) CSRF validation
    if (
        empty($input['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $input['csrf_token'])
    ) {
        http_response_code(400);
        throw new Exception('Token CSRF invalid');
    }

    // 8) Validate fields
    $name             = $input['username']        ?? '';
    $email            = $input['email']           ?? '';
    $password         = $input['password']        ?? '';
    $confirm_password = $input['confirm_password'] ?? '';

    $errors = [];
    if (trim($name) === '')                            $errors[] = 'Username este obligatoriu.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))     $errors[] = 'Email invalid.';
    if (strlen($password) < 6)                         $errors[] = 'Parola trebuie ≥ 6 caractere.';
    if ($password !== $confirm_password)               $errors[] = 'Parolele nu se potrivesc.';

    if ($errors) {
        http_response_code(422);
        throw new Exception(implode(' ', $errors));
    }

    // 9) Insert into DB
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if (!$pdo) {
        throw new Exception('PDO instance is null');
    }
    $stmt = $pdo->prepare(
       'INSERT INTO users (name, email, password_hash, created_at)
        VALUES (?, ?, ?, NOW())'
    );
    $stmt->execute([$name, $email, $hash]);

    // 10) Rotate CSRF token on success
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // 11) Success response
    echo json_encode(['user_id' => $pdo->lastInsertId()]);
    exit;

} catch (Exception $e) {
    // Return the exception message as JSON for debugging
    $code = http_response_code() ?: 500;
    http_response_code($code);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
