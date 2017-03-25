<?php

namespace App\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        return $this->dispatcher->forward([
            'controller' => 'index',
            'action' => 'dashboard'
        ]);
    }

    public function testAction()
    {
        $this->view->pageTitle = 'Test Page';
        $this->view->data = __METHOD__;
        $this->view->data = print_r($this->deviceService->getDevicesOfType(1, 'EnvKit'), true);
    }

    public function dashboardAction()
    {
        $this->view->pageTitle = 'GCS Dashboard';
        $this->view->data = $this->snapshotService->load();
    }

    public function projectAction()
    {
        $this->view->pageTitle = 'Projects';
        $this->view->data = $this->dataService->getSnapshot();
    }

    public function reportAction()
    {
        $this->view->pageTitle = 'Report';
    }
}
