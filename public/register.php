<?php
// public/register.php

require_once __DIR__ . '/../bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Validare CSRF
$token = $_POST['csrf_token'] ?? '';
if (empty($token) || !verify_csrf_token($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Preia date din formular
$name = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

$errors = [];
if ($name === '') {
    $errors[] = 'Nume utilizator necesar';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email invalid';
}
if (strlen($password) < 6) {
    $errors[] = 'Parolă prea scurtă';
}
if ($password !== $confirm) {
    $errors[] = 'Parolele nu se potrivesc';
}
if ($errors) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit;
}

try {
    $pdo = get_db_connection();
    // Verifică unicitate pe name sau email
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE `name` = ? OR email = ?');
    $stmt->execute([$name, $email]);
    if ($stmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Nume utilizator sau email deja folosit']);
        exit;
    }
    // Inserează: coloanele name, email, password_hash și created_at cu NOW()
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (`name`, email, password_hash, created_at) VALUES (?, ?, ?, NOW())'
    );
    $stmt->execute([$name, $email, $hash]);
    $userId = $pdo->lastInsertId();
    // Autentificare automată
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = 'user';
    http_response_code(201);
    echo json_encode(['success' => true, 'user_id' => $userId]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}