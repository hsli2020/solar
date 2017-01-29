<?php

namespace App\Service;

use Phalcon\Di\Injectable;

use App\Models\Devices;
use App\Models\DataEnvKits;
use App\Models\DataGenMeters;
use App\Models\DataInverterTcp;
use App\Models\DataInverterSerial;

class DeviceService extends Injectable
{
    protected $devices = [];

    public function getAll()
    {
        if (!$this->devices) {
            $projects = $this->projectService->getAll();
            foreach ($projects as $projectId => $project) {
                $result = Devices::find("projectId=$projectId");
                foreach ($result as $device) {
                    $devcode = $device->code;
                    $this->devices[$projectId.'-'.$devcode] = $device->toArray();
                }
            }
        }

        return $this->devices;
    }

    // $dev='mb-xxx'
    public function getDevice($prj, $dev)
    {
        $devices = $this->getAll();

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
}
