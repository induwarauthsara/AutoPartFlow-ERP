<?php

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri !== '/' && is_file(__DIR__ . $uri)) {
    return false;
}

$_GET['url'] = ltrim($uri, '/');

require __DIR__ . '/index.php';
