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
            return round($result['tmp'], 2);
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
            return round($result['tmp'], 2);
        }

        return 0;
    }

    public function getChartData($date)
    {
        $table = $this->getDeviceTable();

        $start = $date . ' 00:00:00';
        $end   = $date . ' 23:59:59';

        $sql = "SELECT time,
                       ROUND(AVG(IRR)) AS irr
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
            $values[$time] = [ $time*1000, max(0, intval($e['irr'])) ];
        };

        return $values;
    }

    // same as getTMP for now
    public function getAvgTMP($period)
    {
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT AVG(PANELT) AS tmp FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return round($result['tmp'], 2);
        }

        return 0;
    }

    // same as getOAT() for now
    public function getAvgOAT($period)
    {
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT AVG(OAT) AS tmp FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return round($result['tmp'], 2);
        }

        return 0;
    }

    public function export($interval, $start, $end)
    {
        $table = $this->getDeviceTable();

        $fmt = ($interval == 24*60) ? '%Y-%m-%d' : '%Y-%m-%d %H:%i';

        $sql = "SELECT DATE_FORMAT(CONVERT_TZ(time, 'UTC', 'America/Toronto'), '$fmt') AS time, oat, panelt, irr FROM $table WHERE time>='$start' AND time<'$end' AND error=0";

        if ($interval > 5) {
            $seconds = $interval*60; // convert to seconds
            $sql = "SELECT DATE_FORMAT(CONVERT_TZ(time, 'UTC', 'America/Toronto'), '$fmt') AS time,
                           ROUND(AVG(OAT))    AS oat,
                           ROUND(AVG(PANELT)) AS panelt,
                           ROUND(AVG(IRR))    AS irr
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
