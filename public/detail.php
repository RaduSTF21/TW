<?php
// public/detail.php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/service/ListingService.php';

use App\service\ListingService;

// 1) Get & validate ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    echo 'ID proprietate invalid.';
    exit;
}

$pdo      = get_db_connection();
$property = ListingService::getById($pdo, $id);

if (!$property) {
    http_response_code(404);
    echo 'Proprietate inexistentă.';
    exit;
}

$images = ListingService::getImages($pdo, $id);

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Detalii – <?= h($property['title']) ?></title>

  <!-- Leaflet CSS (no SRI) -->
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
  />
  <link rel="stylesheet" href="admin/assets/css/detail.css"/>
  <style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: auto; padding: 1rem; }
    .back { margin-bottom: 1rem; }
    .images { display: flex; gap: .5rem; overflow-x: auto; margin-bottom: 1rem; }
    .images img { max-height: 200px; border-radius: 4px; }
    .info { margin-bottom: 1rem; }
    #map { height: 300px; margin-top: 1rem; border: 1px solid #ccc; }
  </style>
</head>
<body>
  <button class="back" onclick="history.back()">← Înapoi</button>
  <h1><?= h($property['title']) ?></h1>

  <div class="images">
    <?php if (count($images)): ?>
      <?php foreach ($images as $img): ?>
        <img
          src="uploads/<?= h($img['filename']) ?>"
          alt="<?= h($img['alt_text'] ?: $property['title']) ?>"
          loading="lazy"
        />
      <?php endforeach; ?>
    <?php else: ?>
      <p>Nu există imagini.</p>
    <?php endif; ?>
  </div>

  <div class="info">
    <p><strong>Preț:</strong>
      €<?= number_format($property['price'], 2, ',', ' ') ?>
    </p>
    <p><strong>Descriere:</strong><br>
      <?= nl2br(h($property['description'])) ?>
    </p>
    <p><strong>Locație (lat, lng):</strong>
      <?= h($property['latitude']) ?>, <?= h($property['longitude']) ?>
    </p>
  </div>

  <div id="map"></div>

  <!-- Leaflet JS (no SRI) -->
  <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

  <script>
    // Debug output
    console.log(
      'Coords:',
      <?= json_encode((float)$property['latitude']) ?>,
      <?= json_encode((float)$property['longitude']) ?>
    );

    const lat = parseFloat(<?= json_encode($property['latitude']) ?>, 10);
    const lng = parseFloat(<?= json_encode($property['longitude']) ?>, 10);

    if (!isNaN(lat) && !isNaN(lng)) {
      const map = L.map('map').setView([lat, lng], 15);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);
      L.marker([lat, lng]).addTo(map);
    } else {
      document.getElementById('map').innerHTML = '<p>Coordonate invalide pentru hartă.</p>';
    }
  </script>
</body>
</html>
