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

        $today = date('Y-m-d');

        $sql = "SELECT time, ROUND(AVG(IRR)) AS irr FROM $table ".
                "WHERE time > '$today' AND error = 0 ".
                "GROUP BY UNIX_TIMESTAMP(time) DIV 300";

        $result = $this->getDb()->fetchAll($sql);

        $values = array_map(function($e) { return [$e['time'], $e['irr']]; }, $result);

        return $values;
    }
}
