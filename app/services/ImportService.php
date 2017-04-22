<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ImportService extends Injectable
{
    public function import()
    {
        $this->log('Start importing');

        $projects = $this->projectService->getAll();

        $fileCount = 0;
        foreach ($projects as $project) {
            $dir = $project->ftpdir;
            foreach (glob($dir . '/*.csv') as $filename) {
                $fileCount++;
                $this->importFile($filename, $project);

                // move file to BACKUP folder, even it's not imported
                $dir = 'C:\\FTP-Backup\\' . basename($project->ftpdir);
                if (!file_exists($dir) && !is_dir($dir)) {
                    mkdir($dir);
                }

                $newfile = $dir . '\\' . basename($filename);

                $done = false;
                while (!$done) {
                    try {
                        rename($filename, $newfile);
                        $done = true;
                    } catch (\Exception $e) {
                        sleep(1);
                    }
                }
            }
        }

        $this->log("Importing completed, $fileCount file(s) imported.\n");
    }

    protected function importFile($filename, $project)
    {
        // filename: c:\FTP-Backup\125Bermondsey_001EC6053434\mb-001.57BEE4B7_1.log.csv
        $parts = explode('.', basename($filename));
        $dev  = $parts[0]; // mb-001
        $hash = $parts[1]; // 57BEE4B7_1

        if (!isset($project->devices[$dev])) {
           #$this->log("Invalid Filename: $filename");
            return;
        }

        $device  = $project->devices[$dev];
        $table   = $device->getTable();
        $columns = $device->getTableColumns();

        if (($handle = fopen($filename, "r")) !== FALSE) {
            $latest = [];
            fgetcsv($handle); // skip first line
            while (($fields = fgetcsv($handle)) !== FALSE) {
                if (count($columns) != count($fields)) {
                    $this->log("DATA ERROR: $filename\n\t" . implode(', ', $fields));
                    continue;
                };

                $data = array_combine($columns, $fields);

                $data['devcode']    = $dev;
                $data['project_id'] = $project->id;

                $columnList = '`' . implode('`, `', array_keys($data)) . '`';
                $values = "'" . implode("', '", $data). "'";

                $sql = "INSERT INTO $table ($columnList) VALUES ($values)";
                $this->db->execute($sql);
                $latest = $data;
            }
            fclose($handle);

            $this->saveLatestData($project, $device, $latest);
        }
    }

    protected function saveLatestData($project, $device, $data)
    {
        $id = $project->id;
        $name = addslashes($project->name);
        $time = $data['time'];
        $devtype = $device->type;
        $devcode = $device->code;
        $data = addslashes(json_encode($data));

        $sql = "REPLACE INTO latest_data SET"
             . " project_id = $id,"
             . " project_name = '$name',"
             . " time = '$time',"
             . " devtype = '$devtype',"
             . " devcode = '$devcode',"
             . " data = '$data'";

        $this->db->execute($sql);
    }

    protected function log($str)
    {
        $filename = BASE_DIR . '/app/logs/import.log';
        $str = date('Y-m-d H:i:s ') . $str . "\n";

        echo $str;
        error_log($str, 3, $filename);
    }
}
