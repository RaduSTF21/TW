<?php


// Only configure cookies & start session if none is active
if (session_status() === PHP_SESSION_NONE) {
    // Adjust these values as needed:
    session_set_cookie_params([
        'lifetime' => 0,                        // session cookie until browser closes
        'path'     => '/',                      // site‑wide
        'domain'   => $_SERVER['HTTP_HOST'],    // your host
        'secure'   => isset($_SERVER['HTTPS']), // only send over HTTPS
        'httponly' => true,                     // JS cannot read the cookie
        'samesite' => 'Lax',                    // mitigate CSRF
    ]);
    session_start();
}

/**
 * Returns the CSRF token, generating one if needed.
 */
function csrf_get_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates the incoming CSRF token, exiting on failure.
 */
function csrf_validate(): void {
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        http_response_code(400);
        exit('Invalid CSRF token');
    }
}