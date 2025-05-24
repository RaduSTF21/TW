<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config.php';
$pdo = getPDO();

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : null;
$editing = false;
$title = $description = '';
$price = $latitude = $longitude = '';
$existingImages = [];

if ($id) {
    // Fetch existing property
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $prop = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($prop) {
        $editing = true;
        $title = $prop['title'];
        $description = $prop['description'];
        $price = $prop['price'];
        $latitude = $prop['latitude'];
        $longitude = $prop['longitude'];
        // Fetch existing images
        $imgStmt = $pdo->prepare("SELECT id, filename FROM property_images WHERE property_id = :pid");
        $imgStmt->execute([':pid' => $id]);
        $existingImages = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize & validate inputs
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);

    if ($editing) {
        $sql = "UPDATE properties
                SET title = :title,
                    description = :description,
                    price = :price,
                    latitude = :lat,
                    longitude = :lng
                WHERE id = :id";
        $params = [
            ':title' => $title,
            ':description' => $description,
            ':price' => $price,
            ':lat' => $latitude,
            ':lng' => $longitude,
            ':id' => $id
        ];
    } else {
        $sql = "INSERT INTO properties
                (title, description, price, latitude, longitude)
                VALUES (:title, :description, :price, :lat, :lng)";
        $params = [
            ':title' => $title,
            ':description' => $description,
            ':price' => $price,
            ':lat' => $latitude,
            ':lng' => $longitude
        ];
    }
    // Execute property insert/update
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Determine the property ID
    $propId = $editing ? $id : $pdo->lastInsertId();

    // Handle file uploads
    if (!empty($_FILES['images']) && $_FILES['images']['error'][0] !== UPLOAD_ERR_NO_FILE) {
        $uploadDir = __DIR__ . '/uploads/';
        foreach ($_FILES['images']['tmp_name'] as $index => $tmpPath) {
            $error = $_FILES['images']['error'][$index];
            if ($error === UPLOAD_ERR_OK) {
                $origName = basename($_FILES['images']['name'][$index]);
                $ext = pathinfo($origName, PATHINFO_EXTENSION);
                $newName = uniqid('img_') . '.' . $ext;
                $dest = $uploadDir . $newName;
                if (move_uploaded_file($tmpPath, $dest)) {
                    $ins = $pdo->prepare(
                        "INSERT INTO property_images (property_id, filename, alt_text)
                         VALUES (:pid, :fn, :alt)"
                    );
                    $ins->execute([
                        ':pid' => $propId,
                        ':fn'  => $newName,
                        ':alt' => ''
                    ]);
                }
            }
        }
    }

    header('Location: properties.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $editing ? 'Edit' : 'Add New' ?> Property</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 1rem; }
    img { max-height: 80px; border-radius: 4px; }
    .existing-images { margin-bottom: 1rem; }
    .existing-images div { display: inline-block; margin: 0.5rem; text-align: center; }
  </style>
</head>
<body>
  <h1><?= $editing ? 'Edit Property' : 'Add New Property' ?></h1>
  <form method="post" enctype="multipart/form-data">
    <label>Title:<br>
      <input name="title" type="text" value="<?= htmlspecialchars($title) ?>" required>
    </label><br><br>
    <label>Description:<br>
      <textarea name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>
    </label><br><br>
    <label>Price:<br>
      <input name="price" type="number" step="0.01" value="<?= htmlspecialchars($price) ?>" required>
    </label><br><br>
    <label>Latitude:<br>
      <input name="latitude" type="text" value="<?= htmlspecialchars($latitude) ?>">
    </label><br><br>
    <label>Longitude:<br>
      <input name="longitude" type="text" value="<?= htmlspecialchars($longitude) ?>">
    </label><br><br>

    <?php if ($editing && !empty($existingImages)): ?>
      <div class="existing-images">
        <p><strong>Existing Images:</strong></p>
        <?php foreach ($existingImages as $img): ?>
          <div>
            <img src="uploads/<?= htmlspecialchars($img['filename']) ?>" alt="">
            <br>
            <a href="delete_image.php?id=<?= $img['id'] ?>&prop=<?= $id ?>"
               onclick="return confirm('Delete this image?');">Delete</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <label>Upload Images:<br>
      <input type="file" name="images[]" accept="image/*" multiple>
    </label><br><br>
    <button type="submit"><?= $editing ? 'Save Changes' : 'Create Property' ?></button>
    <a href="properties.php">Cancel</a>
  </form>
</body>
</html>
