<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class SolarService extends Injectable
{
    const TTL = 5*24*60*60; // 5 days

    public function cleanup()
    {
        $this->cleanupFolder(BASE_DIR . "/app/logs");
        $this->cleanupFolder(BASE_DIR . "/tmp");
        $this->cleanupPictures();
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

    protected function cleanupPictures()
    {
        $rootDir = 'C:/GCS-FTP-ROOT/';

        $arr = []; // picture-id need to be deleted

        $result = $this->db->query("SELECT * FROM camera_picture");
        while ($row = $result->fetch(\Phalcon\Db::FETCH_ASSOC)) {
            $id = $row['id'];
            $filename = $rootDir . $row['filename'];

            if (!file_exists($filename)) {
                $arr[] = $id;
                echo $id, ' ~ ', $filename, EOL;
            } else if (time() - filemtime($filename) > self::TTL) {
                $arr[] = $id;
                unlink($filename);
                echo $id, ' - ', $filename, EOL;
            }
        }

        if ($arr) {
            $ids = implode(',', $arr);
            $sql = "DELETE FROM camera_picture WHERE id IN ($ids)";
            $this->db->execute($sql);

            $sql = "SET @newid=0; UPDATE camera_picture SET id=(@newid:=@newid+1) ORDER BY id;";
            $maxid = $this->db->fetchColumn("SELECT MAX(id)+1 FROM camera_picture");

            $sql = "ALTER TABLE camera_picture AUTO_INCREMENT = $maxid";
            $this->db->execute($sql);
        }
    }
}
