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
                    $this->devices[$projectId][$devcode] = $device->toArray();
                }
            }
        }

        return $this->devices;
    }

    // $dev='mb-xxx'
    public function getDevice($prj, $dev)
    {
        $devices = $this->getAll();
        return isset($devices[$prj][$dev]) ? $devices[$prj][$dev] : [];
    }

    public function getTable($prj, $dev)
    {
        $device = $this->getDevice($prj, $dev);
        return $device['table'];
    }

    public function getTableColumns($prj, $dev)
    {
        $table = $this->getTable($prj, $dev);
        $columns = $this->db->fetchAll("DESC $table");
        unset($columns[0]); // remove id
        return array_column($columns, 'Field');
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

    public function add($projectId, $devices)
    {
        foreach ($devices as $info) {
            $device = new Devices();
            $device->projectId = $projectId;
            $device->code  = $info['devcode'];
            $device->type  = $info['devtype'];
            $device->table = $info['table']; // TODO: getTableName from type
            $device->desc  = '';
            $device->save();
        }
    }
}
