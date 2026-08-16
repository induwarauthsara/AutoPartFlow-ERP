<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/core/Auth.php';

$auth = new Auth();
$auth->logout();
header('Location: /login.php');
exit;
