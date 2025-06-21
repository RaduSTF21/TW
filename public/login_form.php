<?php
// public/login_form.php
require_once __DIR__ . '/../bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();
// Generează token CSRF dacă nu există
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="/TW/assets/css/imob.css">
  <style>
    .error { color: red; }
    .success { color: green; }
    .login-container { max-width: 400px; margin: auto; padding: 1rem; }
    label { display: block; margin-bottom: 0.5rem; }
    input { width: 100%; padding: 0.5rem; margin-bottom: 1rem; }
    button { padding: 0.5rem 1rem; }
    #message { margin-bottom: 1rem; }
  </style>
</head>
<body>
  <div class="login-container">
    <h1>Login</h1>
    <div id="message"></div>
    <form id="loginForm" action="login.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <label>Nume utilizator:
        <input type="text" name="username" required>
      </label>
      <label>Parolă:
        <input type="password" name="password" required>
      </label>
      <button type="submit">Login</button>
    </form>
  </div>
  <script>
  document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const messageDiv = document.getElementById('message');
    messageDiv.innerHTML = '';
    const actionUrl = e.target.action;
    const formData = new FormData(e.target);
    try {
      const resp = await fetch(actionUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });
      const text = await resp.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch(err) {
        console.error('Invalid JSON response:', text);
        messageDiv.innerHTML = '<p class="error">Răspuns server invalid.</p>';
        return;
      }
      if (!resp.ok) {
        const errMsg = data.error || 'Eroare neașteptată';
        messageDiv.innerHTML = `<p class="error">${errMsg}</p>`;
      } else {
        messageDiv.innerHTML = '<p class="success">Login reușit!</p>';
        setTimeout(() => {
          window.location.href = 'imob.php';
        }, 1000);
      }
    } catch(err) {
      console.error('Network error:', err);
      messageDiv.innerHTML = `<p class="error">Eroare rețea: ${err.message}</p>`;
    }
  });
  </script>
</body>
</html>
