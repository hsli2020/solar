<?php
namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * App\Models\DataInverterSerial
 */
class DataInverterSerial extends Model
{
    public $id;
    public $projectId;
    public $devcode;
    public $time;
    public $error;
    public $lowAlarm;
    public $highAlarm;
    public $totalKwhDel;
    public $voltsA;
    public $voltsB;
    public $voltsC;
    public $currentA;
    public $currentB;
    public $currentC;
    public $dcInputVoltage;
    public $dcInputCurrent;
    public $lineFreq;
    public $kw;
    public $operatingStatus;
    public $faultWord0;
    public $faultWord1;
    public $faultWord2;
    public $dataCommStatus;

    public function initialize()
    {
        $this->setSource('solar_data_inverter_serial');
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
            'id'                         => 'id',
            'project_id'                 => 'projectId',
            'devcode'                    => 'devcode',
            'time'                       => 'time',
            'error'                      => 'error',
            'low_alarm'                  => 'lowAlarm',
            'high_alarm'                 => 'highAlarm',
            'total_kwh_del'              => 'totalKwhDel',
            'volts_a'                    => 'voltsA',
            'volts_b'                    => 'voltsB',
            'volts_c'                    => 'voltsC',
            'current_a'                  => 'currentA',
            'current_b'                  => 'currentB',
            'current_c'                  => 'currentC',
            'dc_input_voltage'           => 'dcInputVoltage',
            'dc_input_current'           => 'dcInputCurrent',
            'line_freq'                  => 'lineFreq',
            'line_kw'                    => 'kw',
            'inverter_operating_status'  => 'operatingStatus',
            'inverter_fault_word_0'      => 'faultWord0',
            'inverter_fault_word_1'      => 'faultWord1',
            'inverter_fault_word_2'      => 'faultWord2',
            'data_comm_status'           => 'dataCommStatus',
        );
    }
}
