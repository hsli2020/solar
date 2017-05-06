<?php

const EOL = "\n";

if (count($argv) != 3) {
    echo "usage: php new-prj.php project_id ftpdir", EOL;
    exit;
}

$projectId = $argv[1];
$dir = $argv[2];
$ftpdir = "C:\\\\GCS-FTP-ROOT\\\\$dir";
$name = substr($dir, 0, strpos($dir, '_'));

echo '-- remember change 222 to DC size, 111 to AC size', EOL, EOL;

echo "INSERT INTO projects (`id`, `name`, `ftpdir`, `desc`, `DC_Nameplate_Capacity`, `AC_Nameplate_Capacity`, `active`)\n";
echo "\tVALUES ($projectId, '$name', '$ftpdir', '', 222, 111, 0);\n\n";

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
        echo "Unknown dev type: $devcode\n";
        continue;
    }

    if ($devtype == 'EnvKit') {
        $table = sprintf('p%d_%s_envkit', $projectId, str_replace('-', '_', $devcode));
        echo "INSERT INTO devices (`project_id`, `devcode`, `type`, `table`, `class`, `model`)\n";
        echo "\tVALUES ($projectId, '$devcode', 'EnvKit', 'table_envkit', '', '');\n";
        echo "CREATE TABLE $table LIKE table_envkit;\n\n";
    }

    if ($devtype == 'GenMeter') {
        $table = sprintf('p%d_%s_genmeter', $projectId, str_replace('-', '_', $devcode));
        echo "INSERT INTO devices (`project_id`, `devcode`, `type`, `table`, `class`, `model`)\n";
        echo "\tVALUES ($projectId, '$devcode', 'GenMeter', 'table_genmeter', '', '');\n";
        echo "CREATE TABLE $table LIKE table_genmeter;\n\n";
    }

    if ($devtype == 'PVP') {
        $table = sprintf('p%d_%s_inverter', $projectId, str_replace('-', '_', $devcode));
        echo "INSERT INTO devices (`project_id`, `devcode`, `type`, `table`, `class`, `model`)\n";
        echo "\tVALUES ($projectId, '$devcode', 'Inverter', 'table_inverter_pvp', '', 'PVP');\n";
        echo "CREATE TABLE $table LIKE table_inverter_pvp;\n\n";
    }

    if ($devtype == 'SMA') {
        $table = sprintf('p%d_%s_inverter', $projectId, str_replace('-', '_', $devcode));
        echo "INSERT INTO devices (`project_id`, `devcode`, `type`, `table`, `class`, `model`)\n";
        echo "\tVALUES ($projectId, '$devcode', 'Inverter', 'table_inverter_sma', '', 'SMA');\n";
        echo "CREATE TABLE $table LIKE table_inverter_sma;\n\n";
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
