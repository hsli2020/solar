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
            $sql = "SELECT * FROM projects WHERE active=1";
            $projects = $this->db->fetchAll($sql);

            foreach ($projects as $project) {
                $id = $project['id'];
                $object = new Project($project);

                $sql = "SELECT * FROM devices WHERE project_id='$id'";
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

    public function getDetails($id)
    {
        $details = [];

        $project = $this->get($id);

        $details['project_name'] = $project->name;
        $details['address'] = $project->name;
        $details['ac_size'] = $project->capacityAC;
        $details['dc_size'] = $project->capacityDC;
        $details['num_of_inverters'] = count($project->inverters);

        $details['yesterday']['prod'] = 'TODO';
        $details['yesterday']['inso'] = 'TODO';
        $details['month-to-date']['prod'] = 'TODO';
        $details['month-to-date']['inso'] = 'TODO';
        $details['today']['prod'] = 'TODO';
        $details['today']['inso'] = 'TODO';

        return $details;
    }
}
