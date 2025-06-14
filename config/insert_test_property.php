<?php
// insert_test_property.php

require __DIR__ . '/bootstrap.php';

use App\service\ListingService;

// --- 1) Define your property data ---
$data = [
    'title'            => 'Cozy Apartment in Downtown',
    'description'      => 'Close to everything, 2 bedrooms, 1 bath, modern kitchen.',
    'price'            => 98000,
    'rooms'            => 2,
    // These are the lookup‐table IDs—make sure these exist (seeded by auto_setup):
    'transaction_type' => 2,          // e.g. 1=sale, 2=rent, 3=lease
    'property_type'    => 1,          // e.g. 1=apartment, 2=house, etc.
    'latitude'         => 45.7489,
    'longitude'        => 21.2087,
];

// --- 2) Pick some amenity & risk IDs to attach ---
// (Ensure these IDs exist in your amenities/risks tables; adjust as needed)
$amenityIds = [1, 3];  // e.g. 1=parking, 3=garden
$riskIds    = [1];     // e.g. 1=flood zone

// --- 3) Save via the ListingService ---
$propertyId = ListingService::saveProperty($pdo, $data, $amenityIds, $riskIds);

echo "✅ Test property inserted with ID {$propertyId}\n";
