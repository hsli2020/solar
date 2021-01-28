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
            $this->outputPicture($file);
        } else {
            $this->pictureService->drawPlaceholder();
        }
    }

    // GM-Camera picture
    public function gmshowAction($id)
    {
        $this->view->disable();

        $picture = $this->pictureService->getGMPicture($id);
        if ($picture) {
            $file = $picture['filename'];
            $this->outputPicture($file);
        }
    }

    protected function outputPicture($file)
    {
        $type = 'image/jpeg';
        header('Content-Type:'.$type);
        header('Content-Length: ' . filesize($file));

        $ttl = 86400; // 1 day (60sec * 60min * 24hours)
        $ts = gmdate("D, d M Y H:i:s", time() + $ttl) . " GMT";
        header("Expires: $ts");
        header("Pragma: cache");
        header("Cache-Control: max-age=$ttl");

        readfile($file);
    }
}
