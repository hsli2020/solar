<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class SnapshotService extends Injectable
{
    public function load()
    {
        $nothing = [
            'rows'  => [],
            'total' => [
                'current_power' => '',
                'project_size_ac' => '',
                'average_irradiance' => '',
                'performance' => '',
            ]
        ];

        $result = $this->db->fetchAll("SELECT * FROM snapshot");

        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return $nothing; // if user not logged in, display nothing
        }

        $userProjects = $this->userService->getUserProjects($auth['id']);

        $totalPower = 0;
        $totalProjectSizeAC = 0;
        $averageIrradiance = 0;

        $data = [];
        foreach ($result as $key => $val) {
            if (!in_array($val['project_id'], $userProjects)) {
                continue; // current user doesn't have permission to the project
            }

            $result[$key]['error'] = [];

            // check if GCPR is too low
            if ($val['GCPR'] >= 200) {
               #$result[$key]['error']['GCPR'] = 'red';
            }
            else if ($val['GCPR'] >= 90) {
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

            // check if irradiance is too low
           #$result[$key]['error']['current_power'] = 1;
            $irr = 1000*$val['current_power'] / $val['project_size_ac'];
            if ($irr > 100 && $val['irradiance'] < ($irr/2)) {
                $result[$key]['error']['irradiance'] = 'text-red';
            }

            // check if current_power is too low
           #if ($val['current_power'] < 2 && $val['irradiance'] >= 100) {
           #    $result[$key]['error']['inverters_generating'] = 'red';
           #} else if ($val['current_power'] < 4) {
           #    list($a, $b) = explode('/', $val['inverters_generating']);
           #    $result[$key]['inverters_generating'] = "0/$b";
           #}

            // highlight inverters_generating if something is wrong
            if ($val['current_power'] < 4) {
                list($a, $b) = explode('/', $val['inverters_generating']);
                $result[$key]['inverters_generating'] = "0/$b";
            }
            list($a, $b) = explode('/', $val['inverters_generating']);
            if ($a != $b) {
                $result[$key]['error']['inverters_generating'] = 'red';
            }

            // highlight devices_communicating if something is wrong
            list($a, $b) = explode('/', $val['devices_communicating']);
            if ($a != $b) {
                $result[$key]['error']['devices_communicating'] = 'red';
            }

            $totalPower         += $val['current_power'];
            $totalProjectSizeAC += $val['project_size_ac'];
            $averageIrradiance  += $val['irradiance'];

            $result[$key]['project_size_ac'] = number_format($val['project_size_ac']);
            $result[$key]['current_power']   = number_format($val['current_power']);

           #$result[$key]['error']['last_com'] = 1;
           #$result[$key]['error']['Avg_Irradiance_POA'] = 1;
           #$result[$key]['error']['Avg_Module_Temp'] = 1;
           #$result[$key]['error']['Measured_Energy'] = 1;

            $data[$key] = $result[$key];
        }

        $total['current_power']      = number_format($totalPower);
        $total['project_size_ac']    = number_format($totalProjectSizeAC);
        $total['average_irradiance'] = number_format($averageIrradiance/count($result));
        $total['performance']        = number_format($totalPower / $totalProjectSizeAC * 100);

        return [ 'rows' => $data, 'total' => $total ];
    }

    public function generate()
    {
        echo 'Snapshot generating...';

        $projects = $this->projectService->getAll();

        foreach ($projects as $project) {
            $id = $project->id;
            $name = $project->name;
            $sizeAC = $project->capacityAC;

            $GCPR                 = $this->getGCPR($project);
            $currentPower         = $this->getCurrentPower($project);
            $irradiance           = $this->getIrradiance($project);
            $temperature          = $this->getTemperature($project);
            $invertersGenerating  = $this->getGeneratingInverters($project, $currentPower, $irradiance);
            $devicesCommunicating = $this->getCommunicatingDevices($project);
            $lastCom              = $this->getLastCom($project);

            $sql = "REPLACE INTO snapshot SET"
                 . " project_id = $id,"
                 . " project_name = '$name',"
                 . " project_size_ac = '$sizeAC',"
                 . " GCPR = '$GCPR',"
                 . " current_power = '$currentPower',"
                 . " irradiance = '$irradiance',"
                 . " temperature = '$temperature',"
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
        $kw = $project->getSnapshotKW();
        return round($kw);
    }

    protected function getIrradiance($project)
    {
        $irr = $project->getSnapshotIRR();
        return round($irr);
    }

    protected function getTemperature($project)
    {
        $oat = $project->getSnapshotOAT();
        return round($oat);
    }

    protected function getGeneratingInverters($project, $currentPower, $irradiance)
    {
        $total = $project->getTotalInverters();
        $working = $project->getGeneratingInverters();

        if ($currentPower < 4) {
            $working = 0;
        }

        return "$working/$total";
    }

    protected function getCommunicatingDevices($project)
    {
        $total = $project->getTotalDevices();
        $working = $project->getCommunicatingDevices();

        return "$working/$total";
    }

    protected function getLastCom($project)
    {
        return $project->getSnapshotTime();
    }
}
