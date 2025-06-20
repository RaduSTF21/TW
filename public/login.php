<?php
// Disable HTML errors
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Bootstrap & session
require __DIR__ . '/../bootstrap.php';
session_start();

// Bring PDO into scope
$pdo = $GLOBALS['pdo'];

$errors = [];
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $errors[] = 'Token CSRF invalid.';
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($errors)) {
        // Note: table column is `name`, and password column is `password_hash`
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE name = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $username;
            // Rotate CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: anunturi.php');
            exit;
        }
        $errors[] = 'Credențiale invalide.';
    }
}

// If GET or validation errors, render the form:
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Autentificare – ImobiliareIasi.ro</title>
  <link rel="stylesheet" href="imob.css"/>
</head>
<body>
  <!-- Header -->
  <header><div class="logo">ImobiliareIasi.ro</div></header>
  <!-- Navigation -->
  <nav>
    <a href="imob.html">Acasă</a>
    <a href="anunturi.html">Anunțuri</a>
    <a href="register_form.php">Înregistrare</a>
    <a href="login.php" class="active">Autentificare</a>
    <a href="#">Contact</a>
  </nav>

  <div class="main-content">
    <div class="search-box" style="max-width:400px;margin:auto;">
      <h2>Autentificare</h2>

      <?php if ($errors): ?>
        <div style="color:red;"><ul><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="csrf_token"
               value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">
        <div class="filter-row" style="flex-direction:column;">
          <div class="filter-item">
            <label for="username">Username:</label>
            <input id="username" name="username" type="text" value="<?=htmlspecialchars($username)?>" required>
          </div>
          <div class="filter-item">
            <label for="password">Parolă:</label>
            <input id="password" name="password" type="password" required>
          </div>
        </div>
        <button type="submit">Autentifică-te</button>
      </form>
    </div>
  </div>
</body>
</html>
