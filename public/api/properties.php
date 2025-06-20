<?php
// File: public/api/properties.php

require_once __DIR__ . '/../../config/db.php'; // Adjust path if needed

// Build WHERE clauses from filters
$where   = [];
$params  = [];

if (!empty($_GET['transaction'])) {
    $where[]           = 'transaction_type = :transaction';
    $params[':transaction'] = $_GET['transaction'];
}
if (!empty($_GET['property_type'])) {
    $where[]              = 'property_type = :ptype';
    $params[':ptype']     = $_GET['property_type'];
}
if (isset($_GET['price_max']) && is_numeric($_GET['price_max'])) {
    $where[]                  = 'price <= :price_max';
    $params[':price_max']     = $_GET['price_max'];
}
if (isset($_GET['rooms_min']) && is_numeric($_GET['rooms_min'])) {
    $where[]                  = 'rooms >= :rooms_min';
    $params[':rooms_min']     = $_GET['rooms_min'];
}

// Base query
$sql = "SELECT id, title, price, rooms, transaction_type, property_type, created_at
        FROM properties";

// Apply filters
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

// Newest first
$sql .= ' ORDER BY created_at DESC';

// Apply limit if given
if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
    $sql .= ' LIMIT :limit';
}

$stmt = $conn->prepare($sql);

// Bind filter params
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
// Bind limit as integer
if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
    $stmt->bindValue(':limit', (int)$_GET['limit'], PDO::PARAM_INT);
}

$stmt->execute();
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($listings);
