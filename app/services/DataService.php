<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class DataService extends Injectable
{
    // deprecated
    public function getChartData($prj, $dev, $fld) { return false; }

    public function getDataToCompare($info)
    {
        $startTime  = $info['startTime'];
        $endTime    = $info['endTime'];
        $interval   = $info['interval'];
        $projectIds = isset($info['projects']) ? $info['projects'] : [];

        $dataSet = [];
        $timeSet = [];
        foreach ($projectIds as $projectId) {
            $project = $this->projectService->get($projectId);
            $dataSet[$projectId] = $project->getDataToCompare($startTime, $endTime, $interval);
            $timeSet = array_merge(array_keys($dataSet[$projectId]));
        }
        $timeSet = array_unique($timeSet);

        $data = [];
        foreach ($timeSet as $time) {
            foreach ($projectIds as $projectId) {
                if (isset($dataSet[$projectId][$time])) {
                    $data[$time][$projectId] = $dataSet[$projectId][$time];

                    // some of projects have no inverters
                    if (!isset($data[$time][$projectId]['kw'])) {
                        $data[$time][$projectId]['kw'] = '';
                    }

                    // all of projects have envkits, just in case
                    if (!isset($data[$time][$projectId]['irr'])) {
                        $data[$time][$projectId]['irr'] = '';
                    }

                    // all of projects have genmeters, just in case
                    if (!isset($data[$time][$projectId]['kwh'])) {
                        $data[$time][$projectId]['kwh'] = '';
                    }
                } else {
                    $data[$time][$projectId] = [ 'kw' => '', 'irr' => '', 'kwh' => '' ];
                }
            }
        }

        return $data;
    }

    public function archive()
    {
        $projects = $this->projectService->getAll();

        foreach ($projects as $project) {
            $this->archiveDir($project->ftpdir, $project);

            if ($project->cbdir) {
                $this->archiveDir($project->cbdir, $project);
            }
        }
    }

    protected function archiveDir($ftpdir, $project)
    {
        $projectName = str_replace(' ', '_', $project->name);

        $files = [];

        $dir = 'C:/FTP-Backup/' . basename($ftpdir);
        foreach (glob($dir . '/*.log.csv') as $filename) {
            $files[$filename] = filemtime($filename);
        }
        asort($files);

        echo $project->name, ' ', count($files), ' files', EOL;

        if (count($files) == 0) {
            return;
        }

        $handles = [];
        $zipfiles = [];
        foreach (array_keys($files) as $filename) {
            echo "$filename\r";

            // filename: c:\FTP-Backup\125Bermondsey_001EC6053434\mb-001.57BEE4B7_1.log.csv
            $parts = explode('.', basename($filename));
            $dev = $parts[0]; // mb-001

            $isNewFile = false;
            if (isset($handles[$dev])) {
                $dst = $handles[$dev];
            } else {
                $archiveFilename = "C:/FTP-Backup/archive/$projectName-$dev.csv";
                if (file_exists($archiveFilename)) {
                    $dst = fopen($archiveFilename, 'a');
                } else {
                    $isNewFile = true;
                    $dst = fopen($archiveFilename, 'w');
                }
                $handles[$dev] = $dst;
            }

            // Merge the file into a single big file
            $src = fopen($filename, 'r');

            $title = fgets($src);
            if ($isNewFile) {
                fputs($dst, $title);
            }

            while ($line = fgets($src)) {
                fputs($dst, $line);
            }
            fclose($src);

            // Pack the file into a zip file
            if (isset($zipfiles[$dev])) {
                $zip = $zipfiles[$dev];
            } else {
                $zipFilename = "C:/FTP-Backup/archive/$projectName-$dev.zip";

                $zip = new \ZipArchive;
                if ($zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
                    echo "Failed to open zip file $zipFilename\n";
                    continue;
                }

                $zipfiles[$dev] = $zip;
            }

            $zip->addFile($filename, basename($filename));
        }

        foreach ($handles as $handle) {
            fclose($handle);
        }

        foreach ($zipfiles as $zip) {
            $zip->close();
        }

        // delete files after zip files created and closed
        foreach (array_keys($files) as $filename) {
            unlink($filename);
        }

        echo EOL;
    }

    public function fakeInverterData()
    {
        $fmt = "INSERT INTO %s (time, error, low_alarm, high_alarm, kw, status, fault_code, vln_a, vln_b, vln_c)
                SELECT time, error, low_alarm, high_alarm, GREATEST(0, kva-%d), 0, 0, vln_a, vln_b, vln_c
                FROM %s ORDER BY time DESC LIMIT 10";

        $projects = $this->projectService->getAll();

        $map = $this->db->fetchAll("SELECT * FROM fake_inverter");
        foreach ($map as $info) {
            $projectId = $info['project_id'];
            $inverterCode = $info['inverter']; // mb-f01
            $genMeterCode = $info['genmeter']; // mb-011

            $project = $projects[$projectId];

            $offset = $project->offset;
            $devices = $project->devices;

            $fakeInverter = $devices[$inverterCode];
            $genMeter = $devices[$genMeterCode];

            $genMeterTable = $genMeter->getDeviceTable();
            $fakeInverterTable = $fakeInverter->getDeviceTable();

            echo 'Generating data for ', $fakeInverter, EOL;

            try {
                $sql = sprintf($fmt, $fakeInverterTable, $offset, $genMeterTable);
                $this->db->execute($sql);
            } catch (\Exception $e) {
                echo $e->getMessage(), EOL;
            }

            $this->fakeLatestData($projectId, $inverterCode, $genMeterCode, $offset);
        }
    }

    public function fakeLatestData($projectId, $inverterCode, $genMeterCode, $offset)
    {
        $row = $this->db->fetchOne("SELECT * FROM latest_data WHERE project_id=$projectId AND devcode='$genMeterCode'");
        if ($row) {
            $name = $row['project_name'];
            $time = $row['time'];
            $devtype = 'Inverter';
            $devcode = $inverterCode;
            $data = json_decode($row['data'], true);
            /**
             * `time`
             * `error`
             * `low_alarm`
             * `high_alarm`
             * `kva`
             * `kwh_del`
             * `kwh_rec`
             * `vln_a`
             * `vln_b`
             * `vln_c`
             */
            $data['status'] = 0;
            $data['fault_code'] = 0;
            $data['kw'] = max(0, $data['kva'] - $offset);

            unset($data['kva']);
            unset($data['kwh_del']);
            unset($data['kwh_rec']);

            $json = json_encode($data);

            $sql = "REPLACE INTO latest_data SET"
                 . " project_id = $projectId,"
                 . " project_name = '$name',"
                 . " time = '$time',"
                 . " devtype = '$devtype',"
                 . " devcode = '$devcode',"
                 . " data = '$json'";

            $this->db->execute($sql);
        }
    }

    public function fakeEnvkitData()
    {
        $sql = "INSERT IGNORE INTO p36_mb_x71_envkit
                SELECT * FROM p35_mb_071_envkit WHERE time>(SELECT MAX(time) FROM p36_mb_x71_envkit)";
        $this->db->execute($sql);

        // latest data
        $sql = "SELECT * FROM p35_mb_071_envkit WHERE time=(SELECT MAX(time) FROM p36_mb_x71_envkit)";
        $row = $this->db->fetchOne($sql);

        $projectId = 36;
        $name = '400 Glen Hill';
        $time = $row['time'];
        $devtype = 'EnvKit';
        $devcode = 'mb-x71';
        $json = json_encode($row);

        $sql = "REPLACE INTO latest_data SET"
             . " project_id = $projectId,"
             . " project_name = '$name',"
             . " time = '$time',"
             . " devtype = '$devtype',"
             . " devcode = '$devcode',"
             . " data = '$json'";

        $this->db->execute($sql);
    }

    public function loadBudget($prj)
    {
        $sql = "SELECT * FROM monthly_budget WHERE project_id=$prj";
        $rows = $this->db->fetchAll($sql);
        return $rows;
    }

    // CRH data
    public function getCrhData($prj, $date)
    {
        // return an array in the following format
        // [
        //    HOUR => [ HOUR, BASELINE, LOAD ]
        //    HOUR => [ HOUR, BASELINE, LOAD ]
        // ]

        $result = $this->getStdBaseline($prj, $date);
        $result = $this->getActualLoad($prj, $date, $result);

        return $result;
    }

    // CRH Standard Baseline
    protected function getStdBaseline($prj, $date)
    {
        $start = date('Y-m-d', strtotime('-35 day'));
        $sql = "SELECT time AS time_utc,
                       CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time_edt,
                       CONVERT_TZ(time, 'UTC', 'EST') AS time_est,
                       kva AS kw
                  FROM p{$prj}_mb_001_genmeter
                HAVING time_edt>='$start' AND time_edt<'$date'
              ORDER BY time DESC";
        $data = $this->db->fetchAll($sql);

        $season = getSeason($date);

        $daily = [];
        foreach ($data as $rec) {
            $time = $rec['time_edt'];
            $kwh = $rec['kw'];

            $dt = substr($time, 0, 10);
            $hr = substr($time, 11, 2);

            if (isWeekend($dt) || isHoliday($dt) || isMaintenance($dt)) {
                continue;
            }

            if (getSeason($dt) != $season) {
                break; // shouldn't cross seasons (SUMMER/WINTER)
            }

            if (isset($daily[$dt])) {
                $daily[$dt]['total'] += $kwh;
            } else {
                $daily[$dt]['total'] = $kwh;
            }

            if (isset($daily[$dt]['hourly'][$hr])) {
                $daily[$dt]['hourly'][$hr]['sum'] += $kwh;
                $daily[$dt]['hourly'][$hr]['cnt'] += 1;
            } else {
                $daily[$dt]['hourly'][$hr]['sum'] = $kwh;
                $daily[$dt]['hourly'][$hr]['cnt'] = 1;
            }

            if (count($daily) == 20+1) {
                array_pop($daily);
                break;
            }
        }

        uasort($daily, function($a, $b) {
            if ($a['total'] == $b['total']) { return 0; }
            return ($a['total'] < $b['total']) ? 1 : -1;
        });

        $top15 = array_slice($daily, 0, 15);

        $hourly = [];
        foreach ($top15 as $day) {
            foreach ($day['hourly'] as $hour => $rec) {
                if (isset($hourly[$hour])) {
                    $hourly[$hour]['sum'] += $rec['sum'];
                    $hourly[$hour]['cnt'] += $rec['cnt'];
                } else {
                    $hourly[$hour]['sum'] = $rec['sum'];
                    $hourly[$hour]['cnt'] = 1;
                }
            }
        }

        foreach ($hourly as $hour => $rec) {
            $avg = round($rec['sum']/$rec['cnt']);
            $hourly[$hour]['avg'] = $avg;
        }

        // Baseline (Avg)
        $result = [];
        foreach ($hourly as $hour => $rec) {
            $h = intval($hour); // chart requires number, not string
            $avg = $rec['avg'];
            $result[$h] = [ $h, $avg, null ];
        }

        ksort($result); // sort by hour
        return $result;
    }

    // CRH Actual Load
    protected function getActualLoad($prj, $date, $result)
    {
        $sql = "SELECT time AS time_utc,
                       CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time_edt,
                       CONVERT_TZ(time, 'UTC', 'EST') AS time_est,
                       kva AS kw
                  FROM p{$prj}_mb_001_genmeter
                HAVING DATE(time_edt)='$date'";

        $data = $this->db->fetchAll($sql);

        $hourly = [];
        foreach ($data as $rec) {
            $time = $rec['time_edt'];
            $kwh = $rec['kw'];

            $dt = substr($time, 0, 10);
            $hr = substr($time, 11, 2);

            if (isset($hourly[$hr])) {
                $hourly[$hr]['sum'] += $kwh;
                $hourly[$hr]['cnt'] += 1;
            } else {
                $hourly[$hr]['sum'] = $kwh;
                $hourly[$hr]['cnt'] = 1;
            }
        }

        foreach ($hourly as $hour => $rec) {
            $h = intval($hour); // chart requires number, not string
            $result[$h][2] = intval($rec['sum']/$rec['cnt']);
        }

        return $result;
    }
}
