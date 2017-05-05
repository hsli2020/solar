<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class DataService extends Injectable
{
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
            $projectName = str_replace(' ', '_', $project->name);

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

            #echo "\033[K";  // Erase to the end of the line, DosBox doesn't support.
            echo EOL;
        }
    }
}
