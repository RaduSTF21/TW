<?php
// TW/admin/property_form.php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config.php';
$pdo = getPDO();

// Initialize variables
$editing = false;
$id = $_GET['id'] ?? null;
$title = $description = '';
$price = $rooms = $latitude = $longitude = '';
$transaction_type = $property_type = '';
$existingImages = [];

// If editing, fetch existing record + images
if ($id && ctype_digit($id)) {
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $prop = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($prop) {
        $editing = true;
        extract($prop); // populates $title, $description, $price, $rooms, etc.

        // fetch images
        $imgStmt = $pdo->prepare("
          SELECT id, filename FROM property_images
          WHERE property_id = :pid
        ");
        $imgStmt->execute([':pid' => $id]);
        $existingImages = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $title            = trim($_POST['title']);
    $description      = trim($_POST['description']);
    $price            = floatval($_POST['price']);
    $rooms            = ctype_digit($_POST['rooms'] ?? '') ? (int)$_POST['rooms'] : null;
    $transaction_type = $_POST['transaction_type'] ?? '';
    $property_type    = $_POST['property_type'] ?? '';
    $latitude         = floatval($_POST['latitude']);
    $longitude        = floatval($_POST['longitude']);

    // Build SQL & params
    if ($editing) {
        $sql = "UPDATE properties SET
                  title=:title,
                  description=:description,
                  price=:price,
                  rooms=:rooms,
                  transaction_type=:transaction_type,
                  property_type=:property_type,
                  latitude=:latitude,
                  longitude=:longitude
                WHERE id=:id";
        $params = [
            ':title'            => $title,
            ':description'      => $description,
            ':price'            => $price,
            ':rooms'            => $rooms,
            ':transaction_type' => $transaction_type,
            ':property_type'    => $property_type,
            ':latitude'         => $latitude,
            ':longitude'        => $longitude,
            ':id'               => $id
        ];
    } else {
        $sql = "INSERT INTO properties
                  (title, description, price, rooms, transaction_type, property_type, latitude, longitude)
                VALUES
                  (:title, :description, :price, :rooms, :transaction_type, :property_type, :latitude, :longitude)";
        $params = [
            ':title'            => $title,
            ':description'      => $description,
            ':price'            => $price,
            ':rooms'            => $rooms,
            ':transaction_type' => $transaction_type,
            ':property_type'    => $property_type,
            ':latitude'         => $latitude,
            ':longitude'        => $longitude
        ];
    }

    // Execute save
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Get the property ID for uploads
    $propId = $editing ? $id : $pdo->lastInsertId();

    // Handle image uploads
    if (!empty($_FILES['images']) && $_FILES['images']['error'][0] !== UPLOAD_ERR_NO_FILE) {
        $uploadDir = __DIR__ . '/uploads/';
        foreach ($_FILES['images']['tmp_name'] as $i => $tmpPath) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $origName = basename($_FILES['images']['name'][$i]);
                $ext      = pathinfo($origName, PATHINFO_EXTENSION);
                $newName  = uniqid('img_') . '.' . $ext;
                $dest     = $uploadDir . $newName;
                if (move_uploaded_file($tmpPath, $dest)) {
                    $ins = $pdo->prepare("
                      INSERT INTO property_images
                        (property_id, filename, alt_text)
                      VALUES
                        (:pid, :fn, :alt)
                    ");
                    $ins->execute([
                        ':pid' => $propId,
                        ':fn'  => $newName,
                        ':alt' => '' 
                    ]);
                }
            }
        }
    }

    // Redirect back to list
    header('Location: properties.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $editing ? 'Editează' : 'Adaugă' ?> Proprietate</title>
</head>
<body>
  <h1><?= $editing ? 'Editează' : 'Adaugă' ?> Proprietate</h1>
  <form method="post" enctype="multipart/form-data">
    <label>Title:<br>
      <input name="title" value="<?= htmlspecialchars($title) ?>" required>
    </label><br><br>

    <label>Description:<br>
      <textarea name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>
    </label><br><br>

    <label>Price (€):<br>
      <input name="price" type="number" step="0.01"
             value="<?= htmlspecialchars($price) ?>" required>
    </label><br><br>

    <label>Rooms:<br>
      <input name="rooms" type="number" min="1"
             value="<?= htmlspecialchars($rooms) ?>">
    </label><br><br>

    <label>Tip tranzacție:<br>
      <select name="transaction_type">
        <option value="">— Selectați —</option>
        <option value="inchiriere" <?= $transaction_type==='inchiriere'?'selected':'' ?>>
          Închiriere
        </option>
        <option value="vanzare" <?= $transaction_type==='vanzare'?'selected':'' ?>>
          Vânzare
        </option>
      </select>
    </label><br><br>

    <label>Tip proprietate:<br>
      <select name="property_type">
        <option value="">— Selectați —</option>
        <option value="apartament" <?= $property_type==='apartament'?'selected':'' ?>>
          Apartament
        </option>
        <option value="garsoniera" <?= $property_type==='garsoniera'?'selected':'' ?>>
          Garsonieră
        </option>
        <option value="casa" <?= $property_type==='casa'?'selected':'' ?>>
          Casă
        </option>
      </select>
    </label><br><br>

    <label>Latitude:<br>
      <input name="latitude" type="text"
             value="<?= htmlspecialchars($latitude) ?>">
    </label><br><br>

    <label>Longitude:<br>
      <input name="longitude" type="text"
             value="<?= htmlspecialchars($longitude) ?>">
    </label><br><br>

    <?php if ($editing && $existingImages): ?>
      <fieldset>
        <legend>Imagini existente</legend>
        <?php foreach ($existingImages as $img): ?>
          <div style="display:inline-block; margin:0 10px;">
            <img src="uploads/<?= htmlspecialchars($img['filename']) ?>"
                 style="height:80px; display:block;">
            <a href="delete_image.php?id=<?= $img['id'] ?>&prop=<?= $id ?>"
               onclick="return confirm('Șterge această imagine?')">
              Șterge
            </a>
          </div>
        <?php endforeach; ?>
      </fieldset>
      <br>
    <?php endif; ?>

    <label>Upload images:<br>
      <input type="file" name="images[]" accept="image/*" multiple>
    </label><br><br>

    <button type="submit"><?= $editing ? 'Salvează' : 'Creează' ?></button>
    <a href="properties.php">Anulează</a>
  </form>
</body>
</html>
