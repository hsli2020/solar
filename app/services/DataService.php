<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class DataService extends Injectable
{
    public function getSnapshot()
    {
        $data = [];

        list($start, $end) = $this->getPeriod('SNAPSHOT');

        $projects = $this->projectService->getAll();
        foreach ($projects as $projectId => $project) {
            foreach ($project->devices as $device) {
               #$projectId = $device->projectId;
                $devcode = $device->code;
                $devtype = $device->type;

                $data[$projectId]['name'] = $project->name;

                $criteria = [
                    "conditions" => "projectId=:project: AND devcode=:devcode: AND time >= :start: AND time < :end: AND error=0",
                    "bind"       => ['project' => $projectId, 'devcode' => $devcode, 'start' => $start, 'end' => $end],
                    "order"      => "id DESC",
                    "limit"      => 1
                ];

                $modelClass = $device->getClassName($devcode);

                $row = $modelClass::findFirst($criteria);
                if ($row) {
                    $row->time = substr($row->time, 0, -3);

                    if ($devtype == 'Inverter') {
                        $data[$projectId][$devtype][] = $row->toArray();
                    } else {
                        $data[$projectId][$devtype] = $row->toArray();
                    }
                }
            }
        }

        return $data;
    }

    public function getChartData($prj, $dev, $fld)
    {
        $table = $this->deviceService->getTable($prj, $dev);

        $sql = "(SELECT `time`, $fld FROM $table WHERE error=0 ORDER BY `time` DESC LIMIT 300) ORDER BY `time` ASC";
        $result = $this->db->query($sql);

        $data = [];
        while ($row = $result->fetch()) {
            $row['time'] = toLocalTime($row['time']);
            $data[] = [strtotime($row['time'])*1000, floatval($row[$fld])];
        }

        return $data;
    }
}
