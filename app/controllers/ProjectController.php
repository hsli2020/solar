<?php

namespace App\Controllers;

class ProjectController extends ControllerBase
{
    public function indexAction()
    {
        #return $this->dispatcher->forward([
        #    'controller' => 'report',
        #    'action' => 'daily'
        #]);
    }

    public function detailAction($id = 0)
    {
        $this->view->pageTitle = 'Project Details';
        $this->view->today = date('l, F jS Y');

        $details = $this->projectService->getDetails($id);

        $this->view->details = $details;
    }
}
