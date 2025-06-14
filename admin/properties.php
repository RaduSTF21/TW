<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/../bootstrap.php';
$stmt = $pdo->query('SELECT * FROM properties');

// Fetch all properties
$stmt = $pdo->query("SELECT id, title, price, created_at FROM properties ORDER BY created_at DESC");
$props = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><title>Admin – Properties</title>
  <style>
    table { width:100%; border-collapse: collapse; }
    th, td { padding:0.5rem; border:1px solid #ccc; text-align:left; }
    a.button { padding:0.3rem 0.6rem; background:#007bff; color:#fff; text-decoration:none; border-radius:4px; }
  </style>
</head>
<body>
  <h1>Manage Properties</h1>
  <p><a href="property_form.php" class="button">+ Add New Property</a>
     <a href="logout.php" style="float:right;">Log out</a></p>
  <table>
    <thead>
      <tr><th>ID</th><th>Title</th><th>Price</th><th>Created</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($props as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= htmlspecialchars($p['title']) ?></td>
          <td>€<?= htmlspecialchars($p['price']) ?></td>
          <td><?= $p['created_at'] ?></td>
          <td>
            <a href="property_form.php?id=<?= $p['id'] ?>">Edit</a> |
            <a href="delete_property.php?id=<?= $p['id'] ?>"
               onclick="return confirm('Delete this property?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
