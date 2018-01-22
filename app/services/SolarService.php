<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class SolarService extends Injectable
{
    const TTL = 5*24*60*60; // 5 days

    public function cleanup()
    {
        $this->cleanupFolder(BASE_DIR . "/app/logs/");
        $this->cleanupFolder(BASE_DIR . "/tmp/");
    }

    protected function cleanupFolder($folder)
    {
        $files = glob("$folder/*");
        foreach ($files as $file) {
            if (time() - filemtime($file) > self::TTL) {
                unlink($file);
            }
        }
    }
}
