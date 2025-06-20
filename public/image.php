<?php

require_once __DIR__ . '/../bootstrap.php';  // gives you $pdo

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT image_data, image_mime FROM properties WHERE id = ?");
$stmt->execute([$id]);
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    header('Content-Type: ' . $row['image_mime']);
    echo $row['image_data'];
    exit;
}
http_response_code(404);
