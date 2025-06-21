<?php
// public/account.php

// 1) Load Composer + .env + session + helper functions:
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/init.php';
require __DIR__ . '/../csrf.php';

use App\Service\UserService;    // PSR-4 autoloaded via composer

session_start();

// Redirect non‑logged‑in users
if (empty($_SESSION['user_id'])) {
    header('Location: login_form.php');
    exit;
}

$userService = new UserService($pdo);
$user = $userService->getUserById($_SESSION['user_id']);
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF (will exit with 403 if invalid)
    csrf_validate();

    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '') {
        $errors[] = 'Name cannot be empty.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    if (empty($errors)) {
        $userService->updateUser($user->id, [
            'name'  => $name,
            'email' => $email,
        ]);
        header('Location: account.php?updated=1');
        exit;
    }
}

// Generate fresh CSRF token for the form
$csrfToken = csrf_get_token();

?>
<?php include __DIR__ . '/../templates/header.php'; ?>

<h1>Account Management</h1>

<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">
        Your account has been updated.
    </div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="account.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <div class="form-group">
        <label for="name">Name</label>
        <input id="name" name="name" value="<?= htmlspecialchars($user->name) ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="<?= htmlspecialchars($user->email) ?>" required>
    </div>
    <button type="submit">Save Changes</button>
</form>

<?php include __DIR__ . '/../templates/footer.php'; ?>
