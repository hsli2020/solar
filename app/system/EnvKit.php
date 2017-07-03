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
        if ($data) {
            return $data['IRR'];
        }
        return false;
    }

    public function getOAT($period)
    {
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT SUM(OAT) AS tmp FROM $table ".
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

        $sql = "SELECT SUM(PANELT) AS tmp FROM $table ".
                "WHERE time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['tmp'];
        }

        return 0;
    }

    public function getChartData()
    {
        $table = $this->getDeviceTable();

        $today = gmdate("Y-m-d H:i:s", mktime(0, 0, 0));

        $sql = "SELECT time, ROUND(AVG(IRR)) AS irr FROM $table".
               " WHERE time > '$today' AND error = 0".
               " GROUP BY UNIX_TIMESTAMP(time) DIV 300";

        $result = $this->getDb()->fetchAll($sql);

        $values = [];
        foreach ($result as $e) {
            $time = strtotime($e['time'].' UTC');
            $values[$time] = [ $time*1000, intval($e['irr']) ];
        };

        $full = $values + $this->getEmptyData();
        ksort($full);
        return array_values($full);
    }

    public function getEmptyData()
    {
        $values = [];

        $start = mktime(0, 0, 0);
        for ($i = 0; $i < 24*3600/300; $i++) {
            $time = $start + $i*300;
            $values[$time] = [ $time*1000, 0.0 ];
        }

        return $values;
    }
}
