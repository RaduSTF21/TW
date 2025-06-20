@ -1,40 +1,20 @@
<?php
// csrf.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_get_token() {
    return $_SESSION['csrf_token'];
}
function csrf_validate() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            exit(json_encode(['error' => 'Invalid CSRF token']));
        }
    }
}