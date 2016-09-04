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

    public function columnMap()
    {
        // Keys are the real names in the table and
        // the values their names in the application

        return array(
            'id'          => 'id',
            'projectId'   => 'project_id',
            'devcode'     => 'devcode',
            'time'        => 'time',
            'error'       => 'error',
            'lowAlarm'    => 'low_alarm',
            'highAlarm'   => 'high_alarm',
            'dcvolts'     => 'dcvolts',
            'kw'          => 'kw',
            'kwh'         => 'kwh',
        );
    }
}
