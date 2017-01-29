<?php

error_reporting(E_ALL);

define('BASE_DIR', dirname(__DIR__));
#define('APP_DIR', BASE_DIR . '/app');

include BASE_DIR . '/public/trace.php';
include BASE_DIR . "/app/bootstrap.php";

$bootstrap = new Bootstrap();
echo $bootstrap->run();
