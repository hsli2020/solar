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

       #$projects = Projects::find();
       #$this->view->data = print_r($projects->toArray(), true);

       #$devices = Devices::find();
       #$this->view->data = print_r($devices->toArray(), true);

       #$envkit = DataEnvKits::find(['limit' => 10]);
       #$this->view->data = print_r($envkit->toArray(), true);

       #$genMeter = DataGenMeters::find(['limit' => 10]);
       #$this->view->data = print_r($genMeter->toArray(), true);

       #$inverter = DataInverterTcp::find(['limit' => 10]);
       #$this->view->data = print_r($inverter->toArray(), true);

       #$inverter = DataInverterSerial::find(['limit' => 10]);
       #$this->view->data = print_r($inverter->toArray(), true);

        $dataService = $this->dataService;
        $this->view->data = print_r($dataService->getProjectInfo(), true);
        $this->view->data = print_r($dataService->getDeviceInfo(1, 'mb-001'), true);
        $this->view->data = print_r($dataService->getDeviceInfo(), true);

       #$solarService = $this->solarService;
       #$solarService->ping();
    }

    public function tableAction()
    {
        $this->view->pageTitle = 'Table';

        // Get Projects Information
        $projects = [];
        foreach (Projects::find('active=1')->toArray() as $project) {
            $id = $project['id'];
            $projects[$id] = $project['name'];
        }

        $modelMap = [
            'solar_data_inverter_tcp' => 'DataInverterTcp',
            'solar_data_inverter_serial' => 'DataInverterSerial',
            'solar_data_genmeter' => 'DataGenMeters',
            'solar_data_envkit' => 'DataEnvKits',
        ];

        // Get Data of Devices
        $data = [];

        $devices = Devices::find();
        foreach ($devices as $device) {
            $projectId = $device->projectId;
            $devcode = $device->code;
            $devname = $device->name;

            $data[$projectId]['name'] = $projects[$projectId];

            $criteria = [
                "conditions" => "projectId=?1 AND devcode=?2 AND error=0",
                "bind"       => array(1 => $projectId, 2 => $devcode),
                "order"      => "id DESC",
                "limit"      => 1
            ];

            if (!isset($modelMap[$device->table])) {
                continue;
            }

            $modelClass = 'App\\Models\\'.$modelMap[$device->table];

            $row = $modelClass::findFirst($criteria);
            $row->time = $this->toLocalTime($row->time);

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
