<?php
// TW/setup.php


if (file_exists(__DIR__ . '/setup.lock')) {
    echo "✅ Setup already completed.";
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `real_estate`
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");

    
    $pdo->exec("USE `real_estate`");

    
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

    
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS layers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT
      ) ENGINE=InnoDB CHARACTER SET=utf8mb4
    ");

    
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
    
      $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at DATETIME NOT NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );

$pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    location VARCHAR(255),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );

    file_put_contents(__DIR__ . '/setup.lock', 'done');
    echo "✅ Database & tables created successfully.";

} catch (PDOException $e) {
    echo "❌ Setup failed: " . $e->getMessage();
    exit;
}
