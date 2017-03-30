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
        $averageIrradiance = 0;

        foreach ($result as $key => $val) {
            $result[$key]['error'] = [];

            if ($val['GCPR'] >= 90) {
                $result[$key]['error']['GCPR'] = '';
            }
            else if ($val['GCPR'] >= 80) {
                $result[$key]['error']['GCPR'] = 'text-blue';
            }
            else if ($val['GCPR'] >= 65) {
                $result[$key]['error']['GCPR'] = 'text-purple';
            }
            else { // ($val['GCPR'] < 65)
                $result[$key]['error']['GCPR'] = 'red';
            }

           #$result[$key]['error']['current_power'] = 1;
           #$result[$key]['error']['irradiance'] = 1;

            if ($val['current_power'] < 2 && $val['irradiance'] >= 100) {
                $result[$key]['error']['inverters_generating'] = 'red';
            } else if ($val['current_power'] < 4) {
                list($a, $b) = explode('/', $val['inverters_generating']);
                $result[$key]['inverters_generating'] = "0/$b";
            }

            list($a, $b) = explode('/', $val['devices_communicating']);
            if ($a != $b) {
                $result[$key]['error']['devices_communicating'] = 'red';
            }

            $totalPower += $val['current_power'];
            $totalProjectSizeAC += $val['project_size_ac'];
            $averageIrradiance += $val['irradiance'];

           #$result[$key]['error']['last_com'] = 1;
           #$result[$key]['error']['Avg_Irradiance_POA'] = 1;
           #$result[$key]['error']['Avg_Module_Temp'] = 1;
           #$result[$key]['error']['Measured_Energy'] = 1;
        }

        $total['current_power'] = number_format($totalPower);
        $total['project_size_ac'] = number_format($totalProjectSizeAC);
        $total['average_irradiance'] = number_format($averageIrradiance/count($result));
        $total['performance'] = number_format($totalPower / $totalProjectSizeAC * 100);

        return [ 'rows' => $result, 'total' => $total ];
    }

    public function generate()
    {
        $projects = $this->projectService->getAll();

        foreach ($projects as $project) {
            $id = $project['id'];
            $name = $project['name'];
            $sizeAC = round($project['AC_Nameplate_Capacity']);

            $GCPR = $this->getGCPR($id);
            $currentPower = $this->getCurrentPower($id);
            $irradiance = $this->getIrradiance($id);
            $invertersGenerating = $this->getInvertersGenerating($id);
            $devicesCommunicating = $this->getDevicesCommunicating($id);
            $lastCom = $this->getLastCom($id);

#           $avgIrradiancePOA = $this->getAvgIrradiancePOA($id);
#           $avgModuleTemp = $this->getAvgModuleTemp($id);
#           $measuredEnergy = $this->getMeasuredEnergy($id);

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
#                . " Avg_Irradiance_POA = $avgIrradiancePOA,"
#                . " Avg_Module_Temp = $avgModuleTemp,"
#                . " Measured_Energy = $measuredEnergy";

            $this->db->execute($sql);
        }
    }

    protected function getGCPR($prj)
    {
        $pr = $this->dataService->getPR($prj);
        return round($pr * 100).'%';
    }

    protected function getCurrentPower($prj)
    {
        $kw = $this->dataService->getLatestKW($prj);
        return round($kw);
    }

    protected function getIrradiance($prj)
    {
        $irr = $this->dataService->getLatestIRR($prj);
        return round($irr);
    }

    protected function getInvertersGenerating($prj)
    {
        $inverters = $this->deviceService->getInverters($prj);
        $total = count($inverters);

        // TODO: $this->dataService->getWorkingInverters($prj);
        $working = $total;

        return "$working/$total";
    }

    protected function getDevicesCommunicating($prj)
    {
        $devices = $this->deviceService->getDevices($prj);
        $total = count($devices);

        // TODO: $this->dataService->getWorkingDevices($prj);
        $working = $total;

        return "$working/$total";
    }

    protected function getLastCom($prj)
    {
        return $this->dataService->getLatestTime($prj);
    }

#   protected function getAvgIrradiancePOA($prj) { return '0.0'; }
#   protected function getAvgModuleTemp($prj)    { return '0.0'; }
#   protected function getMeasuredEnergy($prj)   { return '0.0'; }
}
