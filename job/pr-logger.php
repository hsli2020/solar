<?php

include __DIR__ . '/../public/init.php';

$di = \Phalcon\Di::getDefault();

$dataService = $di->get('dataService');
$projectService = $di->get('projectService');

$fp = fopen(BASE_DIR . '/app/logs/PR.log', 'a+');

$projects = $projectService->getAll();
foreach ($projects as $project) {
    $id = $project['id'];
    $name = $project['name'];
    $PR = round($dataService->getPR($id)*100).'%';
    fputs($fp, sprintf("%d  %-25s %-24s %d%%\n", $id, $name, date('Y-m-d H:i:s'), $PR));
}
fputs($fp, "\n");

fclose($fp);
