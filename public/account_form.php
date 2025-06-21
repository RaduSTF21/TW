<?php
?>
<h1>Account Management</h1>

<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">
        Your account has been updated.
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="account.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <div class="form-group">
        <label for="name">Name</label>
        <input id="name" name="name" value="<?= htmlspecialchars($form_name) ?>" required>
    </div>
    <div class="form-group">
        <label for="password">New Password (leave blank to keep current)</label>
        <input id="password" name="password" type="password" minlength="6">
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirm New Password</label>
        <input id="confirm_password" name="confirm_password" type="password" minlength="6">
    </div>
    <button type="submit">Save Changes</button>
</form>
<br>
<a href="../imob.php" class="btn">Back to Imob Page</a>