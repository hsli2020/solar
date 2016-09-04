<?php
namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * App\Models\DataGenMeters
 */
class DataGenMeters extends Model
{
    public $id;
    public $projectId;
    public $devcode;
    public $time;
    public $error;
    public $lowAlarm;
    public $highAlarm;
    public $kva;
    public $kwhDel;
    public $kwhRec;
    public $vinA;
    public $vinB;
    public $vinC;

    public function initialize()
    {
        $this->setSource('solar_data_genmeter');
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
            'kva'        => 'kva',
            'kwhDel'     => 'kwh_del',
            'kwhRec'     => 'kwh_rec',
            'vinA'       => 'vln_a',
            'vinB'       => 'vln_b',
            'vinC'       => 'vln_c',
        );
    }
}
