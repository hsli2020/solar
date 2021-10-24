<?php

namespace App\System;

class Combiner extends Device
{
    public function getInverter()
    {
        return $this->reference;
    }

    public function load($limit = 100)
    {
        $table = $this->getDeviceTable();

        $sql = "SELECT * FROM $table ORDER BY time DESC LIMIT $limit";

        $result = $this->getDb()->fetchAll($sql);
        return $result;
    }

    // Overwrite Device::getLatestData()
    public function getLatestData()
    {
        $projectId = $this->project->id;
        $devcode   = $this->code;

        $sql = "SELECT * FROM combiner_channel WHERE project_id=$projectId AND devcode='$devcode'";
        $channels = $this->getDb()->fetchOne($sql);

        $data = parent::getLatestData();

        $result = [];
        for ($i=1; $i<=8; $i++) {
            $input = [];
            $input['chn'] = $channels["input_$i"]; // Channel #
            $input['cur'] = $data["input_$i"];
            $input['ave'] = $data["input_$i"."_ave"];
            $input['min'] = $data["input_$i"."_min"];
            $input['max'] = $data["input_$i"."_max"];

            $result["input-$i"] = $input;
        }

        return $result;
    }
}
