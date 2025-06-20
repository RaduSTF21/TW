<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../csrf.php';
$stmt = $pdo->query('SELECT * FROM properties');

// Filters from GET
$trans  = $_GET['transaction']    ?? '';
$ptype  = $_GET['property_type']  ?? '';
$rooms  = $_GET['rooms']          ?? '';
$price  = $_GET['price_max']      ?? '';

// Build base SQL
$sql = "SELECT p.id, p.title, p.price, p.rooms,
               COALESCE(pi.filename,'') AS image_file
        FROM properties p
        LEFT JOIN (
          SELECT property_id, filename
          FROM property_images
          GROUP BY property_id
        ) pi ON pi.property_id = p.id";
$w = []; $pr = [];

// Apply filters
if ($trans) {
  $w[] = "p.transaction_type = :trans";
  $pr[':trans'] = $trans;
}
if ($ptype) {
  $w[] = "p.property_type = :ptype";
  $pr[':ptype'] = $ptype;
}
if ($rooms && $rooms !== 'toate') {
  if ($rooms === '4+') {
    $w[] = "p.rooms >= :rooms";
    $pr[':rooms'] = 4;
  } else {
    $w[] = "p.rooms = :rooms";
    $pr[':rooms'] = (int)$rooms;
  }
}
if ($price !== '') {
  $w[] = "p.price <= :price";
  $pr[':price'] = (float)$price;
}

if ($w) {
  $sql .= ' WHERE ' . implode(' AND ', $w);
}

$stmt = $pdo->prepare($sql);
$stmt->execute($pr);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map each to include a full URL for the image
$data = array_map(function($r){
  $r['image_url'] = $r['image_file']
    ? 'admin/uploads/' . $r['image_file']
    : 'placeholder.jpg';
  unset($r['image_file']);
  return $r;
}, $rows);

echo json_encode($data);
