<?php

namespace App\Service;

use Phalcon\Di\Injectable;

use App\Models\Projects;
use App\Models\Devices;
use App\Models\DataEnvKits;
use App\Models\DataGenMeters;
use App\Models\DataInverterTcp;
use App\Models\DataInverterSerial;
use App\Models\DataInverterSma;
use App\Models\DataInverterPvp;

class DataService extends Injectable
{
    public function getSnapshot()
    {
        $data = [];

        list($start, $end) = $this->getPeriod('X-MINUTES-AGO');

        $projects = $this->projectService->getAll();
        foreach ($projects as $projectId => $project) {
            $devices = Devices::find("projectId=$projectId");
            foreach ($devices as $device) {
               #$projectId = $device->projectId;
                $devcode = $device->code;
                $devtype = $device->type;

                $data[$projectId]['name'] = $project['name'];

                $criteria = [
                    "conditions" => "projectId=:project: AND devcode=:devcode: AND time >= :start: AND time < :end: AND error=0",
                    "bind"       => ['project' => $projectId, 'devcode' => $devcode, 'start' => $start, 'end' => $end],
                    "order"      => "id DESC",
                    "limit"      => 1
                ];

                $modelClass = $this->deviceService->getModelName($projectId, $devcode);

                $row = $modelClass::findFirst($criteria);
                if ($row) {
                    $row->time = substr($row->time, 0, -3);

                    if ($devtype == 'Inverter') {
                        $data[$projectId][$devtype][] = $row->toArray();
                    } else {
                        $data[$projectId][$devtype] = $row->toArray();
                    }
                }
            }
        }

        return $data;
    }

    public function getChartData($prj, $dev, $fld)
    {
        $table = $this->deviceService->getTable($prj, $dev);

        $sql = "(SELECT `time`, $fld FROM $table WHERE error=0 ORDER BY `time` DESC LIMIT 300) ORDER BY `time` ASC";
        $result = $this->db->query($sql);

        $data = [];
        while ($row = $result->fetch()) {
            $row['time'] = toLocalTime($row['time']);
            $data[] = [strtotime($row['time'])*1000, floatval($row[$fld])];
        }

        return $data;
    }

    public function getIRR($prj, $period)
    {
        $device  = $this->deviceService->getDevicesOfType($prj, 'EnvKit');
        $devcode = $device[0]; // only one envkit per site

        $criteria = $this->getEnvKitCriteria($prj, $devcode, $period);
        $criteria["column"] = "IRR";

        $result = DataEnvKits::sum($criteria);

        return $result;
    }

    // only for SnapshotService
    public function getLatestIRR($prj)
    {
        $device  = $this->deviceService->getDevicesOfType($prj, 'EnvKit');
        $devcode = $device[0]; // only one envkit per site

        $criteria = $this->getEnvKitCriteria($prj, $devcode, 'X-MINUTES-AGO');
       #$criteria["column"] = "IRR";

        $result = DataEnvKits::findFirst($criteria);
        if (!$result) {
           #fpr($criteria);
        }

        return $result ? $result->IRR : 0;
    }

    public function getTMP($prj, $period)
    {
        $device  = $this->deviceService->getDevicesOfType($prj, 'EnvKit');
        $devcode = $device[0]; // only one envkit per site

        $criteria = $this->getEnvKitCriteria($prj, $devcode, $period);
        $criteria["column"] = "PANELT";

        $result = DataEnvKits::sum($criteria);

        return $result;
    }

    public function getKW($prj, $period)
    {
        $devices = $this->deviceService->getDevicesOfType($prj, 'Inverter');

        $sum = 0;
        foreach ($devices as $devcode) {
            $criteria = $this->getInverterCriteria($prj, $devcode, $period);
            $criteria["column"] = "kw";
            $modelClass = $this->deviceService->getModelName($prj, $devcode);
            $result = $modelClass::sum($criteria);
            $sum += $result;
        }

        return $sum;
    }

    // only for SnapshotService
    public function getLatestKW($prj)
    {
        $devices = $this->deviceService->getDevicesOfType($prj, 'Inverter');

        $sum = 0;
        foreach ($devices as $devcode) {
            $criteria = $this->getInverterCriteria($prj, $devcode, 'X-MINUTES-AGO');
           #$criteria["column"] = "kw";

            $modelClass = $this->deviceService->getModelName($prj, $devcode);

            $result = $modelClass::findFirst($criteria);

            if ($result) {
                $sum += $result->kw;
            } else {
               #fpr($criteria);
            }
        }

        return $sum;
    }

    // only for SnapshotService
    public function getLatestTime($prj)
    {
        $device  = $this->deviceService->getDevicesOfType($prj, 'EnvKit');
        $devcode = $device[0]; // only one envkit per site

        $criteria = $this->getEnvKitCriteria($prj, $devcode, 'X-MINUTES-AGO');
        $modelClass = $this->deviceService->getModelName($prj, $devcode);

        $result = $modelClass::findFirst($criteria);

        return $result ? $result->time : gmdate('Y-m-d H:i:00', strtotime('-20 minute'));
    }

    public function getKWHREC($prj, $period)
    {
        $device  = $this->deviceService->getDevicesOfType($prj, 'GenMeter');
        $devcode = $device[0]; // only one genmeter per site

        $criteria = $this->getGenMeterCriteria($prj, $devcode, $period);

        $criteria["order"]  = "time";
        $first = DataGenMeters::findFirst($criteria);

        $criteria["order"]  = "time DESC";
        $last = DataGenMeters::findFirst($criteria);

        return ($first && $last) ? $last->kwhRec - $first->kwhRec : 0;
    }

