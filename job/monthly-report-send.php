<?php

include __DIR__ . '/../public/init.php';

$di = \Phalcon\Di::getDefault();

$monthlyReport = $di->get('monthlyReportService');
$monthlyReport->send();
