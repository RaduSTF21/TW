<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config.php';
$pdo = getPDO();

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM properties WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['id']]);
}
header('Location: properties.php');
exit;
