<?php

error_reporting(0);
ini_set('display_errors', 'Off');

define('TOOLKIT', __DIR__);

define('WP_DIR', dirname(__DIR__));

require(TOOLKIT.'/src/Loader.php');

header('Content-Type: application/json');

$router = new Router($_POST);

echo $router->response();