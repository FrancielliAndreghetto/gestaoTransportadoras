<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router;

header('Content-Type: application/json; charset=utf-8');

$router = new Router();
registerRoutes($router);
$router->dispatch();
