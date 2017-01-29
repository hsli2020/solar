<?php

namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * App\Models\DataInverterPvp
 */
class DataInverterPvp extends Model
{
    public $id;
    public $projectId;
    public $devcode;
    public $time;
    public $error;
    public $lowAlarm;
    public $highAlarm;
    public $kw;
    public $status;
    public $faultCode0;
    public $faultCode1;
    public $faultCode2;
    public $voltA;
    public $voltB;
    public $voltC;

    public function initialize()
    {
        $this->setSource('solar_data_inverter_pvp');
    }

    public function afterFetch()
    {
        $this->time = toLocalTime($this->time);
    }

    public function columnMap()
    {
        // Keys are the real names in the table and
        // the values their names in the application

        return array(
            'id'           => 'id',
            'project_id'   => 'projectId',
            'devcode'      => 'devcode',
            'time'         => 'time',
            'error'        => 'error',
            'low_alarm'    => 'lowAlarm',
            'high_alarm'   => 'highAlarm',
            'kw'           => 'kw',
            'status'       => 'status',
            'fault_code_0' => 'faultCode0',
            'fault_code_1' => 'faultCode1',
            'fault_code_2' => 'faultCode2',
            'vln_a'        => 'vlnA',
            'vln_b'        => 'vlnB',
            'vln_c'        => 'vlnC',
        );
    }
}


