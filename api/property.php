<?php
header('Content-Type: application/json');
$pdo = new PDO('mysql:host=localhost;dbname=real_estate', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1) Get & validate `id` from query
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing property ID']);
    exit;
}
$id = (int)$_GET['id'];

// 2) Fetch property
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = :id");
$stmt->execute([':id' => $id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    http_response_code(404);
    echo json_encode(['error' => 'Property not found']);
    exit;
}

// 3) Fetch images
$stmt2 = $pdo->prepare("
    SELECT filename, alt_text
    FROM property_images
    WHERE property_id = :id
");
$stmt2->execute([':id' => $id]);
$images = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// 4) Return combined JSON
echo json_encode([
    'property' => $property,
    'images'   => $images
]);
