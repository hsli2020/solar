<?php

include __DIR__ . '/../public/init.php';

deleteOldFiles('c:/GCS-FTP-ROOT/NB4-Camera1/', 3600);
deleteOldPictures();

$inifile = 'c:/xampp/htdocs/solar/job/autopulse.ini';
$cfg = parse_ini_file($inifile);
if ($cfg['state'] != 1) {
    exit;
}

$wiper = new App\System\SnowWiper();
$wiper->pulse();

logger('AutoPluse');

function deleteOldFiles($folder, $ttl)
{
    $now = time();

    foreach (new \DirectoryIterator($folder) as $fileInfo) {
        if (!$fileInfo->isDot()) {
            if ($fileInfo->isDir()) {
                deleteOldFiles($fileInfo->getPathname(), $ttl);
                //rmdir($fileInfo->getPathname());
            } else {
                if (strtolower($fileInfo->getExtension()) != 'jpg') {
                    continue;
                }

                if ($now - $fileInfo->getMTime() > $ttl) {
                    $fullpath = str_replace('\\', '/', $fileInfo->getPathname());
                    echo $fullpath, PHP_EOL;
                    unlink($fullpath);
                }
            }
        }
    }
}

function deleteOldPictures()
{
    $di = \Phalcon\Di::getDefault();
    $db = $di->get('db');
    $db->execute('DELETE FROM camera_picture WHERE project_id=999 AND createdon<(NOW() - INTERVAL 5 MINUTE)');
}

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
