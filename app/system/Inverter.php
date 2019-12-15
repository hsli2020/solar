<?php

namespace App\System;

class Inverter extends Device
{
    public function getInverterType()
    {
        $types = [
            'PVP'     => 'AE Inverter',
            'SMA'     => 'SMA Inverter',
            'FRONIUS' => 'Fronius Inverter',
        ];

        $model = $this->model;

        return isset($types[$model]) ? $types[$model] : '';
    }

    public function getCombiner()
    {
        return $this->reference;
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
        return $this->getLatestKW();
        /*
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
        */
    }

    public function export($interval, $start, $end)
    {
        $table = $this->getDeviceTable();

        $kwcol = ($this->model == 'SERIAL') ?  'line_kw' : 'kw';

        $fmt = ($interval == 24*60) ? '%Y-%m-%d' : '%Y-%m-%d %H:%i';

        $sql = "SELECT DATE_FORMAT(CONVERT_TZ(time, 'UTC', 'America/Toronto'), '$fmt') AS time, $kwcol AS kw FROM $table WHERE time>='$start' AND time<'$end' AND error=0";

        if ($interval > 5) {
            $seconds = $interval*60; // convert to seconds

            $kw = "ROUND(AVG($kwcol)) AS kw";
            if ($interval > 60) { // daily
                $kw = "ROUND(SUM($kwcol)/12) AS kw";
            }

            $sql = "SELECT DATE_FORMAT(CONVERT_TZ(time, 'UTC', 'America/Toronto'), '$fmt') AS time,
                           $kw
                      FROM $table
                     WHERE time >= CONVERT_TZ('$start', 'America/Toronto', 'UTC') AND
                           time <  CONVERT_TZ('$end',   'America/Toronto', 'UTC') AND error=0
                     GROUP BY UNIX_TIMESTAMP(time) DIV $seconds";
        }

        $data = $this->getDb()->fetchAll($sql);
        return array_column($data, null, 'time');
    }

    public function getDataToCompare($startTime, $endTime, $interval)
    {
        $table = $this->getDeviceTable();

        if ($interval > 0) {
            $seconds = $interval*60; // convert to seconds
            $column = ($this->model == 'SERIAL') ?  'line_kw' : 'kw';
            $sql = "SELECT CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time,
                           ROUND(AVG($column)) AS kw
                      FROM $table
                     WHERE time >= CONVERT_TZ('$startTime', 'America/Toronto', 'UTC') AND
                           time <  CONVERT_TZ('$endTime',   'America/Toronto', 'UTC') AND error=0
                     GROUP BY UNIX_TIMESTAMP(time) DIV $seconds";
            $data = $this->getDb()->fetchAll($sql);
            return array_column($data, 'kw', 'time');
        }

        return [];
    }

    public function getChartData($date)
    {
        $table = $this->getDeviceTable();

        $start = $date . ' 00:00:00';
        $end   = $date . ' 23:59:59';

        $column = ($this->model == 'SERIAL') ?  'line_kw' : 'kw';
        $sql = "SELECT time,
                       ROUND(SUM($column)) AS kw
                  FROM $table
                 WHERE time >= CONVERT_TZ('$start', 'America/Toronto', 'UTC') AND
                       time <= CONVERT_TZ('$end',   'America/Toronto', 'UTC') AND error=0
                 GROUP BY UNIX_TIMESTAMP(time) DIV 300";

        $result = $this->getDb()->fetchAll($sql);

        // utc time to local time
        $values = [];
        foreach ($result as $e) {
            $time = strtotime($e['time'].' UTC') + date('Z');
            $time -= $time%60; // floor to minute (no seconds)
           #$values[$time] = [ $time*1000, intval($e['kw']) ];
            $values[$time] = [ $time*1000, max(0, intval($e['kw'])) ];
        };

        return $values;
    }
}
