<?php

namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * App\Models\DataInverterSma
 */
class DataInverterSma extends Model
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
    public $faultCode;
    public $voltA;
    public $voltB;
    public $voltC;

    public function initialize()
    {
        $this->setSource('data_inverter_sma');
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
            'id'          => 'id',
            'project_id'  => 'projectId',
            'devcode'     => 'devcode',
            'time'        => 'time',
            'error'       => 'error',
            'low_alarm'   => 'lowAlarm',
            'high_alarm'  => 'highAlarm',
            'kw'          => 'kw',
            'status'      => 'status',
            'fault_code'  => 'faultCode',
            'volt_a'      => 'voltA',
            'volt_b'      => 'voltB',
            'volt_c'      => 'voltC',
        );
    }
}
