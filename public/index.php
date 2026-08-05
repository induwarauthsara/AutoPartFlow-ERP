<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

require APP_PATH . '/bootstrap.php';

$appConfig = require CONFIG_PATH . '/app.php';
$dbConfig  = require CONFIG_PATH . '/database.php';

define('BASE_URL', $appConfig['base_url']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($appConfig['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

use App\Core\App;
use App\Core\Database;

Database::connect($dbConfig);

$app = new App();
$app->run();
