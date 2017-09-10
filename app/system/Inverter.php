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
    }
}
