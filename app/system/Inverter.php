<?php

namespace App\System;

class Inverter extends Device
{
    public function getKW($period)
    {
        $projectId = $this->project->id;
        $table = $this->table;
        $code = $this->code;

       #$column = ($this->model == 'SERIAL') ?  'line_kw' : 'kw';
        $column = ($projectId == 2) ? 'line_kw' : 'kw';

        list($start, $end) = $this->getPeriod($period);

        $sql = "SELECT SUM($column) kw FROM $table ".
                "WHERE project_id=$projectId AND devcode='$code' AND ".
                      "time>='$start' AND time<'$end' AND error=0";

        $result = $this->getDb()->fetchOne($sql);
        if ($result) {
            return $result['kw'];
           #return $result['kw'] / 60.0; // to kwH
        }

        return 0;
    }

    public function getLatestKW()
    {
        $data = $this->getLatestData();
        if ($data) {
            if (isset($data['kw'])) {
                return $data['kw'];
            }
            if (isset($data['line_kw'])) {
                return $data['line_kw'];
            }
        }
        return false;
    }

    public function getSnapshotKW()
    {
        $data = $this->getSnapshotData();
        if ($data) {
            if (isset($data['kw'])) {
                return $data['kw'];
            }
            if (isset($data['line_kw'])) {
                return $data['line_kw'];
            }
        }
        return false;
    }
}
