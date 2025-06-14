<?php
require_once __DIR__ . '/../csrf.php';
require_once __DIR__ . '/../bootstrap.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
csrf_validate();
header('Content-Type: application/json');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$errors = [];
if ($username === '') { $errors[] = 'Username necesar'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email invalid'; }
if (strlen($password) < 6) { $errors[] = 'Parola prea scurtă'; }
if ($password !== $confirm) { $errors[] = 'Parolele nu se potrivesc'; }
if ($errors) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit;
}
// Verificare unicitate
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u OR email = :e");
$stmt->execute(['u' => $username, 'e' => $email]);
if ($stmt->fetchColumn() > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Username sau email deja folosit']);
    exit;
}
$hash = password_hash($password, PASSWORD_DEFAULT);
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (:u, :e, :ph, 'user')");
    $stmt->execute(['u' => $username, 'e' => $email, 'ph' => $hash]);
    $userId = $pdo->lastInsertId();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = 'user';
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>