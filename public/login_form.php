<?php
// public/login_form.php
session_start();

// If user already logged in, go to the main app
if (!empty($_SESSION['user_id'])) {
    header('Location: imob.php');
    exit;
}

// Otherwise, show the HTML form
readfile(__DIR__ . '/templates/login.html');
