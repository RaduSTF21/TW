<?php


require  '/xampp/htdocs/TW/bootstrap.php';

use App\service\ListingService;

$data = [
    'title'            => 'Cozy Apartment in Downtown',
    'description'      => 'Close to everything, 2 bedrooms, 1 bath, modern kitchen.',
    'price'            => 98000,
    'rooms'            => 2,
    'transaction_type' => 2,          // e.g. 1=sale, 2=rent, 3=lease
    'property_type'    => 1,          // e.g. 1=apartment, 2=house, etc.
    'latitude'         => 45.7489,
    'longitude'        => 21.2087,
];


$amenityIds = [1, 3];  // e.g. 1=parking, 3=garden
$riskIds    = [1];     // e.g. 1=flood zone

$propertyId = ListingService::saveProperty($pdo, $data, $amenityIds, $riskIds);

echo "✅ Test property inserted with ID {$propertyId}\n";
