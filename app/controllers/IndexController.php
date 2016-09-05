<?php

namespace App\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->pageTitle = 'My Dashboard';
        return $this->dispatcher->forward([
            'controller' => 'index',
            'action' => 'chart'
        ]);
    }

    public function testAction()
    {
        $this->view->pageTitle = 'Test Page';
        $this->view->data = __METHOD__;
        $this->view->data = print_r($this->dataService->getDevicesOfType(1, 'EnvKit'), true);
    }

    public function tableAction()
    {
        $this->view->pageTitle = 'Table';
        $this->view->data = $this->dataService->getSnapshot();
    }

    public function chartAction()
    {
        $this->view->pageTitle = 'Chart';
    }
}
