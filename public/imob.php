<?php
// public/imob.php

require_once __DIR__ . '/../csrf.php';
require_once __DIR__ . '/../bootstrap.php';
session_start();

// Nu procesăm POST aici. Vom folosi AJAX pentru GET la API.
// Generăm token CSRF (pentru eventuale POST-uri)
$csrfToken = csrf_get_token();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Imobiliare - Apartamente de închiriat</title>
  <!-- Link către CSS-ul existent -->
  <!-- Dacă imob.css se află în assets/css/imob.css și public root servește /assets/... -->
  <link rel="stylesheet" href="/TW/assets/css/imob.css" />
  <!-- Dacă ai și alte CSS inline din template, le poți păstra: -->
  <style>
    /* Dacă template-ul imob.html avea <style> pentru footer, include-l aici: */
    footer {
      background-color: #2c3e50;
      color: white;
      padding: 40px 20px 20px;
      margin-top: 50px;
    }
    .footer-section ul {
      list-style: none;
      padding: 0;
    }
    .footer-section a {
      color: #ecf0f1;
      text-decoration: none;
      transition: color 0.3s;
    }
    .footer-section a:hover {
      color: #3498db;
    }
    .contact-info {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
    }
    .contact-info i {
      margin-right: 10px;
      width: 20px;
    }
    .footer-bottom {
      text-align: center;
      margin-top: 20px;
      font-size: 0.9rem;
    }
    .social-links a:hover {
      background-color: #2980b9;
    }
  </style>
</head>
<body>
  <!-- Header din template -->
  <header>
    <div class="logo">ImobiliareIasi.ro</div>
  </header>
  <!-- Navigation din template; adaptează link-urile după rutele tale -->
  <nav>
    <a href="/TW/public/imob.php">Acasă</a>
    <a href="/TW/public/anunturi.php">Anunțuri</a>
    <a href="/TW/public/login_form.php">Login</a>
    <a href="/TW/public/register_form.php">Înregistrare</a>
    <!-- alte linkuri -->
  </nav>

  <!-- Main content: folosim clasa .main-content dacă e definită în imob.css -->
  <main class="main-content">
    <h1>Lista Proprietăți</h1>
    <div id="message"></div>

    <!-- Filtre: adaptează clase/structură conform CSS-ul tău -->
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
      <!-- Alte filtre după nevoie -->
      <button id="btnApplyFilters" type="button">Aplică filtre</button>
    </div>

    <!-- Buton geolocation -->
    <button id="btnLocate" type="button">Arată proprietăți aproape de mine</button>

    <!-- Container pentru listarea proprietăților -->
    <div id="propertiesContainer" class="properties-grid">
      <!-- Populat de JS -->
    </div>
  </main>

  <!-- Footer din template: adaptează structura reală din imob.html -->
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
      <p>Dezvoltat cu ❤️ pentru comunitatea ieșeană</p>
    </div>
  </footer>

  <!-- Script JS: poți pune într-un fișier separat, ex assets/js/imob.js și include <script src="/assets/js/imob.js"></script> -->
  <script>
  (function() {
    const PROPERTIES_API = '/api/properties.php';
    const NEARBY_API = '/api/properties_nearby.php';

    async function fetchProperties(filters = {}) {
      const params = new URLSearchParams();
      if (filters.transaction) params.append('transaction', filters.transaction);
      if (filters.price_max) params.append('price_max', filters.price_max);
      if (filters.rooms_min) params.append('rooms_min', filters.rooms_min);
      const url = PROPERTIES_API + '?' + params.toString();
      const messageDiv = document.getElementById('message');
      messageDiv.innerHTML = 'Se încarcă...';
      try {
        const resp = await fetch(url, {
          method: 'GET',
          credentials: 'same-origin'
        });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const data = await resp.json();
        renderProperties(data);
        messageDiv.innerHTML = '';
      } catch (err) {
        messageDiv.innerHTML = '<p class="error">Eroare la încărcare: ' + err.message + '</p>';
      }
    }

    function renderProperties(props) {
      const container = document.getElementById('propertiesContainer');
      container.innerHTML = '';
      if (!Array.isArray(props) || props.length === 0) {
        container.innerHTML = '<p>Nu s-au găsit proprietăți.</p>';
        return;
      }
      props.forEach(p => {
        const card = document.createElement('div');
        card.className = 'property-card'; // adaptează dacă ai altă clasă CSS
        if (p.image_url) {
          const img = document.createElement('img');
          img.src = p.image_url;
          img.alt = p.title || 'Proprietate';
          img.loading = 'lazy';
          card.appendChild(img);
        }
        const title = document.createElement('h3');
        title.textContent = p.title || 'Fără titlu';
        card.appendChild(title);
        const price = document.createElement('p');
        price.textContent = 'Preț: ' + (p.price ? p.price + ' €' : 'N/A');
        card.appendChild(price);
        if (p.rooms) {
          const rooms = document.createElement('p');
          rooms.textContent = 'Camere: ' + p.rooms;
          card.appendChild(rooms);
        }
        // Link detaliu
        const link = document.createElement('a');
        link.href = '/public/detail.php?id=' + encodeURIComponent(p.id);
        link.textContent = 'Vezi detaliu';
        card.appendChild(link);

        container.appendChild(card);
      });
    }

    document.getElementById('btnApplyFilters').addEventListener('click', () => {
      const filters = {
        transaction: document.getElementById('filterTransaction').value,
        price_max: document.getElementById('filterPriceMax').value,
        rooms_min: document.getElementById('filterRoomsMin').value
      };
      fetchProperties(filters);
    });

    document.addEventListener('DOMContentLoaded', () => {
      fetchProperties({});
    });

    document.getElementById('btnLocate').addEventListener('click', () => {
      if (!navigator.geolocation) {
        alert('Geolocation nu e suportat de browser');
        return;
      }
      navigator.geolocation.getCurrentPosition(async pos => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        const radiusKm = 5;
        const params = new URLSearchParams({ lat, lng, radius: radiusKm });
        const messageDiv = document.getElementById('message');
        messageDiv.innerHTML = 'Căut proprietăți în apropiere...';
        try {
          const resp = await fetch(NEARBY_API + '?' + params.toString(), {
            method: 'GET',
            credentials: 'same-origin'
          });
          if (!resp.ok) throw new Error('HTTP ' + resp.status);
          const data = await resp.json();
          renderProperties(data);
          messageDiv.innerHTML = '';
        } catch (err) {
          messageDiv.innerHTML = '<p class="error">Eroare: ' + err.message + '</p>';
        }
      }, err => {
        alert('Eroare geolocație: ' + err.message);
      });
    });
  })();
  </script>
</body>
</html>
