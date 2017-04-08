<?php

include __DIR__ . '/../public/init.php';

$di = \Phalcon\Di::getDefault();

$dailyReport = $di->get('dailyReportService');
$dailyReport->send();
