<?php
// public/anunturi.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Build nav (same as imob.php)
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
  <title>Anunțuri Imobiliare</title>

  <!-- Main CSS -->
  <link rel="stylesheet" href="admin/assets/css/imob.css"/>

  <!-- Leaflet CSS -->
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
  />

  <style>
    #map { height: 400px; margin-bottom: 2rem; }
    .search-box { margin-bottom: 1.5rem; }
  </style>
</head>
<body>
  <header>
    <div class="logo">ImobMap</div>
    <nav><?= $nav ?></nav>
  </header>

  <div class="main-content">
    <h2>Toate Anunțurile</h2>

    <!-- Filters -->
    <div class="search-box">
      <div class="filter-row">
        <div class="filter-item">
          <label for="filterTransaction">Tip tranzacție:</label>
          <select id="filterTransaction">
            <option value="">Toate</option>
            <option value="inchiriere">Închiriere</option>
            <option value="vanzare">Vânzare</option>
          </select>
        </div>
        <div class="filter-item">
          <label for="filterPriceMax">Preț maxim (€):</label>
          <input type="number" id="filterPriceMax" placeholder="Orice" />
        </div>
        <div class="filter-item">
          <label for="filterRoomsMin">Camere min.:</label>
          <input type="number" id="filterRoomsMin" placeholder="Orice" />
        </div>
        <div class="filter-item">
          <button id="btnApplyFilters">Aplică filtre</button>
        </div>
      </div>
    </div>

    <!-- Map -->
    <div id="map"></div>

    <!-- Listings -->
    <div id="property-container" class="properties-grid"></div>
  </div>

  <footer>
    <!-- your footer here -->
  </footer>

  <!-- Leaflet JS -->
  <script
    src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
  ></script>

  <script>
  (function(){
    const map = L.map('map').setView([47.1585, 27.6014], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const container = document.getElementById('property-container');
    const btnApply = document.getElementById('btnApplyFilters');

    async function load(filters = {}) {
      // Clear markers
      map.eachLayer(l => {
        if (l instanceof L.Marker) map.removeLayer(l);
      });

      const qs = new URLSearchParams(filters).toString();
      const resp = await fetch('api/properties.php?' + qs);
      const listings = await resp.json();

      container.innerHTML = '';
      listings.forEach(p => {
        // Marker
        if (p.latitude && p.longitude) {
          const m = L.marker([p.latitude, p.longitude]).addTo(map);
          m.on('click', () => location.href = `detail.php?id=${p.id}`);
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

        const btn = document.createElement('a');
        btn.className = 'btn-details';
        btn.href = `detail.php?id=${p.id}`;
        btn.textContent = 'Vezi detaliu';
        info.appendChild(btn);

        card.appendChild(info);
        container.appendChild(card);
      });
    }

    // initial load
    load();

    btnApply.addEventListener('click', () => {
      const filters = {
        transaction: document.getElementById('filterTransaction').value,
        price_max:   document.getElementById('filterPriceMax').value,
        rooms_min:   document.getElementById('filterRoomsMin').value
      };
      load(filters);
    });
  })();
  </script>
</body>
</html>