    protected function getEnvKitCriteria($prj, $devcode, $period)
    {
        list($start, $end) = $this->getPeriod($period);

        $criteria = [
            'conditions' => implode(' AND ', [
                'projectId = :projectId:',
                'devcode = :devcode:',
                'time >= :start: AND time < :end:',
                'error = 0',
            ]),
            "bind" => [
                'projectId' => $prj,
                'devcode'   => $devcode,
                'start'     => $start,
                'end'       => $end,
            ],
        ];

        return $criteria;
    }

    protected function getInverterCriteria($prj, $devcode, $period)
    {
        list($start, $end) = $this->getPeriod($period);

        $criteria = [
            'conditions' => implode(' AND ', [
                'projectId = :projectId:',
                'devcode = :devcode:',
                'time >= :start: AND time < :end:',
                'error = 0',
            ]),
            "bind" => [
                'projectId' => $prj,
                'devcode'   => $devcode,
                'start'     => $start,
                'end'       => $end,
            ],
        ];

        return $criteria;
    }

    protected function getGenMeterCriteria($prj, $devcode, $period)
    {
        list($start, $end) = $this->getPeriod($period);

        $criteria = [
            'conditions' => implode(' AND ', [
                'projectId = :projectId:',
                'devcode = :devcode:',
                'time >= :start: AND time < :end:',
                'error = 0',
            ]),
            "bind" => [
                'projectId' => $prj,
                'devcode'   => $devcode,
                'start'     => $start,
                'end'       => $end,
            ],
        ];

        return $criteria;
    }

    protected function getPeriod($period)
    {
       #$Y = $this->year;
       #$M = $this->month;
       #$D = $this->day;

        switch (strtoupper($period)) {
        case 'LAST-HOUR':
            $start = gmdate('Y-m-d H:00:00', strtotime('-1 hours'));
            $end   = gmdate('Y-m-d H:00:00');
            break;

        case 'TODAY':
            $start = gmdate('Y-m-d h:i:s', mktime(0, 0, 0));
            $end   = gmdate('Y-m-d h:i:s', mktime(23, 59, 59));
            break;

        case 'MONTH-TO-DATE':
            $start = gmdate('Y-m-d h:i:s', mktime(0, 0, 0, date('n'), 1));
            $end   = gmdate('Y-m-d h:i:s', mktime(23, 59, 59));
            break;

        case 'THIS-MONTH':
            $start = gmdate('Y-m-01 h:i:s', mktime(0, 0, 0));
            $end   = gmdate('Y-m-t h:i:s', mktime(23, 59, 59));
            break;

        case 'X-MINUTES-AGO':
            // last minute (15 minutes ago)
            $start = gmdate('Y-m-d H:i:00', strtotime('-16 minute'));
            $end   = gmdate('Y-m-d H:i:30', strtotime('-15 minute'));
            break;

        default:
            throw new InvalidArgumentException("Bad argument '$period'");
            break;
        }

        return [ $start, $end ];
    }

    public function getRefData($prj, $year, $month)
    {
        return $this->db->fetchOne("SELECT * FROM project_reference_data
            WHERE project_id=$prj AND year=$year AND month=$month");
    }

    public function getMonthlyBudget($prj, $year, $month)
    {
        return $this->db->fetchOne("SELECT * FROM monthly_budget
            WHERE project_id=$prj AND year=$year AND month=$month");
    }

    public function getPR($prj)
    {
        $site = $this->projectService->get($prj);

        $DC_Nameplate_Capacity    = $site['DC_Nameplate_Capacity'];
        $AC_Nameplate_Capacity    = $site['AC_Nameplate_Capacity'];

        $Module_Power_Coefficient = $site['Module_Power_Coefficient'];
        $Inverter_Efficiency      = $site['Inverter_Efficiency'];
        $Transformer_Loss         = $site['Transformer_Loss'];
        $Other_Loss               = $site['Other_Loss'];

        $Avg_Irradiance_POA       = $this->getIRR($prj, 'LAST-HOUR') / 60.0; // avg 60 minutes
        $Avg_Module_Temp          = $this->getTMP($prj, 'LAST-HOUR') / 60.0; // PANELT
        $Measured_Energy          = $this->getKW($prj,  'LAST-HOUR') / 60.0; // sum 60 minutes

        if ($DC_Nameplate_Capacity == 0) return 0;

        $Maximum_Theory_Output = ($Avg_Irradiance_POA / 1000) * $DC_Nameplate_Capacity;

        if ($Maximum_Theory_Output == 0) return 0;

        $Temperature_Losses = ($Maximum_Theory_Output * ($Module_Power_Coefficient * (25 - $Avg_Module_Temp))) / 1000.0;
        $Inverter_Losses = (1 - $Inverter_Efficiency) * ($Maximum_Theory_Output - $Temperature_Losses);

        if (($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses) > $AC_Nameplate_Capacity) {
            $Inverter_Clipping_Loss = $Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $AC_Nameplate_Capacity;
        } else {
            $Inverter_Clipping_Loss = 0;
        }

        $Transformer_Losses  = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss) * $Transformer_Loss;
        $Other_System_Losses = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss - $Transformer_Loss) * $Other_Loss;
        $Total_Losses = ($Temperature_Losses + $Inverter_Losses + $Inverter_Clipping_Loss + $Transformer_Loss + $Other_System_Losses) / $Maximum_Theory_Output;
        $Theoretical_Output = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss - $Transformer_Loss - $Other_System_Losses);

        if ($Theoretical_Output == 0) return 0;

        $GCS_Performance_Index = $Measured_Energy / $Theoretical_Output;

        return $GCS_Performance_Index;
    }
}
