<?php
// File: public/anunt_form.php
require_once __DIR__ . '/../csrf.php';
require_once __DIR__ . '/../bootstrap.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
csrf_validate();
header('Content-Type: application/json');
// Verificare autentificare utilizator
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Autentificare necesară']);
    exit;
}
// Preia câmpuri anunț (similar admin/property_form, fără privilegii extinse)
$title = trim($_POST['title'] ?? '');
$price = $_POST['price'] ?? '';
$lat = $_POST['latitude'] ?? '';
$lng = $_POST['longitude'] ?? '';
$description = trim($_POST['description'] ?? '');
$errors = [];
if ($title === '') { $errors[] = 'Titlu necesar'; }
if (!is_numeric($price) || $price <= 0) { $errors[] = 'Preț invalid'; }
if (!is_numeric($lat) || !is_numeric($lng)) { $errors[] = 'Coordonate invalide'; }
if ($errors) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit;
}
try {
    $stmt = $pdo->prepare("INSERT INTO properties (title, price, latitude, longitude, description, user_id) VALUES (:title, :price, :lat, :lng, :desc, :uid)");
    $stmt->execute(['title'=>$title,'price'=>$price,'lat'=>$lat,'lng'=>$lng,'desc'=>$description,'uid'=>$_SESSION['user_id']]);
    $propertyId = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'property_id' => $propertyId]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>
