<?php

namespace App\System;

class Inverter extends Device
{
    public function getKW($period)
    {
        $projectId = $this->project->id;
        $table = $this->table;
        $code = $this->code;

        $column = ($this->model == 'SERIAL') ?  'line_kw' : 'kw';

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT sum($column) kw FROM $table ".
                "WHERE project_id=$projectId AND devcode='$code' AND ".
                      "time>='$start' AND time<'$end' AND error=0";

        $result = $this->db->fetchOne($sql);
        if ($result) {
            return $result['kw'] / 60.0; // to kwH
        }

        return 0;
    }

    public function getLatestKW($period)
    {
    }
}
