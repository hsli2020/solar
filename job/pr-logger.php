<?php

include __DIR__ . '/../public/init.php';

$di = \Phalcon\Di::getDefault();
$db = $di->get('db');

$projectService = $di->get('projectService');

$projects = $projectService->getAll();
foreach ($projects as $project) {
    $id   = $project->id;
    $name = $project->name;
    $PR   = round($project->getPR()*100);

    $db->insertAsDict('gcpi', [
        'project_id'   => $id,
        'project_name' => $name,
        'start_time'   => date('Y-m-d H:00:00', strtotime('-1 hour')),
        'end_time'     => date('Y-m-d H:00:00'),
        'index'        => $PR,
    ]);
}
