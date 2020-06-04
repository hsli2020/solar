<?php

namespace App\System;

class GenMeter extends Device
{
    public function getKWH($period, $f = 'rec')
    {
        $table = $this->getDeviceTable();

        $column = "kwh_$f";

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT $column AS kwh FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne("$sql ORDER BY time");
        $first = $result['kwh'];

        $result = $this->getDb()->fetchOne("$sql ORDER BY time DESC");
        $last = $result['kwh'];

        $val = $last - $first;

        if ($this->project->id == 7) {
            // Norfolk kwh_del reset after reach 10,000,000
            if ($val < -5000000) {
                $val += 10000000;
            }
        } else {
            // Alfred & Bruining kwh_del reset after reach 1,000,000,000
            if ($val < 0) {
                $val += 1000000000;
            }
        }

        return $val;
    }

    public function getKVA($period)
    {
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT SUM(kva) AS kva FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['kva'];
        }

        return 0;
    }

    public function getAvgKVA($period)
    {
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT AVG(kva) AS kva FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['kva'];
        }

        return 0;
    }

    public function getLatestKVA()
    {
        $data = $this->getLatestData();
        if ($data) {
            return $data['kva'];
        }
        return false;
    }

    public function getSnapshotKVA()
    {
        return $this->getLatestKVA();
        /*
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod('SNAPSHOT');

        $sql = "SELECT AVG(kva) AS kva".
               "  FROM $table".
               " WHERE time>='$start' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['kva'];
        }

        return 0;
        */
    }

    public function getChartData($date)
    {
        $table = $this->getDeviceTable();

        $start = $date . ' 00:00:00';
        $end   = $date . ' 23:59:59';

        $sql = "SELECT time,
                       ROUND(AVG(KVA)) AS kva
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
            $values[$time] = [ $time*1000, intval($e['kva']) ];
        };

        return $values;
    }

    public function export($interval, $start, $end)
    {
        $table = $this->getDeviceTable();

        $fmt = ($interval == 24*60) ? '%Y-%m-%d' : '%Y-%m-%d %H:%i';

        $sql = "SELECT DATE_FORMAT(CONVERT_TZ(time, 'UTC', 'America/Toronto'), '$fmt') AS time, kva, kwh_del, kwh_rec FROM $table WHERE time>='$start' AND time<'$end' AND error=0";

        if ($interval > 5) {
            $kva = "ROUND(AVG(kva)) AS kva";
            if ($interval > 60) { // daily
                $kva = "ROUND(SUM(kva)/12) AS kva";
            }
            $seconds = $interval*60; // convert to seconds
            $sql = "SELECT DATE_FORMAT(CONVERT_TZ(time, 'UTC', 'America/Toronto'), '$fmt') AS time,
                           $kva,
                           ROUND(SUM(kwh_del)) AS kwh_del,
                           ROUND(SUM(kwh_rec)) AS kwh_rec
                      FROM $table
                     WHERE time >= CONVERT_TZ('$start', 'America/Toronto', 'UTC') AND
                           time <  CONVERT_TZ('$end',   'America/Toronto', 'UTC') AND error=0
                     GROUP BY UNIX_TIMESTAMP(time) DIV $seconds";
        }

        $data = $this->getDb()->fetchAll($sql);
        return array_column($data, null, 'time');
    }

    public function getDataToCompare($startTime, $endTime, $interval, $col)
    {
        $table = $this->getDeviceTable();

        if ($interval > 0) {
            $seconds = $interval*60; // convert to seconds
            $sql = "SELECT CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time,
                           ROUND(AVG($col)) AS kwh
                      FROM $table
                     WHERE time >= CONVERT_TZ('$startTime', 'America/Toronto', 'UTC') AND
                           time <  CONVERT_TZ('$endTime',   'America/Toronto', 'UTC') AND error=0
                     GROUP BY UNIX_TIMESTAMP(time) DIV $seconds";
            $data = $this->getDb()->fetchAll($sql);
            return array_column($data, 'kwh', 'time');
        }

        return [];
    }
}
