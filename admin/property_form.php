<?php
require __DIR__ . '/../bootstrap.php';
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

use App\service\ListingService;

$pdo = $GLOBALS['pdo'];
$errors = [];

// Fetch lookups
$transactions = ListingService::getTransactionTypes($pdo);
$propTypes    = ListingService::getPropertyTypes($pdo);
$amenities    = ListingService::getAmenities($pdo);
$risks        = ListingService::getRisks($pdo);

// Initialize form data
$data = [
  'id' => null,
  'title' => '',
  'description' => '',
  'price' => '',
  'rooms' => '',
  'transaction_type' => '',
  'property_type' => '',
  'latitude' => '',
  'longitude' => '',
];
$existingAmenityIds = [];
$existingRiskIds    = [];
$existingImages     = [];

// If editing existing property
if (!empty($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $data = array_merge($data, $row);
        // load pivots
        $stmtA = $pdo->prepare("SELECT amenity_id FROM property_amenities WHERE property_id = ?");
        $stmtA->execute([$id]);
        $existingAmenityIds = array_column($stmtA->fetchAll(), 'amenity_id');
        $stmtR = $pdo->prepare("SELECT risk_id FROM property_risks WHERE property_id = ?");
        $stmtR->execute([$id]);
        $existingRiskIds = array_column($stmtR->fetchAll(), 'risk_id');
        // load existing images
        $stmtI = $pdo->prepare("SELECT id, filename FROM property_images WHERE property_id = ?");
        $stmtI->execute([$id]);
        $existingImages = $stmtI->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect & sanitize
    foreach (['title','description','price','rooms','transaction_type','property_type','latitude','longitude'] as $field) {
        $data[$field] = trim($_POST[$field] ?? '');
    }
    $amenityIds = $_POST['amenities'] ?? [];
    $riskIds    = $_POST['risks']    ?? [];

    // Basic validation
    if (!$data['title'])   $errors[] = 'Titlu obligatoriu.';
    if (!is_numeric($data['price'])) $errors[] = 'Prețul trebuie să fie numeric.';
    if (!is_numeric($data['rooms'])) $errors[] = 'Numărul de camere trebuie să fie numeric.';
    if (!$data['transaction_type']) $errors[] = 'Selectează tipul tranzacției.';
    if (!$data['property_type'])    $errors[] = 'Selectează tipul proprietății.';

    if (empty($errors)) {
        // Save via service
        $propertyId = ListingService::saveProperty(
            $pdo,
            $data,
            $amenityIds,
            $riskIds
        );
        // Handle images
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = __DIR__ . '/uploads/';
            foreach ($_FILES['images']['tmp_name'] as $index => $tmp) {
                $origName = $_FILES['images']['name'][$index];
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                    $filename = uniqid('img_') . '.' . $ext;
                    move_uploaded_file($tmp, $uploadDir . $filename);
                    // insert record
                    $stmtImg = $pdo->prepare("INSERT INTO property_images (property_id, filename) VALUES (?, ?)");
                    $stmtImg->execute([$propertyId, $filename]);
                }
            }
        }
        header('Location: properties.php?success=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $data['id'] ? 'Editează' : 'Adaugă' ?> anunț</title>
  <link rel="stylesheet" href="../imob.css" />
</head>
<body>
  <!-- Header -->
  <header>
    <div class="logo">ImobiliareIasi.ro</div>
  </header>
  <!-- Navigation -->
  <nav>
    <a href="../imob.html">Acasă</a>
    <a href="../anunturi.html">Anunțuri</a>
    <a href="properties.php">Adaugă anunț</a>
    <a href="#">Contact</a>
  </nav>

  <div class="main-content">
    <h1><?= $data['id'] ? 'Editează anunț' : 'Adaugă anunț' ?></h1>

    <?php if ($errors): ?>
      <div class="errors">
        <ul>
          <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <!-- Fields -->
      <label>Titlu:<br>
        <input name="title" value="<?= htmlspecialchars($data['title']) ?>" />
      </label><br>
      <label>Descriere:<br>
        <textarea name="description"><?= htmlspecialchars($data['description']) ?></textarea>
      </label><br>
      <label>Preț (€):<br>
        <input name="price" value="<?= htmlspecialchars($data['price']) ?>" />
      </label><br>
      <label>Camere:<br>
        <input name="rooms" value="<?= htmlspecialchars($data['rooms']) ?>" />
      </label><br>
      <label>Tip tranzacție:<br>
        <select name="transaction_type">
          <option value="">Toate</option>
          <?php foreach ($transactions as $t): ?>
            <option value="<?= $t['id']?>" <?= $data['transaction_type']==$t['id']?'selected':''?>><?= htmlspecialchars($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label><br>
      <label>Tip proprietate:<br>
        <select name="property_type">
          <option value="">Toate</option>
          <?php foreach ($propTypes as $pt): ?>
            <option value="<?= $pt['id']?>" <?= $data['property_type']==$pt['id']?'selected':''?>><?= htmlspecialchars($pt['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label><br>
      <fieldset><legend>Facilități</legend>
        <?php foreach ($amenities as $a): ?>
          <label><input type="checkbox" name="amenities[]" value="<?= $a['id']?>" <?= in_array($a['id'],$existingAmenityIds)?'checked':''?> /> <?= htmlspecialchars($a['name'])?></label>
        <?php endforeach; ?>
      </fieldset><br>
      <fieldset><legend>Riscuri</legend>
        <?php foreach ($risks as $r): ?>
          <label><input type="checkbox" name="risks[]" value="<?= $r['id']?>" <?= in_array($r['id'],$existingRiskIds)?'checked':''?> /> <?= htmlspecialchars($r['name'])?></label>
        <?php endforeach; ?>
      </fieldset><br>

      <!-- Image Upload -->
      <label>Imagini:<br>
        <input type="file" name="images[]" multiple accept="image/*" />
      </label><br>
      <?php if ($existingImages): ?>
        <div class="img-preview">
          <?php foreach ($existingImages as $img): ?>
            <div class="thumb">
              <img src="uploads/<?= htmlspecialchars($img['filename']) ?>" alt="" />
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <button type="submit"><?= $data['id'] ? 'Actualizează' : 'Publică' ?></button>
      <a href="properties.php">Anulează</a>
    </form>
  </div>
</body>
</html>

