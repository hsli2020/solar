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
        $dataService->ping();

        $solarService = $this->solarService;
        $solarService->ping();
    }

    public function tableAction()
    {
        $this->view->pageTitle = 'Table';

        // TODO: put these stuff to database
        $projects = [
            1 => [
                'EnvKit'   => [ 'table' => 'solar_data_3', 'devcode' => 'mb-071' ],
                'GenMeter' => [ 'table' => 'solar_data_5', 'devcode' => 'mb-100' ],
                'Inverter' => [
                    'table' => 'solar_data_1',
                    'column' => 'kw',
                    'devcodes' => [ 'mb-001', 'mb-002', 'mb-003' ]
                ]
            ],

            2 => [
                'EnvKit'   => [ 'table' => 'solar_data_3', 'devcode' => 'mb-047' ],
                'GenMeter' => [ 'table' => 'solar_data_5', 'devcode' => 'mb-100' ],
                'Inverter' => [
                    'table' => 'solar_data_4',
                    'column' => 'line_kw',
                    'devcodes' => [ 'mb-080', 'mb-081', 'mb-xxx' ]
                ]
            ]
        ];

        $data = [];

        foreach ($projects as $prj => $info) {
            // EnvKit
            $data[$prj]['EnvKit'] = array (
                'OAT' => '',
                'PANELT' => '',
                'IRR' => '',
            );
            $table = $info['EnvKit']['table'];
            $devcode = $info['EnvKit']['devcode'];

            $sql = "SELECT * FROM $table WHERE project_id=$prj AND devcode='$devcode' ORDER BY id DESC LIMIT 1";
            $result = $this->db->query($sql)->fetchAll(\Phalcon\Db::FETCH_ASSOC);
            if ($result) {
                $data[$prj]['EnvKit'] = $result[0];

                $time = $data[$prj]['EnvKit']['time'];
                $data[$prj]['EnvKit']['time'] = $this->toLocaltime($time);
            }

            // GenMeter
            $data[$prj]['GenMeter'] = array(
                'kva'   => '',
                'vln_a' => '',
                'vln_b' => '',
                'vln_c' => '',
            );
            $table = $info['GenMeter']['table'];
            $devcode = $info['GenMeter']['devcode'];

            $sql = "SELECT * FROM $table WHERE project_id=$prj AND devcode='$devcode' ORDER BY id DESC LIMIT 1";
            $result = $this->db->query($sql)->fetchAll(\Phalcon\Db::FETCH_ASSOC);
            if ($result) {
                $data[$prj]['GenMeter'] = $result[0];
            }

            // Inverter 1-2-3
            $table = $info['Inverter']['table'];
            $column = $info['Inverter']['column'];
            $devcodes = $info['Inverter']['devcodes'];

            foreach ($devcodes as $i => $devcode) {
                $data[$prj]['Inverter'][$i+1] = '';
                $sql = "SELECT $column FROM $table WHERE project_id=$prj AND devcode='$devcode' ORDER BY id DESC LIMIT 1";
                $result = $this->db->fetchColumn($sql);
                if ($result) {
                    $data[$prj]['Inverter'][$i+1] = $result;
                }
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
        return $date->format('Y-m-d H:i:s');
    }
}
