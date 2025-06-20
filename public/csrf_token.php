<?php
// File: public/csrf_token.php

require_once __DIR__ . '/../csrf.php';  // adjust path if your csrf.php lives elsewhere
header('Content-Type: application/json');
echo json_encode([
    'csrf_token' => csrf_get_token()
]);