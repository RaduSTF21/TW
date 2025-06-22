<?php
// public/imob.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../bootstrap.php';

// Start session for nav logic
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Build nav
$user = $_SESSION['user_id'] ?? null;
if ($user) {
    $navLinks = [
        '<a href="imob.php">Acasă</a>',
        '<a href="anunturi.php">Anunțuri</a>',
        '<a href="adauga_anunt.php">Adaugă Anunț</a>',
        '<a href="account.php">Profil</a>',
        '<a href="logout.php">Logout</a>',
    ];
} else {
    $navLinks = [
        '<a href="imob.php">Acasă</a>',
        '<a href="anunturi.php">Anunțuri</a>',
        '<a href="templates/register.html">Înregistrare</a>',
        '<a href="templates/login.html">Autentificare</a>',
    ];
}
$nav = implode(' ', $navLinks);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>ImobMap – Pagina principală</title>

  <!-- Main CSS -->
  <link rel="stylesheet" href="admin/assets/css/imob.css"/>

  <!-- Leaflet CSS -->
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
  />

  <style>
    /* Ensure map has explicit height */
    #map { height: 400px; margin-bottom: 2rem; }
  </style>
</head>
<body>
  <header>
    <div class="logo">ImobMap</div>
    <nav><?= $nav ?></nav>
  </header>

  <!-- Map -->
  <div id="map"></div>

  <!-- Featured Properties -->
  <div class="main-content">
    <h2>Proprietăți recomandate</h2>
    <div id="property-container" class="properties-grid"></div>
  </div>

  <footer>
    <!-- your footer content -->
  </footer>

  <!-- Leaflet JS -->
  <script
    src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
  ></script>

  <script>
  (function(){
    const map = L.map('map').setView([47.1585, 27.6014], 12); // center on Iași
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const container = document.getElementById('property-container');

    // Fetch only 3 featured listings
    fetch('api/properties.php?limit=3')
      .then(r => r.json())
      .then(listings => {
        container.innerHTML = '';
        listings.forEach(p => {
          // Marker
          if (p.latitude && p.longitude) {
            const m = L.marker([p.latitude, p.longitude]).addTo(map);
            m.on('click', () => {
              window.location.href = `detail.php?id=${p.id}`;
            });
          }

          // Card
          const card = document.createElement('div');
          card.className = 'property-card';

          if (p.image_url) {
            const imgWrap = document.createElement('div');
            imgWrap.className = 'property-image';
            const img = document.createElement('img');
            img.src = p.image_url;
            img.alt = p.title;
            imgWrap.appendChild(img);
            card.appendChild(imgWrap);
          }

          const info = document.createElement('div');
          info.className = 'property-info';

          const title = document.createElement('h3');
          title.textContent = p.title;
          info.appendChild(title);

          const price = document.createElement('p');
          price.className = 'price';
          price.textContent = `€${p.price.toLocaleString('ro-RO', {minimumFractionDigits:2})}`;
          info.appendChild(price);

          const snippet = document.createElement('p');
          snippet.className = 'snippet';
          snippet.textContent = p.description.slice(0, 80) + '…';
          info.appendChild(snippet);

          const btn = document.createElement('a');
          btn.className = 'btn-details';
          btn.href = `detail.php?id=${p.id}`;
          btn.textContent = 'Vezi detaliu';
          info.appendChild(btn);

          card.appendChild(info);
          container.appendChild(card);
        });
      })
      .catch(err => {
        container.innerHTML = `<p class="error">Eroare încărcare proprietăți: ${err.message}</p>`;
      });
  })();
  </script>
</body>
</html>
