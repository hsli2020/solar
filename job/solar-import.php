<?php

function getDatabaseConnection()
{
    // Create a connection with PDO options
    $db = new \Phalcon\Db\Adapter\Pdo\Mysql(
        array(
            "host"     => "localhost",
            "username" => "root",
            "password" => "",
            "dbname"   => "solar",
            "options"  => array()
        )
    );
    return $db;
}

function getSolarProjects($db)
{
    $projects = [];

    $result = $db->query('SELECT * FROM solar_project WHERE active=1');
    while ($project = $result->fetch(Phalcon\Db::FETCH_ASSOC)) {
        $projects[] = $project;
    }

    return $projects;
}

function getSolarDevices($db)
{
    $devices = [];

    $result = $db->query('SELECT * FROM solar_device');
    while ($device = $result->fetch(Phalcon\Db::FETCH_ASSOC)) {
        $prj = $device['project_id'];
        $dev = $device['devcode'];
        $devices["$prj-$dev"] = $device;
    }

    return $devices;
}

function getTableColumns($table)
{
    if (file_exists(__DIR__ . "/config/$table.php")) {
        $columns = include(__DIR__ . "/config/$table.php");
        return $columns;
    }

    fileLog("Unknown Table Name: $table");
    return [];
}

function importSolarFile($filename, $project, $devices, $db)
{
    // filename: c:\FTP-Backup\125Bermondsey_001EC6053434\mb-001.57BEE4B7_1.log.csv
    $parts = explode('.', basename($filename));
    $dev = $parts[0]; // mb-001
    $hash = $parts[1]; // 57BEE4B7_1

    /**
     * [1-mb-001] => Array
     * (
     *     [project_id] => 1
     *     [devcode] => mb-001
     *     [name] => Inverter
     *     [table] => solar_data_1
     * )
     */
    $prj = $project['id'];
    if (!isset($devices["$prj-$dev"])) {
        fileLog("Invalid Filename: $filename");
        return;
    }

    $device = $devices["$prj-$dev"];
    $table = $device['table'];
    $columns = getTableColumns($table);

    if (($handle = fopen($filename, "r")) !== FALSE) {
        fgetcsv($handle); // skip first line
        while (($fields = fgetcsv($handle)) !== FALSE) {
            if (count($columns) != count($fields)) {
                fileLog("DATA ERROR: $filename\n\t" . implode(', ', $fields));
                continue;
            };

            $data = array_combine($columns, $fields);

            $data['devcode'] = $dev;
            $data['project_id'] = $project['id'];

            $columnList = '`' . implode('`, `', array_keys($data)) . '`';
            $values = "'" . implode("', '", $data). "'";

            $sql = "INSERT INTO $table ($columnList) VALUES ($values)";
            $db->execute($sql);
        }
        fclose($handle);
    }

    $dir = 'C:\\FTP-Backup\\' . basename($project['ftpdir']);
    @mkdir($dir);

    $newfile = $dir . '\\' . basename($filename);
    rename($filename, $newfile);
}

function fileLog($str)
{
    $filename = __DIR__ . '/../app/logs/import.log';
    $str = date('Y-m-d H:i:s ') . $str . "\n";

    echo $str;
    error_log($str, 3, $filename);
}

function importSolarData()
{
    date_default_timezone_set("America/Toronto");

    fileLog('Start importing');

    $db = getDatabaseConnection();

    /**
     * Array
     * (
     *     [0] => Array
     *     (
     *         [id] => 1
     *         [name] => 125Bermondsey
     *         [ftpdir] => C:\FTP-Root\125Bermondsey_001EC6053434
     *     )
     *     [1] => Array (...)
     *     [2] => Array (...)
     * )
     */
    $projects = getSolarProjects($db);

    /**
     * Array
     * (
     *     [1-mb-001] => Array
     *     (
     *         [project_id] => 1
     *         [devcode] => mb-001
     *         [name] => Inverter
     *         [table] => solar_data_1
     *     )
     *     [mb-002] => Array (...)
     *     [mb-003] => Array (...)
     *     [mb-100] => Array (...)
     * )
     */
    $devices = getSolarDevices($db);

    $fileCount = 0;
    foreach ($projects as $project) {
        $dir = $project['ftpdir'];
        foreach (glob($dir . '/*.csv') as $filename) {
            $fileCount++;
            importSolarFile($filename, $project, $devices, $db);
        }
    }

    fileLog("Importing completed, $fileCount file(s) imported.\n");
}

importSolarData();
