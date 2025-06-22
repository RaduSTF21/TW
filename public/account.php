<?php
// public/account.php

require_once __DIR__ . '/../bootstrap.php';

use App\service\UserService;
use App\service\ListingService;

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login_form.php');
    exit;
}

$pdo         = get_db_connection();
$userService = new UserService($pdo);

// Fetch current user
$user = $userService->getUserById((int)$_SESSION['user_id']);

// Fetch this user’s listings
$listings = ListingService::getByUserId($pdo, (int)$_SESSION['user_id']);

$errors    = [];
$form_name = $user->name;
$alerts    = '';

// Handle account‐info form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['form_type'] ?? '') === 'account'
) {
    csrf_validate();

    $form_name = trim($_POST['name'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if ($form_name === '') {
        $errors[] = 'Name cannot be empty.';
    }
    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== '' && $password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $data = ['name' => $form_name];
        if ($password !== '') {
            $data['password'] = $password;
        }
        $userService->updateUser($user->id, $data);
        header('Location: account.php?updated=1');
        exit;
    }
}

// Build alerts HTML
if (!empty($_GET['updated'])) {
    $alerts = '<div class="alert alert-success">Your account has been updated.</div>';
} elseif (!empty($errors)) {
    $alerts = '<div class="alert alert-danger"><ul>';
    foreach ($errors as $e) {
        $alerts .= '<li>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    $alerts .= '</ul></div>';
}

// Build the listings HTML
$listingsHtml = '<h2>Your Listings</h2>';
if (empty($listings)) {
    $listingsHtml .= '<p>You have no listings yet.</p>';
} else {
    $listingsHtml .= '<ul class="user-listings">';
    foreach ($listings as $ad) {
        $title = htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8');
        $id    = (int)$ad['id'];
        $listingsHtml .= "
<li>
  {$title}
  <a class=\"btn-edit\" href=\"edit_listing.php?id={$id}\">Edit</a>
</li>
";
    }
    $listingsHtml .= '</ul>';
}

// Load and render template
$csrfToken = $_SESSION['csrf_token'];
$template  = file_get_contents(__DIR__ . '/templates/account.html');

echo str_replace(
    [
      '<!--ALERTS-->',
      '<!--CSRF_TOKEN-->',
      '<!--FORM_NAME-->',
      '<!--USER_LISTINGS-->'
    ],
    [
      $alerts,
      htmlspecialchars($csrfToken, ENT_QUOTES),
      htmlspecialchars($form_name, ENT_QUOTES),
      $listingsHtml
    ],
    $template
);
