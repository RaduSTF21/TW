<?php


require __DIR__ . '/../config/config.php';        


use App\Service\UserService;                         

$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $name     = trim($_POST['name'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adresa de email invalidă.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Parola trebuie să aibă cel puțin 6 caractere.';
    }

    if (empty($errors)) {
        $userService = new UserService($pdo);
        $result = $userService->register($email, $password, $name);

        if ($result->success) {
            $success = true;
        } else {
            $errors[] = $result->error;
        }
    }
}


$data = [
    'errors'  => $errors,
    'success' => $success,
    'old'     => ['email' => $email ?? '', 'name' => $name ?? ''],
];


extract($data);
include __DIR__ . '/../templates/register.html';
