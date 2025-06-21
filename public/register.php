<?php
// public/register.php
require_once __DIR__ . '/../bootstrap.php';
session_start();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register_form.php');
    exit;
}

// Collect & validate inputs
$name     = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

if ($name === '' || $email === '' || $password === '' || $confirm === '') {
    $_SESSION['register_error'] = 'Toate câmpurile sunt obligatorii.';
    header('Location: register_form.php');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = 'Adresa de email nu este validă.';
    header('Location: register_form.php');
    exit;
}
if ($password !== $confirm) {
    $_SESSION['register_error'] = 'Parolele nu se potrivesc.';
    header('Location: register_form.php');
    exit;
}
if (strlen($password) < 6) {
    $_SESSION['register_error'] = 'Parola trebuie să aibă cel puțin 6 caractere.';
    header('Location: register_form.php');
    exit;
}

try {
    $pdo = get_db_connection();
    // Check uniqueness
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE `name` = ? OR email = ?');
    $stmt->execute([$name, $email]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['register_error'] = 'Nume utilizator sau email deja folosit.';
        header('Location: register_form.php');
        exit;
    }

    // Insert new user
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
      'INSERT INTO users (`name`, email, password_hash, created_at)
       VALUES (?, ?, ?, NOW())'
    );
    $stmt->execute([$name, $email, $hash]);
    $userId = $pdo->lastInsertId();

    // Log them in and redirect to main app
    session_regenerate_id(true);
    $_SESSION['user_id']   = $userId;
    $_SESSION['user_role'] = 'user';

    header('Location: imob.php');
    exit;

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    $_SESSION['register_error'] = 'Eroare internă, încearcă din nou.';
    header('Location: register_form.php');
    exit;
}
