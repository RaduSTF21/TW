<?php
// public/edit_listing.php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../csrf.php';
use App\service\ListingService;

if (empty($_SESSION['user_id'])) {
    header('Location: login_form.php');
    exit;
}

$pdo        = get_db_connection();
$listingSvc = new ListingService();

// Validate & load existing listing
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    echo 'ID invalid.';
    exit;
}

$property = $listingSvc->getById($pdo, $id);
if (!$property || (int)$property['user_id'] !== (int)$_SESSION['user_id']) {
    http_response_code(404);
    echo 'Listing not found.';
    exit;
}

// Handle POST (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();

    $data = [
        'id'               => $id,
        'user_id'          => (int)$_SESSION['user_id'],
        'title'            => trim($_POST['title'] ?? ''),
        'description'      => trim($_POST['description'] ?? ''),
        'price'            => (float)($_POST['price'] ?? 0),
        'rooms'            => (int)($_POST['rooms'] ?? 0),
        'transaction_type' => (int)($_POST['transaction_type'] ?? 0),
        'property_type'    => (int)($_POST['property_type'] ?? 0),
        'latitude'         => (float)($_POST['latitude'] ?? 0),
        'longitude'        => (float)($_POST['longitude'] ?? 0),
    ];

    $amenities = $_POST['amenities'] ?? [];
    $risks     = $_POST['risks']     ?? [];

    $listingSvc->saveProperty($pdo, $data, $amenities, $risks);
    header("Location: account.php?updated_listing={$id}");
    exit;
}

// On GET, render template
$csrfToken = $_SESSION['csrf_token'];
$template  = file_get_contents(__DIR__ . '/templates/edit_listing.html');

$html = str_replace(
    [
      '<!--CSRF_TOKEN-->',
      '<!--ID-->',
      '<!--TITLE-->',
      '<!--DESCRIPTION-->',
      '<!--PRICE-->',
      '<!--ROOMS-->',
      '<!--LAT-->',
      '<!--LNG-->'
    ],
    [
      htmlspecialchars($csrfToken, ENT_QUOTES),
      $id,
      htmlspecialchars($property['title'], ENT_QUOTES),
      htmlspecialchars($property['description'], ENT_QUOTES),
      htmlspecialchars((string)$property['price'], ENT_QUOTES),
      htmlspecialchars((string)$property['rooms'], ENT_QUOTES),
      htmlspecialchars((string)$property['latitude'], ENT_QUOTES),
      htmlspecialchars((string)$property['longitude'], ENT_QUOTES),
    ],
    $template
);

echo $html;
