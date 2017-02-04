<?php

namespace App\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->pageTitle = 'My Dashboard';
        return $this->dispatcher->forward([
            'controller' => 'index',
            'action' => 'project'
        ]);
    }

    public function testAction()
    {
        $this->view->pageTitle = 'Test Page';
        $this->view->data = __METHOD__;
        $this->view->data = print_r($this->dataService->getDevicesOfType(1, 'EnvKit'), true);
    }

    public function dashboardAction()
    {
        $this->view->pageTitle = 'Dashboard';
        $this->view->data = $this->snapshotService->load();
    }

    public function projectAction()
    {
        $this->view->pageTitle = 'Project';
        $this->view->data = $this->dataService->getSnapshot();
    }

    public function reportAction()
    {
        $this->view->pageTitle = 'Report';
    }
}
