<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ExportService extends Injectable
{
    public function export($params)
    {
        $projectId = max(1, $params['project']); // set project=1 if not specified

        $project = $this->projectService->get($projectId);
        $filename = $project->export($params);

        return $filename;
    }
}
