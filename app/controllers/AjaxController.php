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
}
