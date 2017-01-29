<?php

namespace App\Service;

use Phalcon\Di\Injectable;

use App\Models\Projects;

class ProjectService extends Injectable
{
    protected $projects = [];

    public function getAll()
    {
        if (!$this->projects) {
            $result = Projects::find('active=1');
            foreach ($result as $project) {
                $id = $project->id;
                $this->projects[$id] = $project->toArray();
            }
        }

        return $this->projects;
    }
}
