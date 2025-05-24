<?php
header('Content-Type: application/json');

$pdo = new PDO('mysql:host=localhost;dbname=real_estate', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$lat_min = isset($_GET['lat_min']) ? floatval($_GET['lat_min']) : null;
$lat_max = isset($_GET['lat_max']) ? floatval($_GET['lat_max']) : null;
$lng_min = isset($_GET['lng_min']) ? floatval($_GET['lng_min']) : null;
$lng_max = isset($_GET['lng_max']) ? floatval($_GET['lng_max']) : null;

// Build base SQL
$sql = "SELECT * FROM properties";
$params = [];

// If all four bounds are provided, add a WHERE clause
if ($lat_min !== null && $lat_max !== null && $lng_min !== null && $lng_max !== null) {
    $sql .= " WHERE latitude BETWEEN :lat_min AND :lat_max
              AND longitude BETWEEN :lng_min AND :lng_max";
    $params = [
        ':lat_min' => $lat_min,
        ':lat_max' => $lat_max,
        ':lng_min' => $lng_min,
        ':lng_max' => $lng_max
    ];
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($properties);

