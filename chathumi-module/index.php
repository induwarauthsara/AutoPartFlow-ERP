<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/core/Auth.php';

$auth = new Auth();
header('Location: ' . ($auth->isLoggedIn() ? '/views/dashboard.php' : '/login.php'));
exit;
