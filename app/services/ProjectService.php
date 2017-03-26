<?php

namespace App\Service;

use Phalcon\Di\Injectable;

use App\System\Project;

class ProjectService extends Injectable
{
    protected $projects = [];

    public function getAll(/* $includeInactive = false */)
    {
        if (!$this->projects) {
            $sql = "SELECT * FROM solar_project WHERE active=1";
            $projects = $this->db->fetchAll($sql);

            foreach ($projects as $project) {
                $id = $project['id'];
                $object = new Project($project);

                $sql = "SELECT * FROM solar_device WHERE project_id='$id'";
                $devices = $this->db->fetchAll($sql);

                foreach ($devices as $device) {
                    $object->initDevices($device);
                }

                $this->projects[$id] = $object;
            }
        }

        return $this->projects;
    }

    public function get($id)
    {
        return $this->projects[$id];
    }

    public function getName($id)
    {
        $project = $this->get($id);
        if ($project) {
            return $project['name'];
        }
        return false;
    }

    public function getFtpDir($id)
    {
        $project = $this->get($id);
        if ($project) {
            return $project['ftpdir'];
        }
        return false;
    }

    public function getDcSize($id)
    {
        $project = $this->get($id);
        if ($project) {
            return $project['DC_Nameplate_Capacity'];
        }
        return false;
    }

    public function getAcSize($id)
    {
        $project = $this->get($id);
        if ($project) {
            return $project['AC_Nameplate_Capacity'];
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

    public function getDetails($id)
    {
        $details = ProjectDetails::findFirst($id);
        return $details;
    }
}
