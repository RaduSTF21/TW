<?php
// public/login.php
require_once __DIR__ . '/../bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']); exit;
}
$token = $_POST['csrf_token'] ?? '';
if (empty($token) || !verify_csrf_token($token)) {
    http_response_code(403); echo json_encode(['error' => 'Invalid CSRF token']); exit;
}
$name = trim($_POST['username'] ?? ''); $password = $_POST['password'] ?? '';
if ($name === '' || $password === '') {
    http_response_code(400); echo json_encode(['error' => 'Nume utilizator și parolă necesare']); exit;
}
try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE `name` = ?');
    $stmt->execute([$name]); $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401); echo json_encode(['error' => 'Credențiale invalide']); exit;
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'] ?? 'user';
    echo json_encode(['success' => true, 'user_id' => $user['id']]);
} catch(Exception $e) {
    http_response_code(500); echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}