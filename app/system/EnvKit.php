<?php

namespace App\System;

class EnvKit extends Device
{
    public function getIRR($period)
    {
        $projectId = $this->project->getId();
        $table = $this->table;
        $code = $this->code;

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT SUM(IRR) IRR FROM $table ".
                "WHERE project_id=$projectId AND devcode='$code' AND ".
                      "time>='$start' AND time<'$end' AND error=0";

        $result = $this->fetchOne($sql);
        if ($result) {
            return $result['IRR'] / 60.0 / 1000.0;
        }

        return 0;
    }

    public function getOAT($period)
    {
        list($start, $end) = $this->getPeriod($period);
    }
}
