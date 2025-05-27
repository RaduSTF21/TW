<?php
$pdo = new PDO('mysql:host=localhost;dbname=real_estate', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("
  INSERT INTO properties
    (title, description, price, rooms, transaction_type, property_type, latitude, longitude)
  VALUES
    (:title, :description, :price, :rooms, :trans, :ptype, :lat, :lng)
");

$stmt->execute([
  ':title'       => 'Cozy Apartment in Downtown',
  ':description' => 'Close to everything, 2 bedrooms, 1 bath, modern kitchen.',
  ':price'       => 98000,
  ':rooms'       => 2,
  ':trans'       => 'inchiriere',    // or 'vanzare'
  ':ptype'       => 'apartament',
  ':lat'         => 45.7489,
  ':lng'         => 21.2087
]);

echo "✅ Test property inserted.";
