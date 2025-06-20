<?php
require __DIR__ . '/../bootstrap.php';
session_start();

// 1) Ensure CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2) Validate CSRF token
    if (empty($_POST['csrf_token'])
     || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token invalid. Operațiune oprită.');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $pdo->prepare('DELETE FROM properties WHERE id = ?')->execute([$id]);

        // 3) Rotate token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header('Location: properties.php?deleted=1');
        exit;
    }

    $errors[] = 'ID invalid.';
}

// If GET, show confirmation
$id = (int)($_GET['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <title>Șterge anunț</title>
</head>
<body>
  <h1>Confirmă ștergerea proprietății</h1>

  <?php if ($errors): ?>
    <div style="color:red;">
      <ul><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <button type="submit">Șterge</button>
    <a href="properties.php">Anulează</a>
  </form>
</body>
</html>
