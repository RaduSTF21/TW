<?php
// File: public/init.php

session_start();

// 1) Load the PDO from our new db.php
$pdo  = require __DIR__ . '/../config/db.php';
$conn = $pdo;

// 2) Determine login state
$isLoggedIn    = ! empty($_SESSION['user_id']);
$currentUserId = $isLoggedIn ? (int)$_SESSION['user_id'] : null;
