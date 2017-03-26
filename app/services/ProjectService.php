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
        if (!$this->projects) {
            $this->getAll();
        }
        return $this->projects[$id];
    }
}
