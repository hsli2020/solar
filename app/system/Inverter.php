<?php

namespace App\System;

class Inverter extends Device
{
    public function getType()
    {
        $types = [
            'PVP'     => 'AE Inverter',
            'SMA'     => 'SMA Inverter',
            'FRONIUS' => 'Fronius Inverter',
        ];

        $model = $this->model;

        return isset($types[$model]) ? $types[$model] : '';
    }

    public function getKW($period)
    {
        $table = $this->getDeviceTable();

        $column = ($this->model == 'SERIAL') ?  'line_kw' : 'kw';
       #$column = ($projectId == 2) ? 'line_kw' : 'kw';

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT SUM($column) AS kw FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['kw'];
           #return $result['kw'] / 60.0; // to kwH
        }

        return 0;
    }

    public function getAvgKW($period)
    {
        $table = $this->getDeviceTable();

        $column = ($this->model == 'SERIAL') ?  'line_kw' : 'kw';
       #$column = ($projectId == 2) ? 'line_kw' : 'kw';

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT AVG($column) AS kw FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['kw'];
           #return $result['kw'] / 60.0; // to kwH
        }

        return 0;
    }

    public function getLatestKW()
    {
        $data = $this->getLatestData();
        if ($data) {
            if (isset($data['kw'])) {
                return $data['kw'];
            }
            if (isset($data['line_kw'])) {
                return $data['line_kw'];
            }
        }
        return false;
    }

    public function getSnapshotKW()
    {
        $table = $this->getDeviceTable();

        $column = ($this->model == 'SERIAL') ?  'line_kw' : 'kw';
       #$column = ($projectId == 2) ? 'line_kw' : 'kw';

        list($start, $end) = $this->getPeriod('SNAPSHOT');

        $sql = "SELECT AVG($column) AS kw".
               "  FROM $table".
               " WHERE time>='$start' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['kw'];
           #return $result['kw'] / 60.0; // to kwH
        }

        return 0;
    }

    public function export($file, $interval, $start, $end)
    {
        $table = $this->getDeviceTable();

        $sql = "SELECT * FROM $table WHERE time>='$start' AND time<'$end' AND error=0";

        if ($interval > 1) {
            $seconds = $interval*60; // convert to seconds
            $column = ($this->model == 'SERIAL') ?  'line_kw' : 'kw';
            $sql = "SELECT time, ROUND(SUM($column)) AS kw
                      FROM $table
                     WHERE time >= '$start' AND time<'$end' AND error=0
                     GROUP BY UNIX_TIMESTAMP(time) DIV $seconds";
        }

        $data = $this->getDb()->fetchAll($sql);

        fputs($file, $this->type. ' ' .$this->code. PHP_EOL);
        fputcsv($file, $this->getCsvTitle($interval));

        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fputs($file, PHP_EOL);
    }

    protected function getCsvTitle($interval)
    {
        // time(UTC),error,lowalarm,highalarm,"Power_AC (kW)","Status","Status_Vendor","Voltage_AN (Volts)","Voltage_BN (Volts)","Voltage_CN (Volts)"
        // time(UTC),error,lowalarm,highalarm,"Power_AC_kW (kW)","Mode_SMA","Error","Voltage_AC_LL_AB (Volts)","Voltage_AC_LL_BC (Volts)","Voltage_AC_LL_CA (Volts)"
        // time(UTC),error,lowalarm,highalarm,"Total kWh Delivered (kWh)","Volts A L-N (Volts)","Volts B L-N (Volts)","Volts C L-N (Volts)","Current A (Amps)","Current B (Amps)","Current C (Amps)","DC Input Voltage (Volts)","DC Input Current (Amps)","Line Frequency (Hz)","Line kW (kW)","Inverter Operating Status (State)","Inverter Fault Word 0","Inverter Fault Word 1","Inverter Fault Word 2","Data Comm Status"

        $title1 = ["time(UTC)","error","lowalarm","highalarm","kw (kW)","invsts","f000-f015","f100-f110","f200-f211","vln_a (Volts)","vln_b (Volts)","vln_c (Volts)" ];
        $titlex = ["time(UTC)","kw (kW)" ];

        if ($interval == 1) {
            return $title1;
        }
        return $titlex;
    }
}
