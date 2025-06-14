<?php
// public/register_form.php
require_once __DIR__ . '/../csrf.php';
session_start();
// Generează token CSRF; va fi inclus în form
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Înregistrare</title>
    <style>
      /* Simplu stil pentru afișare erori și layout */
      body { font-family: Arial, sans-serif; margin: 2rem; }
      form { max-width: 400px; margin: auto; }
      label { display: block; margin-bottom: 0.5rem; }
      input { width: 100%; padding: 0.5rem; margin-bottom: 1rem; }
      button { padding: 0.5rem 1rem; }
      .error { color: red; margin-bottom: 1rem; }
      .success { color: green; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <h1>Înregistrare cont</h1>
    <div id="message"></div>
    <form id="registerForm" action="register.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_get_token()) ?>">
        <label>
            Username:
            <input type="text" name="username" required>
        </label>
        <label>
            Email:
            <input type="email" name="email" required>
        </label>
        <label>
            Parolă:
            <input type="password" name="password" required minlength="6">
        </label>
        <label>
            Confirmă parola:
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
        // Coletează datele din formular
        const formData = new FormData(form);
        try {
            const resp = await fetch(form.action, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            const data = await resp.json();
            if (!resp.ok) {
                // Afișează erori
                if (data.errors && Array.isArray(data.errors)) {
                    data.errors.forEach(err => {
                        const p = document.createElement('p');
                        p.textContent = err;
                        p.className = 'error';
                        messageDiv.appendChild(p);
                    });
                } else if (data.error) {
                    const p = document.createElement('p');
                    p.textContent = data.error;
                    p.className = 'error';
                    messageDiv.appendChild(p);
                } else {
                    const p = document.createElement('p');
                    p.textContent = 'Eroare neașteptată.';
                    p.className = 'error';
                    messageDiv.appendChild(p);
                }
            } else {
                // Succes
                const p = document.createElement('p');
                p.textContent = 'Înregistrare reușită!';
                p.className = 'success';
                messageDiv.appendChild(p);
                // Redirecționează după câteva secunde sau imediat:
                setTimeout(() => {
                    window.location.href = 'login_form.php'; // sau altă pagină
                }, 1500);
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
