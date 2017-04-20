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

    public function archive()
    {
        $projects = $this->projectService->getAll();

        foreach ($projects as $project) {
            $files = [];

            $dir = 'C:/FTP-Backup/' . basename($project->ftpdir);
            foreach (glob($dir . '/*.log.csv') as $filename) {
                $files[$filename] = filemtime($filename);
            }
            asort($files);

            echo $project->name, ' ', count($files), ' files', EOL;

            if (count($files) == 0) {
                continue;
            }

            $handles = [];
            foreach (array_keys($files) as $filename) {
                echo "$filename\r";

                // filename: c:\FTP-Backup\125Bermondsey_001EC6053434\mb-001.57BEE4B7_1.log.csv
                $parts = explode('.', basename($filename));
                $dev = $parts[0]; // mb-001

                $isNewFile = false;
                if (isset($handles[$dev])) {
                    $dst = $handles[$dev];
                } else {
                    $archiveFilename = dirname($filename). '/' .$dev. '-archive.csv';
                    if (file_exists($archiveFilename)) {
                        $dst = fopen($archiveFilename, 'a');
                    } else {
                        $isNewFile = true;
                        $dst = fopen($archiveFilename, 'w');
                    }
                    $handles[$dev] = $dst;
                }

                $src = fopen($filename, 'r');

                $title = fgets($src);
                if ($isNewFile) {
                    fputs($dst, $title);
                }

                while ($line = fgets($src)) {
                    fputs($dst, $line);
                }
                fclose($src);

                // zip filename;
                $zipFilename = dirname($filename). '/' .$dev. '-archive.zip';

                #unlink($filename);
            }

            foreach ($handles as $handle) {
                fclose($handle);
            }

            #echo "\033[K";  // Erase to the end of the line, DosBox doesn't support.
            echo EOL;
        }
    }
}
