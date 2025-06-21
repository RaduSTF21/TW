<?php
// public/adauga_anunt.php

require_once __DIR__ . '/../bootstrap.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pdo = get_db_connection();

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_csrf') {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        json_response(['csrf_token' => $_SESSION['csrf_token']]);
    }
    if ($action === 'options') {
        try {
            $stmt = $pdo->query('SELECT id, name FROM transaction_types ORDER BY name');
            $transaction_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = $pdo->query('SELECT id, name FROM property_types ORDER BY name');
            $property_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = $pdo->query('SELECT id, name FROM amenities ORDER BY name');
            $amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = $pdo->query('SELECT id, name FROM risks ORDER BY name');
            $risks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            json_response([
                'transaction_types' => $transaction_types,
                'property_types'    => $property_types,
                'amenities'         => $amenities,
                'risks'             => $risks
            ]);
        } catch (Exception $e) {
            json_response(['error' => 'Eroare la încărcare opțiuni: ' . $e->getMessage()], 500);
        }
    }
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Bad Request';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit') {
    // CSRF
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !verify_csrf_token($token)) {
        json_response(['error' => 'Invalid CSRF token'], 403);
    }
    // Autentificare
    if (empty($_SESSION['user_id'])) {
        json_response(['error' => 'Autentificare necesară'], 401);
    }
    $user_id = (int)$_SESSION['user_id'];

    // Preluare și validare
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $rooms = $_POST['rooms'] ?? '';
    $transaction_type = trim($_POST['transaction_type'] ?? '');
    $property_type = trim($_POST['property_type'] ?? '');
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    $amenities = $_POST['amenities'] ?? [];
    $risks = $_POST['risks'] ?? [];

    $errors = [];
    if ($title === '') { $errors[] = 'Titlu necesar'; }
    if (!is_numeric($price) || $price < 0) { $errors[] = 'Preț invalid'; }
    if (!is_numeric($rooms) || $rooms < 0) { $errors[] = 'Număr camere invalid'; }
    if ($transaction_type === '') { $errors[] = 'Tip tranzacție necesar'; }
    if ($property_type === '') { $errors[] = 'Tip proprietate necesar'; }
    if ($latitude !== '' && !is_numeric($latitude)) { $errors[] = 'Latitude invalid'; }
    if ($longitude !== '' && !is_numeric($longitude)) { $errors[] = 'Longitude invalid'; }
    if (!is_array($amenities)) { $amenities = []; }
    foreach ($amenities as $am_id) {
        if (!is_numeric($am_id)) { $errors[] = 'ID Amenity invalid'; break; }
    }
    if (!is_array($risks)) { $risks = []; }
    foreach ($risks as $rk_id) {
        if (!is_numeric($rk_id)) { $errors[] = 'ID Risk invalid'; break; }
    }
    if ($errors) {
        json_response(['errors' => $errors], 400);
    }

    try {
        $pdo->beginTransaction();
        $sql = 'INSERT INTO properties 
            (user_id, title, description, price, rooms, transaction_type, property_type, latitude, longitude)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id,
            $title,
            $description,
            $price,
            $rooms,
            $transaction_type,
            $property_type,
            $latitude !== '' ? $latitude : null,
            $longitude !== '' ? $longitude : null
        ]);
        $property_id = $pdo->lastInsertId();

        if (!empty($amenities)) {
            $stmtAm = $pdo->prepare('INSERT INTO property_amenities (property_id, amenity_id) VALUES (?, ?)');
            foreach ($amenities as $am_id) {
                $stmtAm->execute([$property_id, (int)$am_id]);
            }
        }
        if (!empty($risks)) {
            $stmtRk = $pdo->prepare('INSERT INTO property_risks (property_id, risk_id) VALUES (?, ?)');
            foreach ($risks as $rk_id) {
                $stmtRk->execute([$property_id, (int)$rk_id]);
            }
        }
        if (!empty($_FILES['images'])) {
            $uploadDir = __DIR__ . '/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            foreach ($_FILES['images']['error'] as $idx => $error) {
                if ($error === UPLOAD_ERR_OK) {
                    $tmp = $_FILES['images']['tmp_name'][$idx];
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($tmp);
                    $allowed = ['image/jpeg'=>'jpg', 'image/png'=>'png'];
                    if (!isset($allowed[$mime])) continue;
                    $ext = $allowed[$mime];
                    $newName = bin2hex(random_bytes(16)) . ".$ext";
                    $dest = $uploadDir . '/' . $newName;
                    if (move_uploaded_file($tmp, $dest)) {
                        $stmtImg = $pdo->prepare(
                            'INSERT INTO property_images (property_id, filename, alt_text) VALUES (?, ?, ?)'
                        );
                        $stmtImg->execute([$property_id, $newName, '']);
                    }
                }
            }
        }
        $pdo->commit();
        json_response(['success' => true, 'property_id' => $property_id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

http_response_code(400);
header('Content-Type: text/plain; charset=utf-8');
echo 'Bad Request';
exit;
