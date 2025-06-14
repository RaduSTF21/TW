<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../csrf.php';
csrf_validate();
session_start();
session_destroy();
header('Location: login.php');
exit;
