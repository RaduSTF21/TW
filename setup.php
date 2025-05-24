<?php
$pdo = new PDO('mysql:host=localhost;dbname=real_estate', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create `properties` table
$pdo->exec("
  CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(12,2),
    latitude DECIMAL(9,6),
    longitude DECIMAL(9,6),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )
");

// Create `property_images` table
$pdo->exec("
  CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    filename VARCHAR(255),
    alt_text VARCHAR(255),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
  )
");

// Create `layers` table
$pdo->exec("
  CREATE TABLE IF NOT EXISTS layers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT
  )
");

// Create `layer_data` table
$pdo->exec("
  CREATE TABLE IF NOT EXISTS layer_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    layer_id INT,
    geojson TEXT,
    metadata TEXT,
    FOREIGN KEY (layer_id) REFERENCES layers(id) ON DELETE CASCADE
  )
");

echo "✅ Tables created successfully.";
