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
        $project1Id = $info['project1'];
        $project2Id = $info['project2'];
        $project3Id = $info['project3'];

        $project1 = $this->projectService->get($project1Id);
        $project2 = $this->projectService->get($project2Id);
        $project3 = $this->projectService->get($project3Id);

        $data1 = $project1->getDataToCompare($startTime, $endTime, $interval);
        $data2 = $project2->getDataToCompare($startTime, $endTime, $interval);
        $data3 = $project3->getDataToCompare($startTime, $endTime, $interval);

        // $maxlen = max(count($data1), count($data2), count($data2));

        $empty = [ 'kw' => '', 'irr' => '', 'kwh' => '' ];

        $data = [];
        foreach ($data1 as $time => $row) {
            $data[$time]['project1'] = $row + $empty;

            $data[$time]['project2'] = $empty;
            if (isset($data2[$time])) {
                $data[$time]['project2'] = $data2[$time] + $empty;
            }

            $data[$time]['project3'] = $empty;
            if (isset($data3[$time])) {
                $data[$time]['project3'] = $data3[$time] + $empty;
            }
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
