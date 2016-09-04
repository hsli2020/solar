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
            'project_id' => 'projectId',
            'devcode'    => 'devcode',
            'time'       => 'time',
            'error'      => 'error',
            'low_alarm'  => 'lowAlarm',
            'high_alarm' => 'highAlarm',
            'kva'        => 'kva',
            'kwh_del'    => 'kwhDel',
            'kwh_rec'    => 'kwhRec',
            'vln_a'      => 'vinA',
            'vln_b'      => 'vinB',
            'vln_c'      => 'vinC',
        );
    }
}
