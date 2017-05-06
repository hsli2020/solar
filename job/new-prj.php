<?php

const EOL = "\n";

$dir = '125Bermondsey_001EC6053434'; //$argv[1];

$ftpdir = "C:\\\\GCS-FTP-ROOT\\\\$dir";
$ftpdir = "C:\\\\FTP-Backup\\\\$dir";

$name = substr($dir, 0, strpos($dir, '_'));

$projectId = 29; // $argv[2]

echo <<<EOS
INSERT INTO projects (`id`, `name`, `ftpdir`, `desc`, `DC_Nameplate_Capacity`, `AC_Nameplate_Capacity`, `active`)
    VALUES ($projectId, '$name', '$ftpdir', '', TODO, TODO, 0);\n\n
EOS;

$devices = [];
foreach (glob($ftpdir . '/*.csv') as $filename) {
    $parts = explode('.', basename($filename));
    $devcode = $parts[0];

    if (isset($devices[$devcode])) {
        continue;
    }

    $devices[$devcode] = 1;
    $devtype = getDevType($filename);

    if (!$devtype) {
        echo 'Unknown dev type: $devcode', EOL;
        continue;
    }

    $devcode = str_replace('-', '_', $devcode);

    if ($devtype == 'EnvKit') {
        $table = sprintf('p%d_%s_envkit', $projectId, $devcode);
        echo <<<EOS
INSERT INTO devices (`project_id`, `devcode`, `type`, `table`, `class`, `model`)
    VALUES ($projectId, '$devcode', 'EnvKit', 'table_envkit', '', '');
CREATE TABLE $table LIKE table_envkit;\n\n
EOS;
    }

    if ($devtype == 'GenMeter') {
        $table = sprintf('p%d_%s_genmeter', $projectId, $devcode);
        echo <<<EOS
INSERT INTO devices (`project_id`, `devcode`, `type`, `table`, `class`, `model`) 
    VALUES ($projectId, '$devcode', 'GenMeter', 'table_genmeter', '', '');
CREATE TABLE $table LIKE table_genmeter;\n\n
EOS;
    }

    if ($devtype == 'PVP') {
        $table = sprintf('p%d_%s_inverter', $projectId, $devcode);
        echo <<<EOS
INSERT INTO devices (`project_id`, `devcode`, `type`, `table`, `class`, `model`)
    VALUES ($projectId, '$devcode', 'Inverter', 'table_inverter_pvp', '', 'PVP');
CREATE TABLE $table LIKE table_inverter_pvp;\n\n
EOS;
    }

    if ($devtype == 'SMA') {
        $table = sprintf('p%d_%s_inverter', $projectId, $devcode);
        echo <<<EOS
INSERT INTO devices (`project_id`, `devcode`, `type`, `table`, `class`, `model`)
    VALUES ($projectId, '$devcode', 'Inverter', 'table_inverter_sma', '', 'SMA');
CREATE TABLE $table LIKE table_inverter_sma;\n\n
EOS;
    }
}

function getDevType($filename)
{
    $fp = fopen($filename, 'r');
    $line = fgets($fp);
    fclose($fp);

    if (strpos($line, 'Degrees C')) {
        return 'EnvKit';
    }

    if (strpos($line, 'kwh_del')) {
        return 'GenMeter';
    }

    if (strpos($line, 'f000-f015')) {
        return 'PVP';
    }

    if (strpos($line, 'Mode_SMA')) {
        return 'SMA';
    }

    return false;
}
