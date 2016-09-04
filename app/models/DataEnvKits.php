<?php
namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * App\Models\DataEnvKits
 */
class DataEnvKits extends Model
{
    public $id;
    public $projectId;
    public $devcode;
    public $time;
    public $error;
    public $lowAlarm;
    public $highAlarm;
    public $OAT;
    public $PANELT;
    public $IRR;

    public function initialize()
    {
        $this->setSource('solar_data_envkit');
    }

    public function columnMap()
    {
        // Keys are the real names in the table and
        // the values their names in the application

        return array(
            'id'         => 'id',
            'projectId'  => 'project_id',
            'devcode'    => 'devcode',
            'time'       => 'time',
            'error'      => 'error',
            'lowAlarm'   => 'low_alarm',
            'highAlarm'  => 'high_alarm',
            'OAT'        => 'OAT',
            'PANELT'     => 'PANELT',
            'IRR'        => 'IRR',
        );
    }
}
