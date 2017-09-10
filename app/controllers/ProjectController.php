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
        $this->view->now = date('g:i a');

        $details = $this->projectService->getDetails($id);

        $this->view->details = $details;
    }

    public function chartAction($id = 0)
    {
        $this->view->pageTitle = 'Project Chart';

        $project = $this->projectService->get($id);

        $envkit = $project->getFirstEnvKit();
        $genmeter = $project->getFirstGenMeter();

        $irr = $envkit->getChartData();
        $kva = $genmeter->getChartData();

        $this->view->irr = json_encode($irr);
        $this->view->kva = json_encode($kva);
    }

    public function exportAction()
    {
        $this->view->pageTitle = 'Data Export';

        if ($this->request->isPost()) {
            $project   = $this->request->getPost('project');
            $period    = $this->request->getPost('period');
            $startTime = $this->request->getPost('start-time');
            $endTime   = $this->request->getPost('end-time');

            $filename = $this->exportService->export($project, $period, $startTime, $endTime);

            $this->startDownload($filename);
        }
    }
}
