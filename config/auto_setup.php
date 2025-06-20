<?php
// config/auto_setup.php

if (file_exists(__DIR__ . '/setup.lock')) {
    echo "✅ Setup already completed.\n";
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1) Create & switch to database
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `real_estate`
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    $pdo->exec("USE `real_estate`");

    // 2) Users table (must come first) — make id UNSIGNED
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100)   NOT NULL,
    `email`         VARCHAR(255)   NOT NULL UNIQUE,
    `password_hash` VARCHAR(255)   NOT NULL,
    `role`          ENUM('user','admin') NOT NULL DEFAULT 'user',
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );

    // 3) Properties table (user_id is INT UNSIGNED)
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `properties` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`          INT UNSIGNED NOT NULL,
    `title`            VARCHAR(255)   NOT NULL,
    `description`      TEXT           NOT NULL,
    `price`            DECIMAL(12,2)  NOT NULL DEFAULT 0,
    `rooms`            TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `transaction_type` ENUM('sale','rent') NOT NULL,
    `property_type`    VARCHAR(100)   NOT NULL,
    `image_data`       LONGBLOB       NULL,
    `image_mime`       VARCHAR(50)    NULL,
    `latitude`         DECIMAL(9,6)   NULL,
    `longitude`        DECIMAL(9,6)   NULL,
    `created_at`       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_properties_user` (`user_id`),
    CONSTRAINT `fk_properties_user`
      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );

    // 4) property_images
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `property_images` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `property_id` INT UNSIGNED NOT NULL,
    `filename`    VARCHAR(255) NOT NULL,
    `alt_text`    VARCHAR(255),
    CONSTRAINT `fk_property_images_property`
      FOREIGN KEY (`property_id`)
      REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );

    // 5) layers
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `layers` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(100) NOT NULL,
    `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );

    // 6) layer_data
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `layer_data` (
    `id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `layer_id` INT UNSIGNED NOT NULL,
    `geojson`  TEXT NOT NULL,
    `metadata` TEXT,
    CONSTRAINT `fk_layer_data_layer`
      FOREIGN KEY (`layer_id`)
      REFERENCES `layers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );

    // 7) listings (user_id UNSIGNED)
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `listings` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL,
    `title`       VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price`       DECIMAL(12,2) NOT NULL,
    `location`    VARCHAR(255),
    `created_at`  DATETIME NOT NULL,
    `updated_at`  DATETIME,
    KEY `idx_listings_user` (`user_id`),
    CONSTRAINT `fk_listings_user`
      FOREIGN KEY (`user_id`)
      REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );

    // 8) reference & join tables (using UNSIGNED everywhere)
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `transaction_types` (
    `id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `property_types` (
    `id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `amenities` (
    `id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `property_amenities` (
    `property_id` INT UNSIGNED NOT NULL,
    `amenity_id`  INT UNSIGNED NOT NULL,
    PRIMARY KEY (`property_id`,`amenity_id`),
    CONSTRAINT `fk_pa_property`
      FOREIGN KEY (`property_id`)
      REFERENCES `properties`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pa_amenity`
      FOREIGN KEY (`amenity_id`)
      REFERENCES `amenities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `risks` (
    `id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `property_risks` (
    `property_id` INT UNSIGNED NOT NULL,
    `risk_id`     INT UNSIGNED NOT NULL,
    PRIMARY KEY (`property_id`,`risk_id`),
    CONSTRAINT `fk_pr_property`
      FOREIGN KEY (`property_id`)
      REFERENCES `properties`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pr_risk`
      FOREIGN KEY (`risk_id`)
      REFERENCES `risks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );

    // 9) inquiries
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `inquiries` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `property_id`  INT UNSIGNED NOT NULL,
    `user_id`      INT UNSIGNED NULL,
    `name`         VARCHAR(100) NOT NULL,
    `email`        VARCHAR(255) NOT NULL,
    `message`      TEXT NOT NULL,
    `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_inquiries_property`
      FOREIGN KEY (`property_id`)
      REFERENCES `properties`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_inquiries_user`
      FOREIGN KEY (`user_id`)
      REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );

    // 10) favorites
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `favorites` (
    `user_id`    INT UNSIGNED NOT NULL,
    `listing_id` INT UNSIGNED NOT NULL,
    `saved_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`,`listing_id`),
    CONSTRAINT `fk_fav_user`
      FOREIGN KEY (`user_id`)
      REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fav_listing`
      FOREIGN KEY (`listing_id`)
      REFERENCES `listings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );

    // --- Seed reference tables ---
    $pdo->exec("INSERT IGNORE INTO `transaction_types` (name) VALUES ('sale'),('rent');");
    $pdo->exec("INSERT IGNORE INTO `property_types` (name) VALUES ('apartment'),('house'),('land'),('office'),('studio'),('villa');");
    $pdo->exec("INSERT IGNORE INTO `amenities` (name) VALUES
      ('WiFi'),('Parking'),('Swimming Pool'),('Air Conditioning'),
      ('Balcony'),('Garden'),('Elevator'),('Heating'),
      ('Furnished'),('Security System')
    ");
    $pdo->exec("INSERT IGNORE INTO `risks` (name) VALUES
      ('Flood'),('Earthquake'),('Fire'),('Landslide'),
      ('Pollution'),('Noise'),('Crime')
    ");

    // 11) Create lock file
    file_put_contents(__DIR__ . '/setup.lock', 'done');
    echo "✅ Setup completed successfully.\n";

} catch (PDOException $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
