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

        $getVal = function($data, $fields) {
            foreach ($fields as $name) {
                if (isset($data[$name])) {
                   return round($data[$name]);
                }
            }
            return '';
        };

        $data = $project->getFirstInverter()->getLatestData();

        $details['inverter']['power'] = $getVal($data, ['kw', 'line_kw']);
        $details['inverter']['status'] = 'On';
        $details['inverter']['fault'] = 'None';
        $details['inverter']['vla'] = $getVal($data, ['vln_a', 'volt_a', 'volts_a']);
        $details['inverter']['vlb'] = $getVal($data, ['vln_b', 'volt_b', 'volts_b']);
        $details['inverter']['vlc'] = $getVal($data, ['vln_c', 'volt_c', 'volts_c']);
        $details['inverter']['vln'] = '';

        $data = $project->getFirstEnvKit()->getLatestData();

        $details['envkit']['inso'] = round($data['IRR']);
        $details['envkit']['oat'] = round($data['OAT']);
        $details['envkit']['panelt'] = round($data['PANELT']);

        $data = $project->getFirstGenMeter()->getLatestData();

        $details['genmeter']['kw-del'] = round($data['kwh_del']);
        $details['genmeter']['kw-rec'] = round($data['kwh_rec']);
        $details['genmeter']['kvar'] = round($data['kva']);
        $details['genmeter']['vla'] = round($data['vln_a']);
        $details['genmeter']['vlb'] = round($data['vln_b']);
        $details['genmeter']['vlc'] = round($data['vln_c']);
        $details['genmeter']['vln'] = '';

        return $details;
    }
}
