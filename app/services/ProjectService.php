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

        $report = $this->dailyReportService->load(date('Y-m-d', strtotime('-1 day')));

        $details['yesterday']['prod'] = $report[$id]['Measured_Production'];
        $details['yesterday']['inso'] = $report[$id]['Measured_Insolation'];
        $details['month-to-date']['prod'] = $report[$id]['Total_Energy'];
        $details['month-to-date']['inso'] = $report[$id]['Total_Insolation'];
        $details['today']['prod'] = 'TODO';
        $details['today']['inso'] = 'TODO';

        $details['inverter']['power'] = 'TODO';
        $details['inverter']['status'] = 'On';
        $details['inverter']['fault'] = 'None';
        $details['inverter']['vla'] = 'TODO';
        $details['inverter']['vlb'] = 'TODO';
        $details['inverter']['vlc'] = 'TODO';
        $details['inverter']['vln'] = 'TODO';

        $details['envkit']['inso'] = 'TODO';
        $details['envkit']['oat'] = 'TODO';
        $details['envkit']['panelt'] = 'TODO';

        $details['genmeter']['kw-del'] = 'TODO';
        $details['genmeter']['kw-rec'] = 'TODO';
        $details['genmeter']['kvar'] = 'TODO';
        $details['genmeter']['vla'] = 'TODO';
        $details['genmeter']['vlb'] = 'TODO';
        $details['genmeter']['vlc'] = 'TODO';
        $details['genmeter']['vln'] = 'TODO';

        return $details;
    }
}
