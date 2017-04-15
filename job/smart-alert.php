<?php

include __DIR__ . '/../public/init.php';

$di = \Phalcon\Di::getDefault();

$smartAlert = $di->get('smartAlertService');
$smartAlert->run();
