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

    public function getFirstGMPicture()
    {
        // Start from current hour
        $start = date('Y-m-d H:00:00');

        $sql = "SELECT * FROM gm_camera_picture WHERE createdon>='$start' LIMIT 1";
        $result = $this->db->fetchOne($sql);

        if (!$result) {
            $sql = "SELECT * FROM gm_camera_picture WHERE id>(SELECT MAX(id)-90 FROM gm_camera_picture) LIMIT 1";
            $result = $this->db->fetchOne($sql);
        }
        return $result;
    }

    public function getGMPicture($id)
    {
        $sql = "SELECT * FROM gm_camera_picture WHERE id=$id";
        $result = $this->db->fetchOne($sql);
        if (!$result) {
            $result = $this->getFirstGMPicture();
        }
        return $result;
    }

    public function drawPlaceholder($w = 1280, $h = 720)
    {
        $im = imagecreatetruecolor($w, $h);

        $textcolor = imagecolorallocate($im, 233, 14, 91);

        $text = 'Camera Not Available';
        $font = 'c:/xampp/php/extras/fonts/ttf/Vera.ttf';

        imagettftext($im, 20, 0, $w/3, $h/2, $textcolor, $font, $text);

        // Set the content type header - in this case image/jpeg
        header('Content-Type: image/jpeg');

        // Output the image
        imagejpeg($im);

        // Free up memory
        imagedestroy($im);
    }

    public function getAllCameras()
    {
        $sql = "SELECT * FROM project_camera";
        return $this->db->fetchAll($sql);
    }

    public function getCameras($prj)
    {
        $sql = "SELECT * FROM project_camera WHERE project_id=$prj";
        return $this->db->fetchAll($sql);
    }

    public function getLatestPictures($prj)
    {
        $pictures = [];

        $cameras = $this->getCameras($prj);

        if ($cameras) {
            foreach ($cameras as $camera) {
                $camid = $camera['id'];
                $sql = "SELECT * FROM camera_picture WHERE project_id=$prj AND camera_id=$camid ORDER BY id DESC";
                $picture = $this->db->fetchOne($sql);
                $picture['camera'] = $camera['camera_name'];
                $pictures[] = $picture;
            }
        }

        return $pictures;
    }

    public function getNextPicture($id)
    {
        $sql = "SELECT * FROM camera_picture WHERE id=$id";
        $curpic = $this->db->fetchOne($sql);
        if (!$curpic) {
            return false;
        }

        $prj = $curpic['project_id'];
        $cam = $curpic['camera_id'];

        $sql = "SELECT *
                  FROM camera_picture
                 WHERE project_id=$prj AND camera_id=$cam AND id>$id
              ORDER BY id LIMIT 1";
        $picture = $this->db->fetchOne($sql);
        return $picture;
    }

    public function getPrevPicture($id)
    {
        $sql = "SELECT * FROM camera_picture WHERE id=$id";
        $curpic = $this->db->fetchOne($sql);
        if (!$curpic) {
            return false;
        }

        $prj = $curpic['project_id'];
        $cam = $curpic['camera_id'];

        $sql = "SELECT *
                  FROM camera_picture
                 WHERE project_id=$prj AND camera_id=$cam AND id<$id
              ORDER BY id DESC LIMIT 1";
        $picture = $this->db->fetchOne($sql);
        return $picture;
    }

    public function getPicturesByDate($prj, $date)
    {
        $pictures = [];

        $cameras = $this->getCameras($prj);

        if ($cameras) {
            foreach ($cameras as $camera) {
                $camid = $camera['id'];
                $sql = "SELECT * FROM camera_picture WHERE project_id=$prj AND camera_id=$camid AND DATE(createdon)='$date' ORDER BY id";
                $picture = $this->db->fetchOne($sql);
                $picture['camera'] = $camera['camera_name'];
                $pictures[] = $picture;
            }
        }

        return $pictures;
    }
}
