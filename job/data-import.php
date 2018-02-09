<?php

include __DIR__ . '/../public/init.php';

$di = \Phalcon\Di::getDefault();

$service = $di->get('importService');
$service->import();
$service->importWhitby();

$service = $di->get('dataService');
$service->fakeInverterData();
