<?php

namespace App\Controllers;

class PictureController extends ControllerBase
{
    public function showAction($id)
    {
        $this->view->disable();
        $picture = $this->pictureService->getPicture($id);

        if ($picture) {
            $file = $picture['filename'];
            $type = 'image/jpeg';
            header('Content-Type:'.$type);
            header('Content-Length: ' . filesize($file));

            $ttl = 2592000; // 30days (60sec * 60min * 24hours * 30days)
            $ts = gmdate("D, d M Y H:i:s", time() + $ttl) . " GMT";
            header("Expires: $ts");
            header("Pragma: cache");
            header("Cache-Control: max-age=$ttl");

            readfile($file);
        } else {
            $this->pictureService->drawPlaceholder();
        }
    }
}
