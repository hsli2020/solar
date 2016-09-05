<?php

namespace App\Controllers;

use App\Models\Projects;
use App\Models\Devices;
use App\Models\DataEnvKits;
use App\Models\DataGenMeters;
use App\Models\DataInverterTcp;
use App\Models\DataInverterSerial;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->pageTitle = 'My Dashboard';
        return $this->dispatcher->forward([
            'controller' => 'index',
            'action' => 'chart'
        ]);
    }

    public function testAction()
    {
        $this->view->pageTitle = 'Test Page';
        $this->view->data = __METHOD__;
    }

    public function tableAction()
    {
        $this->view->pageTitle = 'Table';

        $dataService = $this->dataService;

        $data = [];

        $devices = Devices::find();
        foreach ($devices as $device) {
            $projectId = $device->projectId;
            $devcode = $device->code;
            $devname = $device->name;

            $data[$projectId]['name'] = $dataService->getProjectName($projectId);

            $criteria = [
                "conditions" => "projectId=?1 AND devcode=?2 AND error=0",
                "bind"       => array(1 => $projectId, 2 => $devcode),
                "order"      => "id DESC",
                "limit"      => 1
            ];

            $modelClass = $dataService->getModelName($projectId, $devcode);

            $row = $modelClass::findFirst($criteria);
           #$row->time = $this->toLocalTime($row->time);

            if ($devname == 'Inverter') {
                $data[$projectId][$devname][] = $row->toArray();
            } else {
                $data[$projectId][$devname] = $row->toArray();
            }
        }

        $this->view->data = $data;
    }

    public function chartAction()
    {
        $this->view->pageTitle = 'Chart';
    }

    protected function toLocaltime($timeStr)
    {
        $date = new \DateTime($timeStr, new \DateTimeZone('UTC'));
        $date->setTimezone(new \DateTimeZone('EST'));
        return $date->format('Y-m-d H:i');
    }
}
