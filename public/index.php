<?php


// Parse the request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve landing page: imob.php
if ($uri === '/' || $uri === '/index.php') {
    readfile(__DIR__ . '/imob.php');
    exit;
}

// Serve register template
if ($uri === '/register' || $uri === '/register.html') {
    readfile(__DIR__ . '/../templates/register.html');
    exit;
}

// Serve login template
if ($uri === '/login' || $uri === '/login.html') {
    readfile(__DIR__ . '/../templates/login.html');
    exit;
}

// Serve API endpoints directly
if ($uri === '/register.php') {
    require __DIR__ . '/register.php';
    exit;
}
if ($uri === '/login.php') {
    require __DIR__ . '/login.php';
    exit;
}

// 404 for other paths
http_response_code(404);
echo 'Not Found';