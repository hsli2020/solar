<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class DeviceService extends Injectable
{
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
