<?php
// File: admin/login.php
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
$password = $_POST['password'] ?? '';
if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Username și parolă necesare']);
    exit;
}
$stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Credențiale invalide']);
    exit;
}
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_role'] = $user['role'];
echo json_encode(['success' => true]);
?>
