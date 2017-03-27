<?php

namespace App\Service;

use Phalcon\Di\Injectable;

use App\Models\Snapshot;

class SnapshotService extends Injectable
{
    public function load()
    {
        $result = $this->db->fetchAll("SELECT * FROM snapshot");

        $totalPower = 0;
        $totalProjectSizeAC = 0;

        foreach ($result as $key => $val) {
            $result[$key]['error'] = [];

            if ($val['GCPR'] < 60) {
                $result[$key]['error']['GCPR'] = 1;
            }

           #$result[$key]['error']['current_power'] = 1;
           #$result[$key]['error']['irradiance'] = 1;

            list($a, $b) = explode('/', $val['inverters_generating']);
            if ($a != $b) {
                $result[$key]['error']['inverters_generating'] = 1;
            }

            list($a, $b) = explode('/', $val['devices_communicating']);
            if ($a != $b) {
                $result[$key]['error']['devices_communicating'] = 1;
            }

            $totalPower += $val['current_power'];
            $totalProjectSizeAC += $val['project_size_ac'];

           #$result[$key]['error']['last_com'] = 1;
           #$result[$key]['error']['Avg_Irradiance_POA'] = 1;
           #$result[$key]['error']['Avg_Module_Temp'] = 1;
           #$result[$key]['error']['Measured_Energy'] = 1;
        }

        $total['current_power']   = number_format($totalPower);
        $total['project_size_ac'] = number_format($totalProjectSizeAC);
        $total['performance'] = number_format($totalPower / $totalProjectSizeAC * 100);

        return [ 'rows' => $result, 'total' => $total ];
    }

    public function generate()
    {
        $projects = $this->projectService->getAll();

        foreach ($projects as $project) {
            $id = $project->id;
            $name = $project->name;
            $sizeAC = round($project->capacityAC);

            $GCPR = $this->getGCPR($project);
            $currentPower = $this->getCurrentPower($project);
            $irradiance = $this->getIrradiance($project);
            $invertersGenerating = $this->getInvertersGenerating($project);
            $devicesCommunicating = $this->getDevicesCommunicating($project);
            $lastCom = $this->getLastCom($project);

            $sql = "REPLACE INTO snapshot SET"
                 . " project_id = $id,"
                 . " project_name = '$name',"
                 . " project_size_ac = '$sizeAC',"
                 . " GCPR = '$GCPR',"
                 . " current_power = '$currentPower',"
                 . " irradiance = '$irradiance',"
                 . " inverters_generating = '$invertersGenerating',"
                 . " devices_communicating = '$devicesCommunicating',"
                 . " last_com = '$lastCom'";

            $this->db->execute($sql);
        }
    }

    protected function getGCPR($project)
    {
        $pr = $project->getPR();
        return round($pr * 100).'%';
    }

    protected function getCurrentPower($project)
    {
        $kw = $project->getLatestKW();
        return round($kw);
    }

    protected function getIrradiance($project)
    {
        $irr = $project->getLatestIRR();
        return round($irr);
    }

    protected function getInvertersGenerating($project)
    {
        $total = count($project->inverters);

        // TODO: $this->dataService->getWorkingInverters($project->id);
        $working = $total;

        return "$working/$total";
    }

    protected function getDevicesCommunicating($project)
    {
        $total = $project->getDeviceCount();

        // TODO: $this->dataService->getWorkingDevices($project->id);
        $working = $total;

        return "$working/$total";
    }

    protected function getLastCom($project)
    {
        return $project->getLatestTime();
    }
}
