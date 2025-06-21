<?php
// public/register_form.php
session_start();

// If already logged in, send to main app
if (!empty($_SESSION['user_id'])) {
    header('Location: imob.php');
    exit;
}

// If there was an error flash, capture and clear it
$error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);

// Show any error, then the form
if ($error) {
    echo '<p class="error">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</p>';
}

// Output the static HTML form
readfile(__DIR__ . '/register.html');
