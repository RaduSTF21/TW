<?php
// File: config/config.php

// Configuration for database connection without relying on .env
return [
    'db' => [
        'driver'   => 'mysql',
        'host'     => 'sql105.byethost3.com',
        'port'     => '3306',
        'database' => 'b3_39295711_real_estate',
        'username' => 'b3_39295711',
        'password' => '3r9v8h5j',
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],
];
