<?php

namespace App\Service;

use Phalcon\Di\Injectable;

use App\Models\Projects;

class ProjectService extends Injectable
{
    protected $projects = [];

    public function getAll(/* $includeInactive = false */)
    {
        if (!$this->projects) {
            $result = Projects::find('active=1');
            foreach ($result as $project) {
                $id = $project->id;
                // TODO: convert to entity ($this->toEntity(project))
                $this->projects[$id] = $project->toArray();
            }
        }

        return $this->projects;
    }

    public function get($id)
    {
        $project = Projects::findFirst($id);
        // TODO: convert to entity ($this->toEntity(project))
        return $project;
    }

    public function getFtpDir($id)
    {
        $project = Projects::findFirst($id);
        if ($project) {
            return $project['ftpdir'];
        }
        return false;
    }

    public function activate($id)
    {
        $project = Projects::findFirst($id);
        if ($project) {
            $project->active = 1;
            $project->save();
        }
    }

    public function deactivate($id)
    {
        $project = Projects::findFirst($id);
        if ($project) {
            $project->active = 0;
            $project->save();
        }
    }

    public function add($info)
    {
        $project = new Projects();

        $project->name   = $info['name'];
        $project->ftpdir = $info['ftpdir'];
        $project->desc   = '';
        $project->active = isset($info['active']) ? $info['active'] : 1;

        $project->save();

        if (isset($info['devices'])) {
            $this->deviceService->add($project->id, $info['devices']);
        }
    }
}
