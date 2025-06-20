<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="/assets/css/imob.css">
  <style>
    body { display: flex; justify-content: center; align-items: center; height: 100vh; }
    .login-container {
      background: #fff;
      padding: 2rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .login-container h1 { text-align: center; margin-bottom: 1rem; }
    .login-container label { display: block; margin-bottom: 0.5rem; }
    .login-container input { width: 100%; padding: 0.5rem; margin-bottom: 1rem; }
    .login-container button { width: 100%; padding: 0.5rem; }
    .error { color: red; margin-bottom: 1rem; }
    .success { color: green; margin-bottom: 1rem; }
  </style>
</head>
<body>
  <div class="login-container">
    <h1>Autentificare</h1>
    <div id="message"></div>
    <form id="loginForm" action="login.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
      <label>
        Username:
        <input type="text" name="username" required>
      </label>
      <label>
        Parolă:
        <input type="password" name="password" required>
      </label>
      <button type="submit">Login</button>
    </form>
  </div>

  <script>
  document.getElementById('loginForm').addEventListener('submit', async function(e) {
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
        const p = document.createElement('p');
        p.textContent = data.error || 'Eroare neașteptată.';
        p.className = 'error';
        messageDiv.appendChild(p);
      } else {
        const p = document.createElement('p');
        p.textContent = 'Login reușit! Redirecționare...';
        p.className = 'success';
        messageDiv.appendChild(p);
        setTimeout(() => {
          window.location.href = 'imob.php';
        }, 1000);
      }
    } catch (err) {
      const p = document.createElement('p');
      p.textContent = 'Eroare de rețea: ' + err.message;
      p.className = 'error';
      messageDiv.appendChild(p);
    }
  });
  </script>
</body>
</html>
