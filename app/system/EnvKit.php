<?php

namespace App\System;

class EnvKit extends Device
{
    public function getIRR($period)
    {
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT SUM(IRR) AS irr FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['irr'];
           #return $result['irr'] / 60.0 / 1000.0;
        }

        return 0;
    }

    public function getAvgIRR($period)
    {
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT AVG(IRR) AS irr FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['irr'];
           #return $result['irr'] / 60.0 / 1000.0;
        }

        return 0;
    }

    public function getLatestIRR()
    {
        $data = $this->getLatestData();
        if ($data) {
            return $data['IRR'];
        }
        return false;
    }

    public function getSnapshotIRR()
    {
        $data = $this->getSnapshotData();
        return $data['IRR'];
        /*
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod('SNAPSHOT');

        $sql = "SELECT AVG(IRR) AS irr".
               "  FROM $table".
               " WHERE time>='$start' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['irr'];
           #return $result['irr'] / 60.0 / 1000.0;
        }

        return 0;
        */
    }

    public function getSnapshotOAT()
    {
        $data = $this->getSnapshotData();
        return $data['OAT'];
        /*
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod('SNAPSHOT');

        $sql = "SELECT AVG(OAT) AS oat FROM $table ".
                "WHERE time>='$start' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['oat'];
        }

        return 0;
        */
    }

    public function getOAT($period)
    {
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT AVG(OAT) AS tmp FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['tmp'];
        }

        return 0;
    }

    public function getTMP($period)
    {
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT AVG(PANELT) AS tmp FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['tmp'];
        }

        return 0;
    }

    public function getChartData($date)
    {
        $table = $this->getDeviceTable();

        $start = $date . ' 00:00:00';
        $end   = $date . ' 23:59:59';

        $sql = "SELECT CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time,
                       ROUND(AVG(IRR)) AS irr
                  FROM $table
                 WHERE time >= CONVERT_TZ('$start', 'America/Toronto', 'UTC') AND
                       time <= CONVERT_TZ('$end',   'America/Toronto', 'UTC') AND error=0
                 GROUP BY UNIX_TIMESTAMP(time) DIV 300";

        $result = $this->getDb()->fetchAll($sql);

        // utc time to local time
        $values = [];
        foreach ($result as $e) {
            $time = strtotime($e['time']);
            $values[$time] = [ $time*1000, max(0, intval($e['irr'])) ];
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

    public function getAvgTMP($period)
    {
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT AVG(PANELT) AS tmp FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['tmp'];
        }

        return 0;
    }

    public function export($file, $interval, $start, $end)
    {
        $table = $this->getDeviceTable();

        $sql = "SELECT * FROM $table WHERE time>='$start' AND time<'$end' AND error=0";

        if ($interval > 1) {
            $seconds = $interval*60; // convert to seconds
            $sql = "SELECT CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time,
                           ROUND(AVG(OAT))    AS oat,
                           ROUND(AVG(PANELT)) AS panelt,
                           ROUND(AVG(IRR))    AS irr
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
        // time(UTC),error,lowalarm,highalarm,"OAT (Degrees C)","PANELT (Degrees C)","IRR_POA_CMP (W/m^2)"

        $title1 = [ "time(UTC)","error","lowalarm","highalarm","OAT (Degrees C)","PANELT (Degrees C)","IRR (W/m^2)" ];
        $titlex = [ "time(UTC)","OAT (Degrees C)","PANELT (Degrees C)","IRR (W/m^2)" ];

        if ($interval == 1) {
            return $title1;
        }
        return $titlex;
    }

    public function getDataToCompare($startTime, $endTime, $interval)
    {
        $table = $this->getDeviceTable();

        if ($interval > 0) {
            $seconds = $interval*60; // convert to seconds
            $sql = "SELECT CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time,
                           ROUND(AVG(IRR)) AS irr
                      FROM $table
                     WHERE time >= CONVERT_TZ('$startTime', 'America/Toronto', 'UTC') AND
                           time <  CONVERT_TZ('$endTime',   'America/Toronto', 'UTC') AND error=0
                     GROUP BY UNIX_TIMESTAMP(time) DIV $seconds";
            $data = $this->getDb()->fetchAll($sql);
            return array_column($data, 'irr', 'time');
        }
        return [];
    }
}
