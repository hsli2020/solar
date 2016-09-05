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
    public function getAllDevices($prj, $dev)
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

        if (is_null($prj) && is_null($dev)) {
            return $devices;
        }

        $key = $prj.'-'.$dev;

        return $devices[$key] ? $devices[$key] : [];
    }

    public function getTableName($prj, $dev)
    {
        $device = $this->getAllDevices($prj, $dev);
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

    public function getLatestData($prj, $dev, $fld)
    {
    }

    public function getData($prj, $dev, $fld)
    {
    }
}
