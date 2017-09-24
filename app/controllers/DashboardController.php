<?php

namespace App\Controllers;

class DashboardController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->pageTitle = 'GCS Dashboard';
        $this->view->data = $this->snapshotService->load();
    }
}
