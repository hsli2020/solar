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

    public function drawPlaceholder($w = 1280, $h = 720)
    {
        $im = imagecreatetruecolor($w, $h);

        $textcolor = imagecolorallocate($im, 233, 14, 91);
        imagestring($im, 1, $w/2, $h/2, 'No Camera', $textcolor);

        // Set the content type header - in this case image/jpeg
        header('Content-Type: image/jpeg');

        // Output the image
        imagejpeg($im);

        // Free up memory
        imagedestroy($im);
    }
}
