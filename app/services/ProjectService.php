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

#       unset($this->projects[7]); // remove Norfolk, it affects everywhere

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
        $details['ac_size'] = round($project->capacityAC);
        $details['dc_size'] = round($project->capacityDC);
        $details['num_of_inverters'] = count($project->inverters);
        $details['num_of_genmeters'] = count($project->genmeters);
        $details['num_of_envkits'] = count($project->envkits);

        $report = $this->dailyReportService->load(date('Y-m-d', strtotime('-1 day')));

        $details['yesterday']['prod'] = round($report[$id]['Measured_Production']);
        $details['yesterday']['inso'] = round($report[$id]['Measured_Insolation'], 1);
        $details['month-to-date']['prod'] = round($report[$id]['Total_Energy']);
        $details['month-to-date']['inso'] = round($report[$id]['Total_Insolation']);
        $details['today']['prod'] = round($project->getKW('TODAY'));
        $details['today']['inso'] = round($project->getIRR('TODAY') / 1000.0, 1);

        $getVal = function($data, $fields) {
            foreach ($fields as $name) {
                if (isset($data[$name])) {
                   return round($data[$name]);
                }
            }
            return '';
        };

        // Inverters
        $details['inverters'] = [];
        $details['inverter_type'] = '';
        foreach ($project->inverters as $inverter) {
            $code = $inverter->code;
            $data = $inverter->getLatestData();

            $details['inverter_type'] = $inverter->getType();

            $details['inverters'][$code]['type']   = $inverter->getType();
            $details['inverters'][$code]['power']  = $getVal($data, ['kw', 'line_kw']);
            $details['inverters'][$code]['status'] = 'On';
            $details['inverters'][$code]['fault']  = 'None';
            $details['inverters'][$code]['vla']    = $getVal($data, ['vln_a', 'volt_a', 'volts_a']);
            $details['inverters'][$code]['vlb']    = $getVal($data, ['vln_b', 'volt_b', 'volts_b']);
            $details['inverters'][$code]['vlc']    = $getVal($data, ['vln_c', 'volt_c', 'volts_c']);
        }

        // Envkit
        $data = $project->getFirstEnvKit()->getLatestData();

        $details['envkit']['inso'] = round($data['IRR']);
        $details['envkit']['oat'] = round($data['OAT']);
        $details['envkit']['panelt'] = round($data['PANELT']);

        // GenMeter
        $data = $project->getFirstGenMeter()->getLatestData();

        $details['genmeter']['kw-del'] = round($data['kwh_del']);
        $details['genmeter']['kw-rec'] = round($data['kwh_rec']);
        $details['genmeter']['kvar'] = round($data['kva']);
        $details['genmeter']['vla'] = round($data['vln_a']);
        $details['genmeter']['vlb'] = round($data['vln_b']);
        $details['genmeter']['vlc'] = round($data['vln_c']);

        return $details;
    }
}
