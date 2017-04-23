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
}
