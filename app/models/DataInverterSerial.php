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
    public $lineKw;
    public $operatingStatus;
    public $faultWord0;
    public $faultWord1;
    public $faultWord2;
    public $dataCommStatus;

    public function initialize()
    {
        $this->setSource('solar_data_inverter_serial');
    }

    public function columnMap()
    {
        // Keys are the real names in the table and
        // the values their names in the application

        return array(
            'id'               => 'id'
            'projectId'        => 'project_id'
            'devcode'          => 'devcode'
            'time'             => 'time'
            'error'            => 'error'
            'lowAlarm'         => 'low_alarm'
            'highAlarm'        => 'high_alarm'
            'totalKwhDel'      => 'total_kwh_del'
            'voltsA'           => 'volts_a'
            'voltsB'           => 'volts_b'
            'voltsC'           => 'volts_c'
            'currentA'         => 'current_a'
            'currentB'         => 'current_b'
            'currentC'         => 'current_c'
            'dcInputVoltage'   => 'dc_input_voltage'
            'dcInputCurrent'   => 'dc_input_current'
            'lineFreq'         => 'line_freq'
            'lineKw'           => 'line_kw'
            'operatingStatus'  => 'inverter_operating_status'
            'faultWord0'       => 'inverter_fault_word_0'
            'faultWord1'       => 'inverter_fault_word_1'
            'faultWord2'       => 'inverter_fault_word_2'
            'dataCommStatus'   => 'data_comm_status'
        );
    }
}
