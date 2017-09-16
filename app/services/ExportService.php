<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ExportService extends Injectable
{
    public function export($projectId, $interval, $start, $end)
    {
        $projectId = max(1, $projectId); // set project=1 if not specified
        $interval = max(1, $interval);   // set interval=1 if not specified

        $start = $start ? $start : date('Y-m-d');
        $end = $end ? $end : date('Y-m-d', strtotime('1 day'));

        $project = $this->projectService->get($projectId);
        $filename = $project->export($interval, $start, $end);

        return $filename;
    }
}
