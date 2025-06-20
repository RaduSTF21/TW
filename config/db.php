<?php
$config = require __DIR__ . '/config.php';

try {
    $db = $config['db'];
    $conn = new PDO(
        "{$db['driver']}:host={$db['host']};dbname={$db['database']};port={$db['port']}",
        $db['username'],
        $db['password'],
        $db['options']
    );
} catch (PDOException $e) {
    die('Database connection error: ' . $e->getMessage());
}
