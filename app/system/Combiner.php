<?php

namespace App\System;

class Combiner extends Device
{
    public function getInverter()
    {
        return $this->reference;
    }

    public function load()
    {
        $table = $this->getDeviceTable();

        $sql = "SELECT * FROM $table ORDER BY time DESC LIMIT 100";

        $result = $this->getDb()->fetchAll($sql);
        return $result;
    }
}
