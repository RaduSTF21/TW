<?php
// public/api/property.php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../src/service/ListingService.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = get_db_connection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Property ID is required']);
    exit;
}

use App\service\ListingService;

// 1) Fetch the property
$prop = ListingService::getById($pdo, $id);
if (!$prop) {
    http_response_code(404);
    echo json_encode(['error' => 'Property not found']);
    exit;
}

// 2) Fetch its images
$images = ListingService::getImages($pdo, $id);

// 3) Return JSON
echo json_encode([
    'property' => $prop,
    'images'   => $images
]);
