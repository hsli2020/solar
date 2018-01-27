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

        return $last - $first;
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

        $sql = "SELECT CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time,
                       ROUND(AVG(KVA)) AS kva
                  FROM $table
                 WHERE time >= CONVERT_TZ('$start', 'America/Toronto', 'UTC') AND
                       time <= CONVERT_TZ('$end',   'America/Toronto', 'UTC') AND error=0
                 GROUP BY UNIX_TIMESTAMP(time) DIV 300";

        $result = $this->getDb()->fetchAll($sql);

        // utc time to local time
        $values = [];
        foreach ($result as $e) {
            $time = strtotime($e['time']);
            $values[$time] = [ $time*1000, intval($e['kva']) ];
        };

        $full = $values + $this->getEmptyData($date);
        ksort($full);
        return array_values($full);
    }

    public function getEmptyData($date)
    {
        $values = [];

        list($y, $m, $d) = explode('-', $date);
        $start = mktime(0, 0, 0, $m, $d, $y);
        for ($i = 0; $i < 24*3600/300; $i++) {
            $time = $start + $i*300;
            $values[$time] = [ $time*1000, 0.0 ];
        }

        return $values;
    }

    public function export($file, $interval, $start, $end)
    {
        $table = $this->getDeviceTable();

        $sql = "SELECT * FROM $table WHERE time>='$start' AND time<'$end' AND error=0";

        if ($interval > 1) {
            $seconds = $interval*60; // convert to seconds
            $sql = "SELECT CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time,
                           ROUND(SUM(kva))     AS kva, 
                           ROUND(SUM(kwh_del)) AS kwh_del, 
                           ROUND(SUM(kwh_rec)) AS kwh_rec
                      FROM $table
                     WHERE time >= CONVERT_TZ('$start', 'America/Toronto', 'UTC') AND
                           time <  CONVERT_TZ('$end',   'America/Toronto', 'UTC') AND error=0
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
        $title1 = [ "time(UTC)","error","lowalarm","highalarm","kva (kVA)","kwh_del (kWh)","kwh_rec (kWh)","vln_a (Volts)","vln_b (Volts)","vln_c (Volts)" ];
        $titlex = [ "time(UTC)","kva (kVA)","kwh_del (kWh)","kwh_rec (kWh)" ];

        if ($interval == 1) {
            return $title1;
        }
        return $titlex;
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
