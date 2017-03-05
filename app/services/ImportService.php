<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ImportService extends Injectable
{
    public function import()
    {
        date_default_timezone_set("America/Toronto");

        $this->log('Start importing');

        $projects = $this->projectService->getAll();
        $devices  = $this->deviceService->getAll();

        $fileCount = 0;
        foreach ($projects as $project) {
            $dir = $project['ftpdir'];
            foreach (glob($dir . '/*.csv') as $filename) {
                $fileCount++;
                $this->importFile($filename, $project, $devices);
            }
        }

        $this->log("Importing completed, $fileCount file(s) imported.\n");
    }

    protected function importFile($filename, $project, $devices)
    {
        // filename: c:\FTP-Backup\125Bermondsey_001EC6053434\mb-001.57BEE4B7_1.log.csv
        $parts = explode('.', basename($filename));
        $dev  = $parts[0]; // mb-001
        $hash = $parts[1]; // 57BEE4B7_1

        $prj = $project['id'];
        if (!isset($devices[$prj][$dev])) {
            $this->log("Invalid Filename: $filename");
            return;
        }

        $device  = $devices[$prj][$dev];
        $table   = $device['table'];
        $columns = $this->deviceService->getTableColumns($prj, $dev);

        if (($handle = fopen($filename, "r")) !== FALSE) {
            fgetcsv($handle); // skip first line
            while (($fields = fgetcsv($handle)) !== FALSE) {
                if (count($columns) != count($fields)) {
                    $this->log("DATA ERROR: $filename\n\t" . implode(', ', $fields));
                    continue;
                };

                $data = array_combine($columns, $fields);

                $data['devcode']    = $dev;
                $data['project_id'] = $project['id'];

                $columnList = '`' . implode('`, `', array_keys($data)) . '`';
                $values = "'" . implode("', '", $data). "'";

                $sql = "INSERT INTO $table ($columnList) VALUES ($values)";
                $this->db->execute($sql);
            }
            fclose($handle);
        }

        $dir = 'C:\\FTP-Backup\\' . basename($project['ftpdir']);
        @mkdir($dir);

        $newfile = $dir . '\\' . basename($filename);
        rename($filename, $newfile);
    }

    protected function log($str)
    {
        $filename = BASE_DIR . '/app/logs/import.log';
        $str = date('Y-m-d H:i:s ') . $str . "\n";

        echo $str;
        error_log($str, 3, $filename);
    }
}
