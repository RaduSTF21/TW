<?php
// public/login.php
require_once __DIR__ . '/../bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Validare CSRF
$token = $_POST['csrf_token'] ?? '';
if (empty($token) || !function_exists('verify_csrf_token') || !verify_csrf_token($token)) {
    error_log('CSRF invalid in login: sent=' . var_export($token, true) . ' session=' . var_export($_SESSION['csrf_token'] ?? null, true));
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Preluare date
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Username și parolă sunt necesare']);
    exit;
}

try {
    $pdo = get_db_connection();
    // Tabela users are coloanele: id, name, email, password_hash, role, created_at
    // Autentificăm pe baza câmpului `name`:
    $stmt = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE name = :n');
    $stmt->execute(['n' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Credențiale invalide']);
        exit;
    }
    // Succes
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    echo json_encode(['success' => true]);
    exit;
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    exit;
}
