<?php
require __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config/config.php';

$db = $config['db'];
$dsn = "{$db['driver']}:host={$db['host']};
        dbname={$db['database']};port={$db['port']};
        charset=utf8mb4";
$pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);

// Expose for legacy code if needed:
$GLOBALS['pdo'] = $pdo;
