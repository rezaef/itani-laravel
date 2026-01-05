<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../itaniapp/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../itaniapp/vendor/autoload.php';
$app = require_once __DIR__.'/../itaniapp/bootstrap/app.php';


$app->handleRequest(Request::capture());
