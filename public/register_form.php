<?php
// public/register_form.php
require_once __DIR__ . '/../bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Înregistrare</title>
  <link rel="stylesheet" href="admin/assets/css/imob.css">
  <style>
    .error { color: red; }
    .success { color: green; }
    form { max-width: 400px; margin: auto; }
    label { display: block; margin-bottom: 0.5rem; }
    input { width: 100%; padding: 0.5rem; margin-bottom: 1rem; }
    button { padding: 0.5rem 1rem; }
    #message { margin-bottom: 1rem; }
  </style>
</head>
<body>
  <h1>Înregistrare</h1>
  <div id="message"></div>
  <form id="registerForm" action="register.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <label>Nume utilizator:
      <input type="text" name="username" required>
    </label>
    <label>Email:
      <input type="email" name="email" required>
    </label>
    <label>Parolă:
      <input type="password" name="password" required minlength="6">
    </label>
    <label>Confirmă parola:
      <input type="password" name="confirm_password" required minlength="6">
    </label>
    <button type="submit">Înregistrează-te</button>
  </form>

  <script>
  document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const messageDiv = document.getElementById('message');
    messageDiv.innerHTML = '';
    const formData = new FormData(form);
    try {
      const resp = await fetch(form.action, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });
      const data = await resp.json();
      if (!resp.ok) {
        if (data.errors) {
          data.errors.forEach(err => {
            const p = document.createElement('p'); p.textContent = err; p.className = 'error'; messageDiv.appendChild(p);
          });
        } else if (data.error) {
          const p = document.createElement('p'); p.textContent = data.error; p.className = 'error'; messageDiv.appendChild(p);
        } else {
          const p = document.createElement('p'); p.textContent = 'Eroare neașteptată'; p.className = 'error'; messageDiv.appendChild(p);
        }
      } else {
        const p = document.createElement('p'); p.textContent = 'Înregistrare reușită!'; p.className = 'success'; messageDiv.appendChild(p);
        setTimeout(() => window.location.href = 'login_form.php', 1500);
      }
    } catch (err) {
      const p = document.createElement('p'); p.textContent = 'Eroare rețea: ' + err.message; p.className = 'error'; messageDiv.appendChild(p);
    }
  });
  </script>
</body>
</html>