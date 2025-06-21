<?php
// public/api/properties.php

require_once __DIR__ . '/../../bootstrap.php'; // sau calea corectă către bootstrap/db
$pdo = get_db_connection();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'filters') {
    try {
        $stmt = $pdo->query('SELECT name FROM transaction_types ORDER BY name');
        $tt = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->query('SELECT name FROM property_types ORDER BY name');
        $pt = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            'transaction_types' => $tt,
            'property_types'    => $pt
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Eroare la încărcare filtre: ' . $e->getMessage()]);
    }
    exit;
}

// Construiește WHERE după parametri
$where = [];
$params = [];
if (!empty($_GET['transaction'])) {
    $where[] = 'transaction_type = :transaction';
    $params[':transaction'] = $_GET['transaction'];
}
if (!empty($_GET['property_type'])) {
    $where[] = 'property_type = :ptype';
    $params[':ptype'] = $_GET['property_type'];
}
if (isset($_GET['price_max']) && is_numeric($_GET['price_max'])) {
    $where[] = 'price <= :price_max';
    $params[':price_max'] = $_GET['price_max'];
}
if (isset($_GET['rooms_min']) && is_numeric($_GET['rooms_min'])) {
    $where[] = 'rooms >= :rooms_min';
    $params[':rooms_min'] = $_GET['rooms_min'];
}

 $sql = "SELECT p.id,
             p.title,
             p.price,
             p.rooms,
             p.transaction_type,
             p.property_type,
             p.created_at,
                p.description,
             (SELECT filename
                FROM property_images pi
               WHERE pi.property_id = p.id
            ORDER BY pi.id
               LIMIT 1
             ) AS image  FROM properties p";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($listings);
