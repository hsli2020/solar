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
            readfile($file);
        } else {
            $this->pictureService->drawPlaceholder();
        }
    }
}
