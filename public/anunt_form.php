<?php
use App\Service\AnuntService;
use App\Service\ListingService;

require __DIR__ . '/../../config/config.php';
require __DIR__ . '/../../vendor/autoload.php';
session_start();

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

$service = new ListingService($pdo);
$id      = isset($_GET['id']) ? (int)$_GET['id'] : null;
$errors  = [];

define('FORM_ACTION', $_SERVER['PHP_SELF'] . ($id ? '?id=' . $id : ''));

$data = ['titlu' => '', 'descriere' => '', 'pret' => '', 'locatie' => ''];
if ($id) {
    $anunt = $service->getById($id);
    if ($anunt) {
        $data = [
            'titlu'     => $anunt['titlu'],
            'descriere' => $anunt['descriere'],
            'pret'      => $anunt['pret'],
            'locatie'   => $anunt['locatie']
        ];
    } else {
        header('Location: /admin/anunturi.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['titlu']     = trim($_POST['titlu'] ?? '');
    $data['descriere'] = trim($_POST['descriere'] ?? '');
    $data['pret']      = (float)($_POST['pret'] ?? 0);
    $data['locatie']   = trim($_POST['locatie'] ?? '');

    if ($data['titlu'] === '') {
        $errors[] = 'Titlul este obligatoriu.';
    }
    if ($data['pret'] <= 0) {
        $errors[] = 'Prețul trebuie să fie mai mare decât zero.';
    }

    if (empty($errors)) {
        if ($id) {
            $result = $service->update($id, $data['titlu'], $data['descriere'], $data['pret'], $data['locatie']);
        } else {
            $userId = $_SESSION['user_id'];
            $result = $service->create($userId, $data['titlu'], $data['descriere'], $data['pret'], $data['locatie']);
        }

        if ($result['success']) {
            header('Location: /admin/anunturi.php');
            exit;
        }
        $errors[] = $result['error'] ?: 'A apărut o eroare necunoscută.';
    }
}

include __DIR__ . '/../../templates/admin/anunt_form.html';