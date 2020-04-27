<?php

namespace App\Controllers;

class DashboardController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->pageTitle = 'GCS Dashboard';

        $sites = 'all';
        if ($this->cookies->has('sites')) {
            $sites = $this->cookies->get('sites')->getValue();
        }

        $mode = 'full';
        if ($this->cookies->has('mode')) {
            $mode = $this->cookies->get('mode')->getValue();
        }

        if ($mode == 'full') {
            $this->view->pick('dashboard/index');
        } else {
            $this->view->pick('dashboard/compact');
        }

        $this->view->host = gethostname();
        $this->view->data = $this->snapshotService->load($sites);
    }

    public function fullAction()
    {
        $this->cookies->set('mode', 'full', time()+ 15*86400);
        $this->cookies->send();
        $this->response->redirect('/dashboard');
    }

    public function compactAction()
    {
        $this->cookies->set('mode', 'compact', time()+ 15*86400);
        $this->cookies->send();
        $this->response->redirect('/dashboard');
    }

    public function sitesAction($sites = 'all')
    {
        $this->cookies->set('sites', $sites, time()+ 15*86400);
        $this->cookies->send();
        $this->response->redirect('/dashboard');
    }
}
