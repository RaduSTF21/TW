<?php
require __DIR__ . '/../bootstrap.php';
session_start();

// 1) Ensure a CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

use App\service\ListingService;
$pdo    = $GLOBALS['pdo'];
$errors = [];
$data   = [
    'id'               => null,
    'title'            => '',
    'description'      => '',
    'price'            => '',
    'rooms'            => '',
    'transaction_type' => '',
    'property_type'    => '',
    'latitude'         => '',
    'longitude'        => ''
];

// Fetch lookup lists
$transactions = ListingService::getTransactionTypes($pdo);
$propTypes    = ListingService::getPropertyTypes($pdo);
$amenities    = ListingService::getAmenities($pdo);
$risks        = ListingService::getRisks($pdo);

// If editing, preload existing property + pivots + images
$existingAmenityIds = [];
$existingRiskIds    = [];
$existingImages     = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM properties WHERE id = ?');
    $stmt->execute([$id]);
    if ($row = $stmt->fetch()) {
        $data = array_merge($data, $row);
        // amenities
        $stmtA = $pdo->prepare('SELECT amenity_id FROM property_amenities WHERE property_id = ?');
        $stmtA->execute([$id]);
        $existingAmenityIds = array_column($stmtA->fetchAll(), 'amenity_id');
        // risks
        $stmtR = $pdo->prepare('SELECT risk_id FROM property_risks WHERE property_id = ?');
        $stmtR->execute([$id]);
        $existingRiskIds = array_column($stmtR->fetchAll(), 'risk_id');
        // images
        $stmtI = $pdo->prepare('SELECT id, filename FROM property_images WHERE property_id = ?');
        $stmtI->execute([$id]);
        $existingImages = $stmtI->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2) Validate CSRF token
    if (empty($_POST['csrf_token'])
     || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'CSRF token invalid. Operațiune oprită.';
    }

    // 3) Collect & sanitize inputs
    foreach (['title','description','price','rooms','transaction_type','property_type','latitude','longitude'] as $f) {
        $data[$f] = trim($_POST[$f] ?? '');
    }
    $amenityIds = $_POST['amenities'] ?? [];
    $riskIds    = $_POST['risks']    ?? [];

    // 4) (Your existing validation here… add any missing checks)

    if (empty($errors)) {
        // 5) Save via your service
        $propertyId = ListingService::saveProperty(
            $pdo,
            $data,
            $amenityIds,
            $riskIds
        );

        // 6) Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = __DIR__ . '/uploads/';
            foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                $orig = $_FILES['images']['name'][$i];
                $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                    $fn = uniqid('img_') . '.' . $ext;
                    move_uploaded_file($tmp, $uploadDir . $fn);
                    $pdo->prepare(
                        'INSERT INTO property_images(property_id,filename) VALUES(?,?)'
                    )->execute([$propertyId, $fn]);
                }
            }
        }

        // 7) Rotate CSRF token (one-time use)
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

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
  <header><div class="logo">ImobiliareIasi.ro</div></header>
  <nav>
    <a href="../imob.html">Acasă</a>
    <a href="../anunturi.html">Anunțuri</a>
    <a href="properties.php">Admin</a>
  </nav>
  <div class="main-content">
    <h1><?= $data['id'] ? 'Editează anunț' : 'Adaugă anunț' ?></h1>

    <?php if ($errors): ?>
      <div style="color:red;">
        <ul><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <!-- 2) Hidden CSRF field -->
      <input type="hidden" name="csrf_token"
             value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

      <!-- Your existing form fields for title, desc, etc. -->

      <!-- Amenities checkboxes -->
      <fieldset>
        <legend>Facilități</legend>
        <?php foreach ($amenities as $a): ?>
          <label>
            <input type="checkbox" name="amenities[]"
                   value="<?= $a['id'] ?>"
              <?= in_array($a['id'], $existingAmenityIds) ? 'checked' : '' ?>>
            <?= htmlspecialchars($a['name']) ?>
          </label>
        <?php endforeach; ?>
      </fieldset>

      <!-- Risks checkboxes -->
      <fieldset>
        <legend>Riscuri</legend>
        <?php foreach ($risks as $r): ?>
          <label>
            <input type="checkbox" name="risks[]"
                   value="<?= $r['id'] ?>"
              <?= in_array($r['id'], $existingRiskIds) ? 'checked' : '' ?>>
            <?= htmlspecialchars($r['name']) ?>
          </label>
        <?php endforeach; ?>
      </fieldset>

      <!-- Image upload -->
      <label>Imagini:<br>
        <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.gif">
      </label><br><br>

      <button type="submit"><?= $data['id'] ? 'Actualizează' : 'Publică' ?></button>
    </form>
  </div>
</body>
</html>
