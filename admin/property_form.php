<?php

require_once __DIR__ . '/../csrf.php';
require_once __DIR__ . '/../bootstrap.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
// Verificare rol admin
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}
csrf_validate();
header('Content-Type: application/json');
// Preia câmpuri
$id = $_POST['id'] ?? null;
$title = trim($_POST['title'] ?? '');
$price = $_POST['price'] ?? '';
$lat = $_POST['latitude'] ?? '';
$lng = $_POST['longitude'] ?? '';$description = trim($_POST['description'] ?? '');
$transaction_type = $_POST['transaction_type'] ?? '';
$property_type = $_POST['property_type'] ?? '';
// Alte câmpuri: building_state, etc.
$errors = [];
if ($title === '') { $errors[] = 'Titlu necesar'; }
if (!is_numeric($price) || $price <= 0) { $errors[] = 'Preț invalid'; }
if (!is_numeric($lat) || !is_numeric($lng)) { $errors[] = 'Coordonate invalide'; }
if ($transaction_type === '') { $errors[] = 'Tip tranzacție necesar'; }
if ($property_type === '') { $errors[] = 'Tip proprietate necesar'; }
// Validare facilități/risk etc. (presupune tabele adiționale)
if ($errors) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit;
}
try {
    if ($id) {
        // UPDATE
        $stmt = $pdo->prepare("UPDATE properties SET title=:title, price=:price, latitude=:lat, longitude=:lng, description=:desc, transaction_type=:tt, property_type=:pt WHERE id=:id");
        $stmt->execute([
            'title'=>$title,
            'price'=>$price,
            'lat'=>$lat,
            'lng'=>$lng,
            'desc'=>$description,
            'tt'=>$transaction_type,
            'pt'=>$property_type,
            'id'=>$id
        ]);
        $propertyId = $id;
    } else {
        // INSERT
        $stmt = $pdo->prepare("INSERT INTO properties (title, price, latitude, longitude, description, transaction_type, property_type) VALUES (:title, :price, :lat, :lng, :desc, :tt, :pt)");
        $stmt->execute([
            'title'=>$title,
            'price'=>$price,
            'lat'=>$lat,
            'lng'=>$lng,
            'desc'=>$description,
            'tt'=>$transaction_type,
            'pt'=>$property_type
        ]);
        $propertyId = $pdo->lastInsertId();
    }

    if (!empty($_POST['facilities'])) {
        $facArr = array_filter(array_map('intval', explode(',', $_POST['facilities'])));
        // Șterge vechile legături
        $pdo->prepare("DELETE FROM property_facility WHERE property_id = :pid")->execute(['pid'=>$propertyId]);
        if ($facArr) {
            $stmtIns = $pdo->prepare("INSERT INTO property_facility (property_id, facility_id) VALUES (:pid, :fid)");
            foreach ($facArr as $fid) {
                $stmtIns->execute(['pid'=>$propertyId, 'fid'=>$fid]);
            }
        }
    }
    // Upload imagini
    if (!empty($_FILES['images'])) {
        foreach ($_FILES['images']['error'] as $idx => $error) {
            if ($error === UPLOAD_ERR_OK) {
                $tmp = $_FILES['images']['tmp_name'][$idx];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($tmp);
                $allowed = ['image/jpeg'=>'jpg', 'image/png'=>'png'];
                if (!isset($allowed[$mime])) {
                    continue;
                }
                $ext = $allowed[$mime];
                $newName = bin2hex(random_bytes(16)) . ".\$ext";
                $destPath = __DIR__ . '/../uploads/' . $newName;
                if (move_uploaded_file($tmp, $destPath)) {
                    $stmtImg = $pdo->prepare("INSERT INTO property_images (property_id, filename) VALUES (:pid, :fn)");
                    $stmtImg->execute(['pid'=>$propertyId, 'fn'=>$newName]);
                }
            }
        }
    }
    echo json_encode(['success' => true, 'property_id' => $propertyId]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>
