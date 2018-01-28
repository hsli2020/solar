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
        if (isset($this->projects[$id])) {
            return $this->projects[$id];
        }
        throw new \Exception("Invalid Parameter: $id");
    }

    public function getDetails($id)
    {
        $details = [];

        $project = $this->get($id);

        $details['project_name'] = $project->name;
        $details['address'] = $project->name;
        $details['ac_size'] = round($project->capacityAC);
        $details['dc_size'] = round($project->capacityDC);
        $details['num_of_inverters'] = max(1, count($project->inverters));
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

            $details['inverter_type'] = $inverter->getInverterType();

            $power = $getVal($data, ['kw', 'line_kw']);

            $details['inverters'][$code]['type']   = $inverter->getInverterType();
            $details['inverters'][$code]['power']  = $power;
            $details['inverters'][$code]['status'] = $power > 0 ? 'On' : 'Off';
            $details['inverters'][$code]['fault']  = 'None';
            $details['inverters'][$code]['vla']    = $getVal($data, ['vln_a', 'volt_a', 'volts_a']);
            $details['inverters'][$code]['vlb']    = $getVal($data, ['vln_b', 'volt_b', 'volts_b']);
            $details['inverters'][$code]['vlc']    = $getVal($data, ['vln_c', 'volt_c', 'volts_c']);

            $details['inverters'][$code]['combiner'] = '';
            if ($combiner = $inverter->getCombiner()) {
                $details['inverters'][$code]['combiner'] = $project->id.'_'.$combiner;
            }
        }

        // Envkit
        foreach ($project->envkits as $envkit) {
            $code = $envkit->code;
            $data = $envkit->getLatestData();

            $details['envkits'][$code]['inso'] = round($data['IRR']);
            $details['envkits'][$code]['oat'] = round($data['OAT']);
            $details['envkits'][$code]['panelt'] = round($data['PANELT']);
        }

        // GenMeter
        foreach ($project->genmeters as $genmeter) {
            $code = $genmeter->code;
            $data = $genmeter->getLatestData();

            $details['genmeters'][$code]['kw-del'] = round($data['kwh_del']);
            $details['genmeters'][$code]['kw-rec'] = round($data['kwh_rec']);
            $details['genmeters'][$code]['kvar'] = round($data['kva']);
            $details['genmeters'][$code]['vla'] = round($data['vln_a']);
            $details['genmeters'][$code]['vlb'] = round($data['vln_b']);
            $details['genmeters'][$code]['vlc'] = round($data['vln_c']);
        }

        // if there is no inverter
        if (count($project->inverters) == 0) {
            $genmeter = current($details['genmeters']);
            $details['inverters']['fake']['type']   = '';
            $details['inverters']['fake']['power']  = $genmeter['kvar'];
            $details['inverters']['fake']['status'] = 'On';
            $details['inverters']['fake']['fault']  = 'None';
            $details['inverters']['fake']['vla']    = $genmeter['vla'];
            $details['inverters']['fake']['vlb']    = $genmeter['vlb'];
            $details['inverters']['fake']['vlc']    = $genmeter['vlc'];
        }

        return $details;
    }

    public function loadCombiner($prj, $dev)
    {
        $project = $this->get($prj);
        $combiner = $project->combiners[$dev];
        $data = $combiner->load();
        return $data;
    }
}
