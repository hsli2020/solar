<?php

include __DIR__ . '/../public/init.php';

$inifile = 'c:/xampp/htdocs/solar/app/logs/autopulse.ini';
$cfg = parse_ini_file($inifile);
if ($cfg['state'] != 1) {
    exit;
}

$wiper = new App\System\SnowWiper();
$wiper->pulse();

logger('AutoPluse');

function logger($str)
{
    $filename = BASE_DIR . '/app/logs/autopulse.log';

    if (file_exists($filename) && filesize($filename) > 512*1024) {
        unlink($filename);
    }

    $str = date('Y-m-d H:i:s ') . $str . "\n";

    echo $str;
    error_log($str, 3, $filename);
}
