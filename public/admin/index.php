<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
header('Location: properties.php');
exit;
