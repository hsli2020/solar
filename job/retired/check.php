<?php

const EOL = "\n";

function getDatabaseConnection()
{
    $db = new \Phalcon\Db\Adapter\Pdo\Mysql([
        "host"     => "localhost",
        "username" => "root",
        "password" => "",
        "dbname"   => "solar",
        "options"  => array()
    ]);

    return $db;
}

function fpr($var)
{
    $filename = __DIR__ . '/debug.log';

    $str = print_r($var, true) . "\n";
    echo $str;

    error_log($str, 3, $filename);
}

function getPeriod($period)
{
    switch (strtoupper($period)) {
    case 'HOURLY':
    case 'LAST-HOUR':
        // last hour
        $start = gmdate('Y-m-d H:00:00', strtotime('-1 hours'));
        $end   = gmdate('Y-m-d H:00:00');
        break;

    case 'DAILY':
    case 'YESTERDAY':
        // yesterday
        $yesterday = strtotime('-1 day');
        $start = gmdate('Y-m-d 00:00:00', $yesterday);
        $end   = gmdate('Y-m-d 23:59:59', $yesterday);
        break;

    case 'MONTH-TO-DATE':
        // month-to-date
        $start = gmdate('Y-m-01 00:00:00');
        $end   = gmdate('Y-m-d 00:00:00');

        // first day of the month, go back to last month
        if (date('d') == '01') {
            $start = gmdate('Y-m-01 00:00:00', strtotime('-1 month'));  // first day of last month
            #$end  = gmdate('Y-m-d 00:00:00',  strtotime('-1 day'));
            $end   = gmdate('Y-m-01 00:00:00');     // first day of current month
        }
        break;

    case 'LAST-MONTH':
        // last-month
        $start = gmdate('Y-m-01 00:00:00', strtotime('-1 month'));  // first day of last month
        #$end  = gmdate('Y-m-t 23:59:59',  strtotime('-1 month'));
        $end   = gmdate('Y-m-01 00:00:00');     // first day of current month
        break;

    case 'LATEST':
        // last minute (15 minutes ago)
        $start = gmdate('Y-m-d H:i:00', strtotime('-15 minute'));
        $end   = gmdate('Y-m-d H:i:30', strtotime('-14 minute'));
        break;

    default:
        throw new InvalidArgumentException("Bad argument '$period'");
        break;
    }

    return [ $start, $end ];
}

function getDevices($db)
{
    static $devices = [];

    if ($devices) {
        return $devices;
    }

    $list = $db->fetchAll('SELECT * FROM solar_device');

    foreach ($list as $dev) {
        $project = $dev['project_id'];
        $devcode = $dev['devcode'];
        $type    = $dev['type'];
        $table   = $dev['table'];

        $devices[$project][$type][] = [
            'devcode' => $devcode,
            'table'   => $table,
        ];
    }

    //fpr($devices);
    return $devices;
}

function getInverters($db, $project)
{
    $devices = getDevices($db);
    return $devices[$project]['Inverter'];
}

function getEnvKits($db, $project)
{
    $devices = getDevices($db);
    return $devices[$project]['EnvKit'];
}

function getGenMeters($db, $project)
{
    $devices = getDevices($db);
    return $devices[$project]['GenMeter'];
}

function getKW($db, $project, $period)
{
    $result = 0;

    $col = $project == 2 ? 'line_kw' : 'kw';

    list($start, $end) = getPeriod($period);

    $devices = getInverters($db, $project);

    foreach ($devices as $dev) {
        $devcode = $dev['devcode'];
        $table = $dev['table'];
        $sql = "SELECT sum($col) KW FROM $table ".
                "WHERE project_id=$project AND devcode='$devcode' AND ".
                      "time>='$start' AND time<'$end' AND error=0";
#       echo $sql, EOL;
        $res = $db->fetchColumn($sql, 'KW');
        $result += $res;
    }

    return round($result / 60.0, 2);
}

function getIRR($db, $project, $period)
{
    $result = 0;

    list($start, $end) = getPeriod($period);

    $devices = getEnvKits($db, $project);

    foreach ($devices as $dev) {
        $devcode = $dev['devcode'];
        $table = $dev['table'];
        $sql = "SELECT sum(IRR) IRR FROM $table ".
                "WHERE project_id=$project AND devcode='$devcode' AND ".
                      "time>='$start' AND time<'$end' AND error=0";
#       echo $sql, EOL;
        $res = $db->fetchColumn($sql, 'IRR');

        $result += $res;
    }

    return round($result / 60.0 / 1000.0, 2);
}

function getGM($db, $project, $period)
{
    $result = 0;

    list($start, $end) = getPeriod($period);

    $devices = getGenMeters($db, $project);

    foreach ($devices as $dev) {
        $devcode = $dev['devcode'];
        $table = $dev['table'];

        $sql = "SELECT kwh_rec FROM $table ".
                "WHERE project_id=$project AND devcode='$devcode' AND ".
                      "time>='$start' AND time<'$end' AND error=0";

        $res = $db->fetchOne("$sql ORDER BY time");
        $first = $res['kwh_rec'];

        $res = $db->fetchOne("$sql ORDER BY time DESC");
        $last = $res['kwh_rec'];
    }

    return round($last - $first, 2);
}

// main
date_default_timezone_set("America/Toronto");

$prj = 1;
$period = 'LAST-MONTH';

if (count($argv) > 2) {
    $prj = $argv[1];
    $period = strtoupper($argv[2]);
}

$db = getDatabaseConnection();

$KW  = getKW($db,  $prj, $period);
$IRR = getIRR($db, $prj, $period);
$GM  = getGM($db,  $prj, $period);

echo 'PRJ = ', $prj, EOL;
echo 'KW  = ', $KW,  EOL;
echo 'IRR = ', $IRR, EOL;
echo 'GM  = ', $GM,  EOL;

