<?php
require_once __DIR__ . '/../TW/vendor/autoload.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_get_token() {
    return $_SESSION['csrf_token'];
}
function csrf_validate() {
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    if ($method === 'POST') {
        $token = '';
        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        } elseif (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'CSRF token missing']);
            exit;
        }
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid CSRF token']);
            exit;
        }
    }
}
?>