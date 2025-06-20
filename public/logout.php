<?php
// public/logout.php

require_once __DIR__ . '/../bootstrap.php';

// Destroy session and cookie
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
session_destroy();

// Redirect to landing page
header('Location: /TW/public/imob.php');
exit;