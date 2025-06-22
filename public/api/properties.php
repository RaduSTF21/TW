<?php
// public/api/properties.php

require_once __DIR__ . '/../../bootstrap.php';
$pdo = get_db_connection();

header('Content-Type: application/json; charset=utf-8');

// 1) Preserve old action=filters behavior
if (isset($_GET['action']) && $_GET['action'] === 'filters') {
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

// 2) Read standard filters
$transaction = $_GET['transaction']    ?? '';
$propertyType= $_GET['property_type']  ?? '';
$priceMax    = $_GET['price_max']      ?? '';
$roomsMin    = $_GET['rooms_min']      ?? '';
$limit       = isset($_GET['limit']) && is_numeric($_GET['limit'])
               ? (int)$_GET['limit']
               : null;

// 3) Read optional location params
$userLat = isset($_GET['lat'])  && is_numeric($_GET['lat'])  ? (float)$_GET['lat']  : null;
$userLng = isset($_GET['lng'])  && is_numeric($_GET['lng'])  ? (float)$_GET['lng']  : null;
$radius  = isset($_GET['radius']) && is_numeric($_GET['radius']) ? (float)$_GET['radius'] : null;

// 4) Build WHERE clauses (removed p.active filter)
$where = [];
$params = [];

if ($transaction !== '') {
    $where[] = 'p.transaction_type = :transaction';
    $params[':transaction'] = $transaction;
}
if ($propertyType !== '') {
    $where[] = 'p.property_type = :ptype';
    $params[':ptype'] = $propertyType;
}
if ($priceMax !== '' && is_numeric($priceMax)) {
    $where[] = 'p.price <= :price_max';
    $params[':price_max'] = $priceMax;
}
if ($roomsMin !== '' && is_numeric($roomsMin)) {
    $where[] = 'p.rooms >= :rooms_min';
    $params[':rooms_min'] = $roomsMin;
}

// 5) Start building SELECT
$select = "
  SELECT
    p.id,
    p.title,
    p.description,
    p.price,
    p.rooms,
    p.transaction_type,
    p.property_type,
    p.latitude,
    p.longitude,
    p.created_at,
    -- old front-end expects 'image'
    (
      SELECT pi.filename
        FROM property_images pi
       WHERE pi.property_id = p.id
    ORDER BY pi.id
       LIMIT 1
    ) AS image
";

// 6) Append distance calculation if location provided
if ($userLat !== null && $userLng !== null) {
    $select .= ",
    (
      6371 * acos(
        cos(radians(:ulat)) *
        cos(radians(p.latitude)) *
        cos(radians(p.longitude) - radians(:ulng)) +
        sin(radians(:ulat)) *
        sin(radians(p.latitude))
      )
    ) AS distance
    ";
    $params[':ulat'] = $userLat;
    $params[':ulng'] = $userLng;
}

// 7) FROM + WHERE
$sql = $select . "
  FROM properties p"
  . (count($where) ? "\n WHERE " . implode(' AND ', $where) : '');

// 8) Radius filter if given
if ($radius !== null && isset($params[':ulat'])) {
    $sql .= " HAVING distance <= :radius";
    $params[':radius'] = $radius;
}

// 9) ORDER BY
if (isset($params[':ulat'])) {
    $sql .= " ORDER BY distance ASC";
} else {
    $sql .= " ORDER BY p.created_at DESC";
}

// 10) LIMIT
if ($limit !== null) {
    $sql .= " LIMIT " . $limit;
}

try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        if (is_int($v)) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($k, $v);
        }
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 11) Build final output: keep 'image' for old code, add 'image_url' & 'distance'
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $baseUrl  = $protocol . '://' . $_SERVER['HTTP_HOST']
              . dirname($_SERVER['SCRIPT_NAME']) . '/../uploads/';

    $results = array_map(function($r) use ($baseUrl) {
        // image_url for new front-end
        $r['image_url'] = $r['image']
            ? $baseUrl . rawurlencode($r['image'])
            : null;
        // round distance or keep null
        if (isset($r['distance'])) {
            $r['distance'] = round((float)$r['distance'], 2);
        }
        return $r;
    }, $rows);

    echo json_encode($results);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Eroare API proprietăți: ' . $e->getMessage()]);
    exit;
}
