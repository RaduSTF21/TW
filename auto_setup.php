<?php
// TW/setup.php

// 1) Only run once via lock file
if (file_exists(__DIR__ . '/setup.lock')) {
    echo "✅ Setup already completed.";
    exit;
}

try {
    // 2) Connect to MySQL server (no DB yet)
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 3) Create database if missing
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `real_estate`
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");

    // 4) Switch to real_estate
    $pdo->exec("USE `real_estate`");

    // 5) Create properties table
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(12,2) NOT NULL DEFAULT 0,
        rooms TINYINT UNSIGNED,
        transaction_type VARCHAR(20),
        property_type VARCHAR(20),
        latitude DECIMAL(9,6),
        longitude DECIMAL(9,6),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB CHARACTER SET=utf8mb4
    ");

    // 6) Create property_images table
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS property_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        filename VARCHAR(255) NOT NULL,
        alt_text VARCHAR(255),
        FOREIGN KEY (property_id)
          REFERENCES properties(id) ON DELETE CASCADE
      ) ENGINE=InnoDB CHARACTER SET=utf8mb4
    ");

    // 7) Create layers table
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS layers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT
      ) ENGINE=InnoDB CHARACTER SET=utf8mb4
    ");

    // 8) Create layer_data table
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS layer_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        layer_id INT NOT NULL,
        geojson TEXT NOT NULL,
        metadata TEXT,
        FOREIGN KEY (layer_id)
          REFERENCES layers(id) ON DELETE CASCADE
      ) ENGINE=InnoDB CHARACTER SET=utf8mb4
    ");

    // 9) Write lock file
    file_put_contents(__DIR__ . '/setup.lock', 'done');
    echo "✅ Database & tables created successfully.";

} catch (PDOException $e) {
    echo "❌ Setup failed: " . $e->getMessage();
    exit;
}
