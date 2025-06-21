<?php
// File: public/imob.php

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../csrf.php';

$csrfToken = csrf_get_token();

// Definește aici calea de bază către folderul TW, o poți ajusta după nevoie
$base_url = '/TW';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ImobiliareIasi.ro – Apartamente de închiriat</title>
  <link rel="stylesheet" href="<?= $base_url ?>/public/admin/assets/css/imob.css">

  <style>
    /* Stilizare link Vezi toate anunțurile ca buton */
    .btn-see-all {
      display: inline-block;
      background-color: #0056b3;
      color: white;
      padding: 0.7rem 1.5rem;
      font-weight: 700;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      text-align: center;
      transition: background-color 0.3s ease;
    }
    .btn-see-all:hover {
      background-color: #003d80;
    }
  </style>

</head>
<body>
  <header>
    <div class="logo">ImobiliareIasi.ro</div>
  </header>
  <nav>
    <a href="imob.php">Acasă</a>
    <a href="anunturi.php">Anunțuri</a>
    <?php if ($isLoggedIn): ?>
      <a href="<?= $base_url ?>/public/templates/adauga_anunt.html">Adaugă Anunț</a>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login_form.php">Login</a>
      <a href="register_form.php">Înregistrare</a>
    <?php endif; ?>
  </nav>

  <main class="main-content">
    <h1>Lista Proprietăți</h1>
    <div id="message"></div>

    <div class="filters">
      <label>
        Tip tranzacție:
        <select id="filterTransaction">
          <option value="">Orice</option>
          <option value="sale">Vânzare</option>
          <option value="rent">Închiriere</option>
        </select>
      </label>
      <label>
        Preț maxim:
        <input type="number" id="filterPriceMax" placeholder="ex: 100000">
      </label>
      <label>
        Camere min:
        <input type="number" id="filterRoomsMin" placeholder="ex: 2">
      </label>
      <button id="btnApplyFilters" type="button">Aplică filtre</button>
    </div>

    <button id="btnLocate" type="button">Arată proprietăți aproape de mine</button>

    <div id="properties-container" class="properties-grid"></div>

    <div class="see-all">
      <a href="anunturi.php" class="btn-see-all">Vezi toate anunțurile</a>
    </div>
  </main>

  <footer>
    <!-- existing footer markup -->
  </footer>

  <!-- Your external JS -->
  <script src="<?= $base_url ?>/public/assets/js/imob.js"></script>
</body>
</html>
