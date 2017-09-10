<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ExportService extends Injectable
{
    public function export($projectId, $interval, $start, $end)
    {
        $project = $this->projectService->get($projectId);
        $filename = $project->export($interval, $start, $end);
        return $filename;
    }
}
