<?php

namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * App\Models\DataInverterTcp
 */
class DataInverterTcp extends Model
{
    public $id;
    public $projectId;
    public $devcode;
    public $time;
    public $error;
    public $lowAlarm;
    public $highAlarm;
    public $dcvolts;
    public $kw;
    public $kwh;

    public function initialize()
    {
        $this->setSource('solar_data_inverter_tcp');
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
            'dcvolts'     => 'dcvolts',
            'kw'          => 'kw',
            'kwh'         => 'kwh',
        );
    }
}
