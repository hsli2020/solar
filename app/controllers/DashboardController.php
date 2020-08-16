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

    public function crhAction()
    {
        $this->view->pageTitle = 'CRH Dashboard';

        $date = '2017-10-30';

        $data = $this->dataService->getCrhData($date);

        $base = $load = [];
        foreach ($data as $hour => $d) {
            if ($hour > 7 && $hour < 21) {
                $base[] = [ $d[0], $d[1] ];
                $load[] = [ $d[0], $d[2] ];
            }
        }

        $this->view->date = $date;
        $this->view->data = $data;

        $this->view->jsonBase = json_encode($base);
        $this->view->jsonLoad = json_encode($load);
    }
}
