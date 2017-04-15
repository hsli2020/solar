<?php

namespace App\System;

class EnvKit extends Device
{
    public function getIRR($period)
    {
        $projectId = $this->project->id;
        $table = $this->table;
        $code = $this->code;

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT SUM(IRR) irr FROM $table ".
                "WHERE project_id=$projectId AND devcode='$code' AND ".
                      "time>='$start' AND time<'$end' AND error=0";

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

    public function getOAT($period)
    {
        $projectId = $this->project->id;
        $table = $this->table;
        $code = $this->code;

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT SUM(OAT) tmp FROM $table ".
                "WHERE project_id=$projectId AND devcode='$code' AND ".
                      "time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['tmp'];
        }

        return 0;
    }

    public function getTMP($period)
    {
        $projectId = $this->project->id;
        $table = $this->table;
        $code = $this->code;

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT SUM(PANELT) tmp FROM $table ".
                "WHERE project_id=$projectId AND devcode='$code' AND ".
                      "time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['tmp'];
        }

        return 0;
    }
}
