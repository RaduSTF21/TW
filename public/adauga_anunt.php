<?php
require_once __DIR__ . '/../csrf.php';


// Require login
if (empty($_SESSION['user_id'])) {
    header('Location: login_form.php');
    exit;
}

// Get CSRF token (csrf.php will start the session if needed)
$csrf = csrf_get_token();

// If you’re redirecting back to this form on errors, you can pull them from session:
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old']    ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Adaugă Anunț</title>
  <link rel="stylesheet" href="/TW/assets/css/imob.css">
</head>
<body>
  <header><h1>Adaugă Anunț</h1></header>
  <nav>
    <a href="imob.php">Acasă</a> |
    <a href="anunturi.php">Anunțuri</a> |
    <a href="logout.php">Logout</a>
  </nav>

  <main>
    <?php if ($errors): ?>
      <div class="error"><ul>
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul></div>
    <?php endif; ?>

    <form action="adauga_anunt.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

      <label>
        Titlu:
        <input type="text" name="title" required
               value="<?= htmlspecialchars($old['title'] ?? '', ENT_QUOTES) ?>">
      </label><br>

      <label>
        Descriere:
        <textarea name="description" rows="5" required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
      </label><br>

      <label>
        Preț (€):
        <input type="number" step="0.01" name="price" required
               value="<?= htmlspecialchars($old['price'] ?? '', ENT_QUOTES) ?>">
      </label><br>

      <label>
        Camere:
        <input type="number" name="rooms" required
               value="<?= htmlspecialchars($old['rooms'] ?? '', ENT_QUOTES) ?>">
      </label><br>

      <label>
        Tip tranzacție:
        <select name="transaction" required>
          <option value="">Alege...</option>
          <option value="sale" <?= isset($old['transaction']) && $old['transaction']==='sale' ? 'selected' : '' ?>>Vânzare</option>
          <option value="rent" <?= isset($old['transaction']) && $old['transaction']==='rent' ? 'selected' : '' ?>>Închiriere</option>
        </select>
      </label><br>

      <label>
        Tip proprietate:
        <input type="text" name="property_type" required
               value="<?= htmlspecialchars($old['property_type'] ?? '', ENT_QUOTES) ?>">
      </label><br>

      <label>
        Imagine:
        <input type="file" name="image" accept="image/*">
      </label><br>

      <button type="submit">Adaugă Anunț</button>
    </form>
  </main>
</body>
</html>