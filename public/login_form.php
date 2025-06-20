<?php
// public/login_form.php
require_once __DIR__ . '/../bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="/assets/css/imob.css">
  <style>
    .error { color: red; }
    .success { color: green; }
    .login-container { max-width: 400px; margin: auto; }
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
    const messageDiv = document.getElementById('message'); messageDiv.innerHTML = '';
    const formData = new FormData(e.target);
    try {
      const resp = await fetch(e.target.action, { method: 'POST', body: formData, credentials: 'same-origin' });
      const data = await resp.json();
      if (!resp.ok) {
        const errMsg = data.error || 'Eroare neașteptată';
        const p = document.createElement('p'); p.textContent = errMsg; p.className='error'; messageDiv.appendChild(p);
      } else {
        const p = document.createElement('p'); p.textContent = 'Login reușit!'; p.className='success'; messageDiv.appendChild(p);
        setTimeout(() => window.location.href = 'imob.php', 1000);
      }
    } catch(err) {
      const p = document.createElement('p'); p.textContent = 'Eroare rețea: ' + err.message; p.className='error'; messageDiv.appendChild(p);
    }
  });
  </script>
</body>
</html>