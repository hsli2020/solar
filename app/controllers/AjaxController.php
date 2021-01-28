<?php

namespace App\Controllers;

class AjaxController extends ControllerBase
{
    public function initialize()
    {
        $this->view->disable();
    }

    public function dataAction()
    {
        if ($this->request->isPost()) {
            $prj = $this->request->getPost('prj');
            $dev = $this->request->getPost('dev');
            $col = $this->request->getPost('col');

           #$this->response->setJsonContent(['status' => 'ERROR', 'message' => 'Unknown column']);
           #$this->response->setJsonContent(['status' => 'OK']);

            $this->response->setJsonContent(['status' => 'OK',
                //$prj=1; $dev='mb-001'; $col='dcvolts';
                'data' => $this->dataService->getChartData($prj, $dev, $col)
            ]);

            return $this->response;
        }
    }

    public function nextPicAction($id = '')
    {
        $this->response->setContentType("application/json");

        $picture = $this->pictureService->getNextPicture($id);
        if ($picture) {
            $this->response->setJsonContent(['status' => 'OK', 'picture' => $picture ]);
        } else {
            $this->response->setStatusCode(404);
            $this->response->setJsonContent(['status' => 'ERROR', 'message' => 'No Picture']);
        }

        return $this->response;
    }

    public function prevPicAction($id = '')
    {
        $this->response->setContentType("application/json");

        $picture = $this->pictureService->getPrevPicture($id);
        if ($picture) {
            $this->response->setJsonContent(['status' => 'OK', 'picture' => $picture ]);
        } else {
            $this->response->setStatusCode(404);
            $this->response->setJsonContent(['status' => 'ERROR', 'message' => 'No Picture']);
        }

        return $this->response;
    }

    // Temp code for newboro4
    public function latestPicAction($id = '')
    {
#=>
        // only today's files (for better performance)
        $root = 'c:/GCS-FTP-ROOT';
        $dir = 'NB4-Camera1/03115807_097026';
        $today = date('Y-m-d');
        $now = date('H/i');

        // c:/GCS-FTP-ROOT/NB4-Camera1/03115807_097026/2019-01-27/001/jpg/18/46/10[M][0@0][0].jpg
        $folder = "$root/$dir/$today/001/jpg/$now";

        // project_id = 999, camera_id=3
        $this->importService->importPicturesInFolder($root, $folder, 999, 3);

        // Get autoPulse state
        $inifile = 'c:/xampp/htdocs/solar/job/autopulse.ini';
        $autoPulse = parse_ini_file($inifile);

        // Get Wiper state
        $wiper = new \App\System\SnowWiper();
        $wiperState = $wiper->getState();
#=>
        if ($id == 0) {
            $pictures = $this->pictureService->getLatestPictures(999);
            $picture = $pictures ? $pictures[0] : false;
        } else {
            $picture = $this->pictureService->getNextPicture($id);
        }

        if ($picture) {
            $this->response->setJsonContent([
                'status'    => 'OK',
                'picture'   => $picture,
                'wiper'     => $wiperState,
                'autopulse' => $autoPulse,
            ]);
        } else {
            $this->response->setStatusCode(404);
            $this->response->setJsonContent(['status' => 'ERROR', 'message' => 'No Picture']);
        }

        $this->response->setContentType("application/json");
        return $this->response;
    }

    public function nextGMPicAction($id = '0')
    {
        $picture = $this->pictureService->getGMPicture($id);
        $picture['filename'] = pathinfo($picture['filename'], PATHINFO_FILENAME);
        return $this->json('OK', $picture);
    }
}
