<?php

namespace App\Controllers;

class DashboardController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->pageTitle = 'GCS Dashboard';
        $this->view->data = $this->snapshotService->load();
    }

    public function fullAction()
    {
        $this->dispatcher->forward([ 'action' => 'index' ]);
    }

    public function compactAction()
    {
        $this->view->pageTitle = 'GCS Dashboard';
        $this->view->pick('dashboard/compact');
        $this->view->data = $this->snapshotService->load();
    }
}
