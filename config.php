<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'real_estate');
define('DB_USER', 'root');
define('DB_PASS', '');

function getPDO() {
  $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
  $pdo = new PDO($dsn, DB_USER, DB_PASS);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $pdo;
}
