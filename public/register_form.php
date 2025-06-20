<?php
// public/register_form.php
require __DIR__ . '/../bootstrap.php';
session_start();             // ← ADD THIS

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?><!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ImobiliareIasi.ro – Înregistrare</title>
  <link rel="stylesheet" href="../assets/css/imob.css"/>
</head>
<body>
  <!-- Header -->
  <header>
    <div class="logo">ImobiliareIasi.ro</div>
  </header>

  <!-- Navigation -->
  <nav>
    <a href="imob.php">Acasă</a>
    <a href="anunturi.php">Anunțuri</a>
    <a href="register_form.php" class="active">Înregistrare</a>
    <a href="login.php">Autentificare</a>
    <a href="#">Contact</a>
  </nav>

  <!-- Main Content -->
  <div class="main-content">
    <div class="search-box" style="max-width:400px; margin:auto;">
      <h2>Crează-ți cont</h2>

      <form id="register-form">
        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="filter-row" style="flex-direction:column;">
          <div class="filter-item">
            <label for="username">Username:</label>
            <input id="username" name="username" type="text" required>
          </div>
          <div class="filter-item">
            <label for="email">Email:</label>
            <input id="email" name="email" type="email" required>
          </div>
          <div class="filter-item">
            <label for="password">Parolă:</label>
            <input id="password" name="password" type="password" required>
          </div>
          <div class="filter-item">
            <label for="password_confirm">Confirmă parola:</label>
            <input id="password_confirm" name="confirm_password" type="password" required>
          </div>
        </div>

        <button type="submit">Înregistrează-te</button>
        <div id="error-msg" style="color:red; margin-top:10px;"></div>
      </form>
    </div>
  </div>

  <script>
  document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const data = {
      username: form.username.value.trim(),
      email: form.email.value.trim(),
      password: form.password.value,
      confirm_password: form.confirm_password.value,
      csrf_token: form.csrf_token.value
    };

    // Client‑side confirm check
    if (data.password !== data.confirm_password) {
      return document.getElementById('error-msg').innerText = 'Parolele nu se potrivesc.';
    }

    // Explicit relative path with leading ./
    const resp = await fetch('./register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    let json;
    try {
      json = await resp.json();
    } catch (err) {
      console.error('Invalid JSON response', await resp.text());
      return document.getElementById('error-msg').innerText = 'Răspuns server invalid.';
    }

    if (!resp.ok) {
      document.getElementById('error-msg').innerText = json.error;
    } else {
      window.location = 'login.php?registered=1';
    }
  });
  </script>
</body>
</html>
