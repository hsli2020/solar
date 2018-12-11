<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class PictureService extends Injectable
{
    public function getPicture($id)
    {
        $sql = "SELECT * FROM camera_picture WHERE id=$id";
        $result = $this->db->fetchOne($sql);
        if ($result) {
            $result['filename'] = 'c:/GCS-FTP-ROOT/' . $result['filename'];
        }
        return $result;
    }
}
