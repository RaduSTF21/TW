<?php
require_once __DIR__ . '/../csrf.php';
require_once __DIR__ . '/../bootstrap.php';


$csrfToken = csrf_get_token();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Imobiliare - Apartamente de închiriat</title>
  <link rel="stylesheet" href="/TW/assets/css/imob.css" />
</head>
<body>
  <header>
    <div class="logo">ImobiliareIasi.ro</div>
  </header>
  <nav>
    <a href="/TW/public/imob.php">Acasă</a>
    <a href="/TW/public/anunturi.php">Anunțuri</a>
    <a href="/TW/public/login_form.php">Login</a>
    <a href="/TW/public/register_form.php">Înregistrare</a>
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
  </main>

  <footer>
    <div class="footer-section">
      <h3>Despre noi</h3>
      <p>ImobiliareIasi.ro – locale, actualizate, ușor de folosit.</p>
    </div>
    <div class="footer-section">
      <h3>Contact</h3>
      <div class="contact-info">
        <i class="fa fa-map-marker"></i><span>Iași, România</span>
      </div>
      <div class="contact-info">
        <i class="fa fa-phone"></i><span>+40 123 456 789</span>
      </div>
      <div class="contact-info">
        <i class="fa fa-envelope"></i><span>contact@imobiliareiasi.ro</span>
      </div>
    </div>
    <div class="footer-section">
      <h3>Urmărește-ne</h3>
      <div class="social-links">
        <a href="#" title="Facebook">f</a>
        <a href="#" title="Instagram">i</a>
        <a href="#" title="LinkedIn">in</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 ImobiliareIasi.ro. Toate drepturile rezervate.</p>
    </div>
  </footer>

  <!-- External JS -->
  <script src="/TW/assets/js/imob.js"></script>
</body>
</html>