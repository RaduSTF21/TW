<?php
// public/admin/anunturi.php
require __DIR__ . '/../../config/bootstrap.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

use App\Service\ListingService;

$service  = new ListingService($pdo);
$anunturi = $service->getAll($pdo);

// render
include __DIR__ . '/../../templates/admin/anunturi.html';
