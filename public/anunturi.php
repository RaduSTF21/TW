<?php

require __DIR__ . '/../config/config.php';



use App\Service\ListingService;

$service  = new ListingService($pdo);
$anunturi = $service->getAll();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Anunțuri Imobiliare</title>
  <link rel="stylesheet" href="/assets/css/anunturi.css">
</head>
<body>
  <header>
    <h1>Anunțuri imobiliare</h1>
    <nav>
      <a href="/register.php">Înregistrare</a> |
      <a href="/login.php">Autentificare</a>
    </nav>
  </header>

  <?php include __DIR__ . '/../templates/anunturi.html'; ?>

  <footer>
    <p>&copy; <?= date('Y') ?> Agenție Imobiliară</p>
  </footer>
</body>
</html>
