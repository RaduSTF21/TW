<?php
// public/account.php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../csrf.php';

// only start if none exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login_form.php');
    exit;
}

// get DB connection and UserService
$pdo = get_db_connection();
$userService = new \App\Service\UserService($pdo);
$user = $userService->getUserById((int)$_SESSION['user_id']);

// prepare defaults
$errors    = [];
$form_name = $user->name;
$alerts    = '';

// handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();

    $form_name = trim($_POST['name'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if ($form_name === '') {
        $errors[] = 'Name cannot be empty.';
    }
    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== '' && $password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $updateData = ['name' => $form_name];
        if ($password !== '') {
            $updateData['password'] = $password;
        }
        $userService->updateUser($user->id, $updateData);
        header('Location: account.php?updated=1');
        exit;
    }
}

// build alerts HTML
if (!empty($_GET['updated'])) {
    $alerts = '<div class="alert alert-success">Your account has been updated.</div>';
} elseif (!empty($errors)) {
    $alerts = '<div class="alert alert-danger"><ul>';
    foreach ($errors as $e) {
        $alerts .= '<li>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    $alerts .= '</ul></div>';
}

// get CSRF token
$csrfToken = csrf_get_token();

// render template
$template = file_get_contents(__DIR__ . '/templates/account.html');
echo str_replace(
    ['<!--ALERTS-->', '<!--CSRF_TOKEN-->', '<!--FORM_NAME-->'],
    [$alerts, htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'), htmlspecialchars($form_name, ENT_QUOTES, 'UTF-8')],
    $template
);
