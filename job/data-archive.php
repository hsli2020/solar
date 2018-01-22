<?php

include __DIR__ . '/../public/init.php';

$di = \Phalcon\Di::getDefault();

$service = $di->get('dataService');
$service->archive();

$service = $di->get('solarService');
$service->cleanup();
