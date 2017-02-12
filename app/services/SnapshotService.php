<?php

namespace App\Service;

use Phalcon\Di\Injectable;

use App\Models\Snapshot;

class SnapshotService extends Injectable
{
    public function load()
    {
        $result = $this->db->fetchAll("SELECT * FROM snapshot");

        foreach ($result as $key => $val) {
            $result[$key]['error'] = [];

            if ($val['GCPR'] < 95) {
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

           #$result[$key]['error']['last_com'] = 1;
           #$result[$key]['error']['Avg_Irradiance_POA'] = 1;
           #$result[$key]['error']['Avg_Module_Temp'] = 1;
           #$result[$key]['error']['Measured_Energy'] = 1;
        }
        return $result;
    }

    public function generate()
    {
        $projects = $this->projectService->getAll();

        // TODO: to UTC
        $this->start = date('Y-m-d H:i:00', strtotime('-15 minutes'));

        foreach ($projects as $project) {
            $id = $project['id'];
            $name = $project['name'];

            $GCPR = $this->getGCPR($id);
            $currentPower = $this->getCurrentPower($id);
            $irradiance = $this->getIrradiance($id);
            $invertersGenerating = $this->getInvertersGenerating($id);
            $devicesCommunicating = $this->getDevicesCommunicating($id);
            $lastCom = $this->getLastCom($id);
            $AvgIrradiancePOA = $this->getAvgIrradiancePOA($id);
            $AvgModuleTemp = $this->getAvgModuleTemp($id);
            $MeasuredEnergy = $this->getMeasuredEnergy($id);

            $sql = "REPLACE INTO snapshot SET"
                 . " project_id = $id,"
                 . " project_name = '$name',"
                 . " GCPR = '$GCPR',"
                 . " current_power = '$currentPower',"
                 . " irradiance = '$irradiance',"
                 . " inverters_generating = '$invertersGenerating',"
                 . " devices_communicating = '$devicesCommunicating',"
                 . " last_com = '$lastCom',"
                 . " Avg_Irradiance_POA = $AvgIrradiancePOA,"
                 . " Avg_Module_Temp = $AvgModuleTemp,"
                 . " Measured_Energy = $MeasuredEnergy";

            $this->db->execute($sql);
        }
    }

    protected function getGCPR($prj)
    {
        return rand(90, 100).'%';
    }

    protected function getCurrentPower($prj)
    {
        return rand(100, 200);
    }

    protected function getIrradiance($prj)
    {
        return rand(700, 900);
    }

    protected function getInvertersGenerating($prj)
    {
        return '1/1';
    }

    protected function getDevicesCommunicating($prj)
    {
        return rand(3, 4).'/4';
    }

    protected function getLastCom($prj)
    {
        return date('Y-m-d H:i:s');
    }

    protected function getAvgIrradiancePOA($prj)
    {
        return 0.0;
    }

    protected function getAvgModuleTemp($prj)
    {
        return 0.0;
    }

    protected function getMeasuredEnergy($prj)
    {
        return 0.0;
    }
}
