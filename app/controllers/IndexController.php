<?php

namespace App\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->pageTitle = 'My Dashboard';
    }

    public function testAction()
    {
        $this->view->pageTitle = 'Test Page';
    }
}
