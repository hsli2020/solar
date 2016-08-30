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
        $prj = $device['prj'];
        $dev = $device['dev'];
        $devices["$prj-$dev"] = $device;
    }

    return $devices;
}

function getTableColumns($table)
{
    if ($table == 'solar_data_1') {
        return ['time', 'error', 'low_alarm', 'high_alarm', 'dcvolts', 'kw', 'kwh'];
    }

    if ($table == 'solar_data_2') {
        return ['time', 'error', 'low_alarm', 'high_alarm', 'kw', 'kwh_del', 'kwh_rec'];
    }

    if ($table == 'solar_data_3') {
        return ['time', 'error', 'low_alarm', 'high_alarm', 'OAT', 'PANELT', 'IRR'];
    }

    if ($table == 'solar_data_4') {
        return [
            'time', 'error', 'low_alarm', 'high_alarm',
            'total_kwh_del', 'volts_a', 'volts_b', 'volts_c',
            'current_a', 'current_b', 'current_c',
            'dc_input_voltage', 'dc_input_current',
            'line_freq', 'line_kw', 'inverter_operating_status',
            'inverter_fault_word_0', 'inverter_fault_word_1',
            'inverter_fault_word_2', 'data_comm_status'
        ];
    }

    if ($table == 'solar_data_5') {
        return ['time', 'error', 'low_alarm', 'high_alarm', 'kva', 'kwh_del', 'kwh_rec', 'vln_a', 'vln_b', 'vln_c'];
    }

    fileLog("Unknown Table Name: $table");
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
     *     [prj] => 1
     *     [dev] => mb-001
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
            if (($data = array_combine($columns, $fields)) == FALSE) {
                fileLog("DATA ERROR: $filename\n\t" . implode(', ', $fields));
                continue;
            };

            $data['dev'] = $dev;
            $data['prj'] = $project['id'];

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
     *         [prj] => 1
     *         [dev] => mb-001
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
