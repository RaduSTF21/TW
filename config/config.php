<?php
// File: config/config.php

// 1) Parse the .env file in your project root
$envPath = __DIR__ . '/../.env';
if (! file_exists($envPath)) {
    exit("❌ .env file not found at {$envPath}");
}

// SCANNER_RAW to preserve values exactly (no quotes stripping)
$env = parse_ini_file($envPath, false, INI_SCANNER_RAW);
if ($env === false) {
    exit("❌ Failed to parse .env");
}

// 2) Return the config array exactly as before
return [
    'db' => [
        'driver'   => $env['DB_DRIVER']   ?? 'mysql',
        'host'     => $env['DB_HOST']     ?? '127.0.0.1',
        'port'     => $env['DB_PORT']     ?? '3306',
        'database' => $env['DB_DATABASE'] ?? '',
        'username' => $env['DB_USERNAME'] ?? 'root',
        'password' => $env['DB_PASSWORD'] ?? '',
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],
];