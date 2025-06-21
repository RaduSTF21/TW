<?php
// public/login.php
require_once __DIR__ . '/../bootstrap.php';
session_start();

// only handle POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_form.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    // missing data: back to form
    header('Location: login_form.php');
    exit;
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE name = :n');
    $stmt->execute(['n' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        // bad creds
        header('Location: login_form.php');
        exit;
    }

    // success: set session and go to imob.php
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_role'] = $user['role'];

    header('Location: imob.php');
    exit;

} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    header('Location: login_form.php');
    exit;
}
