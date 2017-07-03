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
        $data = $this->getSnapshotData();
        if ($data) {
            return $data['kva'];
        }
        return false;
    }

    public function getChartData()
    {
        $table = $this->getDeviceTable();

        $today = date("Y-m-d H:i:s", mktime(0, 0, 0) - date("Z"));

        $sql = "SELECT time, ROUND(AVG(KVA)) AS kva FROM $table".
               " WHERE time > '$today' AND error = 0".
               " GROUP BY UNIX_TIMESTAMP(time) DIV 300";

        $result = $this->getDb()->fetchAll($sql);

        $values = array_map(function($e) {
            return [ strtotime($e['time'].' UTC')*1000, intval($e['kva']) ];
        }, $result);

        return $values;
    }
}
