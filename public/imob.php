<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../csrf.php';
require_once __DIR__ . '/../bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Build the nav HTML
$user = $_SESSION['user_id'] ?? null;
if ($user) {
    $nav = implode(' ', [
        '<a href="logout.php">Logout</a>',
        '<a href="imob.php">Acasă</a>',
        '<a href="templates/adauga_anunt.html">Adaugă Anunț</a>',
        '<a href="anunturi.php">Anunțuri</a>',
        '<a href="account.php">Profil</a>',
    ]);
} else {
    $nav = implode(' ', [
        '<a href="templates/register.html">Înregistrare</a>',
        '<a href="templates/login.html">Autentificare</a>',
        '<a href="imob.php">Acasă</a>',
        '<a href="anunturi.php">Anunțuri</a>',
    ]);
}

// Load template and inject nav
$template = file_get_contents(__DIR__ . '/templates/imob.html');
echo str_replace('<!--NAVIGATION-->', $nav, $template);