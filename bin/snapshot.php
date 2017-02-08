<?php

include __DIR__ . '/../public/init.php';

$di = \Phalcon\Di::getDefault();

$snapshot = $di->get('snapshotService');
$snapshot->generate();
