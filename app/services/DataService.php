<?php

namespace App\Service;

use Phalcon\Di\Injectable;

use App\Models\Projects;
use App\Models\Devices;
use App\Models\DataEnvKits;
use App\Models\DataGenMeters;
use App\Models\DataInverterTcp;
use App\Models\DataInverterSerial;

class DataService extends Injectable
{
    /**
     * Projects
     */
    public function getAllProjects()
    {
        static $projects = [];

        if (!$projects) {
            $result = Projects::find('active=1');
            foreach ($result as $project) {
                $id = $project->id;
                $projects[$id] = $project->toArray();
            }
        }

        return $projects;
    }

    public function getProject($id)
    {
        $projects = $this->getAllProjects();
        return $projects[$id];
    }

    public function getProjectName($id)
    {
        $project = $this->getProject($id);
        return $project['name'];
    }

    public function getProjectFtpDir($id)
    {
        $project = $this->getProject($id);
        return $project['ftpdir'];
    }

    /**
     * Devices
     */
    public function getAllDevices()
    {
        static $devices = [];

        if (!$devices) {
           #$result = Devices::find("projectId=$prj AND code='$dev'");
            $result = Devices::find();
            foreach ($result as $device) {
                $projectId = $device->projectId;
                $devcode = $device->code;
                $devices[$projectId.'-'.$devcode] = $device->toArray();
            }
        }

        return $devices;
    }

    // $dev='mb-xxx'
    public function getDevice($prj, $dev)
    {
        $devices = $this->getAllDevices();

        $key = $prj.'-'.$dev;
        return isset($devices[$key]) ? $devices[$key] : [];
    }

    // $type='Inverter|GenMeter|EnvKit'
    public function getDevicesOfType($prj, $type)
    {
        $devices = [];

        $result = Devices::find("projectId=$prj AND type='$type'");
        foreach ($result as $device) {
            $devices[] = $device->toArray();
        }

        return $devices;
    }

    public function getTableName($prj, $dev)
    {
        $device = $this->getDevice($prj, $dev);
        return $device['table'];
    }

    public function getModelName($prj, $dev)
    {
        $modelMap = [
            'solar_data_inverter_tcp' => 'DataInverterTcp',
            'solar_data_inverter_serial' => 'DataInverterSerial',
            'solar_data_genmeter' => 'DataGenMeters',
            'solar_data_envkit' => 'DataEnvKits',
        ];

        $table = $this->getTableName($prj, $dev);

        return 'App\\Models\\'.$modelMap[$table];
    }

    /**
     * Data
     */
    public function getSnapshot()
    {
        $data = [];

        $devices = Devices::find();
        foreach ($devices as $device) {
            $projectId = $device->projectId;
            $devcode = $device->code;
            $devtype = $device->type;

            $data[$projectId]['name'] = $this->getProjectName($projectId);

            $criteria = [
                "conditions" => "projectId=?1 AND devcode=?2 AND error=0",
                "bind"       => array(1 => $projectId, 2 => $devcode),
                "order"      => "id DESC",
                "limit"      => 1
            ];

            $modelClass = $this->getModelName($projectId, $devcode);

            $row = $modelClass::findFirst($criteria);
            $row->time = substr($row->time, 0, -3);

            if ($devtype == 'Inverter') {
                $data[$projectId][$devtype][] = $row->toArray();
            } else {
                $data[$projectId][$devtype] = $row->toArray();
            }
        }

        return $data;
    }

    public function getChartData($prj, $dev, $fld)
    {
        $table = $this->getTableName($prj, $dev);

        $sql = "(SELECT `time`, $fld FROM $table WHERE error=0 ORDER BY `time` DESC LIMIT 300) ORDER BY `time` ASC";
        $result = $this->db->query($sql);

        $data = [];
        while ($row = $result->fetch()) {
            $data[] = [strtotime($row['time'])*1000, floatval($row[$fld])];
        }

        return $data;
    }

    public function getData($prj, $dev)
    {
    }
}
