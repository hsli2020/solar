<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ImportService extends Injectable
{
    public function import()
    {
        $this->log('Start importing');

        $projects = $this->projectService->getAll();

        $ftpRoot = "C:\\GCS-FTP-ROOT\\";

        $fileCount = 0;
        foreach ($projects as $project) {
            echo $project->name, EOL;

            $dir = $ftpRoot . $project->ftpdir;
            foreach (glob($dir . '/*.csv') as $filename) {
                echo "\t", $filename, EOL;

                // wait until the file is completely uploaded
                while (time() - filemtime($filename) < 10) {
                    sleep(1);
                }

                $fileCount++;

                $this->importFile($filename, $project);
                $this->backupFile($filename, $dir);
            }

            if ($project->cbdir) {
                $this->importCombiners($project, $ftpRoot . $project->cbdir);
            }
        }

        $this->log("Importing completed, $fileCount file(s) imported.\n");
    }

    protected function backupFile($filename, $ftpdir)
    {
        // move file to BACKUP folder, even it's not imported
        $dir = 'C:\\FTP-Backup\\' . basename($ftpdir);
        if (!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir);
        }

        $newfile = $dir . '\\' . basename($filename);
        rename($filename, $newfile);
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
                $data = $this->fixValues($project, $dev, $data);

                $this->insertIntoDeviceTable($project, $device, $data);

                $latest = $data;
            }
            fclose($handle);

            $this->saveLatestData($project, $device, $latest);
        }
    }

    protected function insertIntoDeviceTable($project, $device, $data)
    {
        // insert into devtab
        $devtab = $device->getDeviceTable();

        $columnList = '`' . implode('`, `', array_keys($data)) . '`';
        $values = "'" . implode("', '", $data). "'";

        $sql = "INSERT INTO $devtab ($columnList) VALUES ($values)";

        try {
            $this->db->execute($sql);
        } catch (\Exception $e) {
            echo $e->getMessage(), EOL;
        }
    }

    protected function saveLatestData($project, $device, $data)
    {
        if (empty($data)) {
            return;
        }

        $id = $project->id;
        $name = addslashes($project->name);
        $time = $data['time'];
        $devtype = $device->type;
        $devcode = $device->code;
        $json = addslashes(json_encode($data));

        $sql = "REPLACE INTO latest_data SET"
             . " project_id = $id,"
             . " project_name = '$name',"
             . " time = '$time',"
             . " devtype = '$devtype',"
             . " devcode = '$devcode',"
             . " data = '$json'";

        $this->db->execute($sql);
    }

    protected function fixValues($project, $dev, $data)
    {
        $table = $project->devices[$dev]->getTable();

        if ($table == 'table_genmeter_ion') {
            $data['vln_a'] = $data['vln_ave'];
            $data['vln_b'] = $data['vln_ave'];
            $data['vln_c'] = $data['vln_ave'];
        } else if ($table == 'table_genmeter_ion_tcp') {
            $data['vln_a'] = $data['vln'];
            $data['vln_b'] = $data['vln'];
            $data['vln_c'] = $data['vln'];
        }

        return $data;
    }

    protected function importCombiners($project, $dir)
    {
        foreach (glob($dir . '/*.csv') as $filename) {
            echo "\t", $filename, EOL;

            // filename: c:\FTP-Backup\125Bermondsey_001EC6053434\mb-001.57BEE4B7_1.log.csv
            $parts = explode('.', basename($filename));
            $dev  = $parts[0]; // mb-001
            $hash = $parts[1]; // 57BEE4B7_1

            if (!isset($project->devices[$dev])) {
               #$this->log("Invalid Filename: $filename");
                continue;
            }

            $device = $project->devices[$dev];
            $table = $device->getDeviceTable();
            $columns = $device->getTableColumns();

            #$columns = [ 'time', 'error', 'low_alarm', 'high_alarm',
            #    'CB_1',  'CB_2',  'CB_3',  'CB_4',  'CB_5',  'CB_6',  'CB_7',  'CB_8',  'CB_9',
            #    'CB_10', 'CB_11', 'CB_12', 'CB_13', 'CB_14', 'CB_15', 'CB_16', 'CB_17', 'CB_18',
            #    'CB_19', 'CB_20', 'CB_21', 'CB_22' ];

            $columnList = '`' . implode('`, `', $columns) . '`';

            if (($handle = fopen($filename, "r")) !== FALSE) {
                fgetcsv($handle); // skip first line

                while (($fields = fgetcsv($handle)) !== FALSE) {
                    if (count($columns) != count($fields)) {
                        $this->log("DATA ERROR: $filename\n\t" . implode(', ', $fields));
                        continue;
                    };

                   #$data = array_combine($columns, $fields);
                   #$values = "'" . implode("', '", $data). "'";

                    $values = "'" . implode("', '", $fields). "'";

                    $sql = "INSERT INTO $table ($columnList) VALUES ($values)";

                    try {
                        $this->db->execute($sql);
                    } catch (\Exception $e) {
                        echo $e->getMessage(), EOL;
                    }
                }

                fclose($handle);

                $this->backupFile($filename, $dir);
            }
        }
    }

    public function importCameraPicture()
    {
        $sql = "SELECT * FROM project_camera";
        $cameras = $this->db->fetchAll($sql);

        $rootDir = 'c:/GCS-FTP-ROOT';

        foreach ($cameras as $camera) {
            $dir = $camera['camera_dir'];
            $prj = $camera['project_id'];
            $cam = $camera['id']; // camera id

            # The picture filename looks like
            # c:/GCS-FTP-ROOT/45Progress-Camera1/14MPIX40_124440/2018-12-10/001/jpg/14/56/16[R][0@0][0].jpg

            $folder = $rootDir .'/'. $dir .'/'. date('Y-m-d'); // only today's files (for better performance)
            if (!file_exists($folder)) {
                #$folder = $rootDir .'/'. $dir; // all files
                continue;
            }

            $this->importPicturesInFolder($rootDir, $folder, $prj, $cam);
        }
    }

    public function importPicturesInFolder($root, $folder, $prj, $camera)
    {
        foreach (new \DirectoryIterator($folder) as $fileInfo) {
            if (!$fileInfo->isDot()) {
                if ($fileInfo->isDir()) {
                    $this->importPicturesInFolder($root, $fileInfo->getPathname(), $prj, $camera);
                } else {
                    if (strtolower($fileInfo->getExtension()) != 'jpg') {
                        continue;
                    }

                    $fullpath = str_replace('\\', '/', $fileInfo->getPathname());
                    $filename = substr($fullpath, strlen($root)+1); // no root dir

                    $sql = "SELECT * FROM camera_picture WHERE filename='$filename'";
                    $pic = $this->db->fetchOne($sql);
                    if ($pic) {
                        continue;
                    }

                    $this->log($filename);

                    try {
                        $this->db->insertAsDict('camera_picture', [
                            'project_id' => $prj,
                            'camera_id'  => $camera,
                            'filename'   => $filename,
                            'createdon'  => date('Y-m-d H:i:s', filemtime($fullpath)),
                        ]);
                    } catch (\Exception $e) {
                        echo $e->getMessage(), EOL;
                    }
                }
            }
        }
    }

    protected function log($str)
    {
        $filename = BASE_DIR . '/app/logs/import.log';

        if (file_exists($filename) && filesize($filename) > 512*1024) {
            unlink($filename);
        }

        $str = date('Y-m-d H:i:s ') . $str . "\n";

        echo $str;
        error_log($str, 3, $filename);
    }
}
