<?php
// public/detail.php

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/init.php';

use App\service\ListingService;  // matches namespace in src/service/ListingService.php

session_start();

$listingService = new ListingService($pdo);
$id = $_GET['id'] ?? null;

if (!$id || !($listing = $listingService->getById($id))) {
    http_response_code(404);
    echo "Listing not found.";
    exit;
}

// First image (if any)
$mainImage = $listing->images[0] ?? null;
$imageUrl  = $mainImage ? '/uploads/' . $mainImage : null;

$lat = htmlspecialchars($listing->latitude);
$lng = htmlspecialchars($listing->longitude);
?>
<?php include __DIR__ . '/../templates/header.php'; ?>

<article class="listing-detail">
    <h1><?= htmlspecialchars($listing->title) ?></h1>

    <?php if ($imageUrl): ?>
    <div class="listing-main-image">
        <img src="<?= htmlspecialchars($imageUrl) ?>"
             alt="Image for <?= htmlspecialchars($listing->title) ?>">
    </div>
    <?php endif; ?>

    <ul class="listing-meta">
        <li><strong>Price:</strong> <?= number_format($listing->price, 2) ?> EUR</li>
        <li><strong>Coordinates:</strong> <?= $lat ?>, <?= $lng ?></li>
        <li><strong>Condition:</strong> <?= htmlspecialchars($listing->building_condition) ?></li>
        <li><strong>Facilities:</strong> <?= htmlspecialchars(implode(', ', $listing->facilities ?? [])) ?></li>
        <li><strong>Risks:</strong> <?= htmlspecialchars($listing->risks) ?></li>
    </ul>

    <section>
        <h2>Description</h2>
        <p><?= nl2br(htmlspecialchars($listing->description)) ?></p>
    </section>

    <section>
        <h2>Location on Map</h2>
        <div id="map" style="height: 300px;"></div>
        <script>
            var map = L.map('map').setView([<?= $lat ?>, <?= $lng ?>], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            L.marker([<?= $lat ?>, <?= $lng ?>]).addTo(map);
        </script>
    </section>
</article>

<?php include __DIR__ . '/../templates/footer.php'; ?>
