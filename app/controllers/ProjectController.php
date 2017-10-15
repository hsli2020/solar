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

        $this->view->project = $project;
        $this->view->irr = json_encode($irr);
        $this->view->kva = json_encode($kva);
    }

    public function exportAction()
    {
        $this->view->pageTitle = 'Data Exporting';

        if ($this->request->isPost()) {
            $params = $this->request->getPost();
            $filename = $this->exportService->export($params);
            $this->startDownload($filename);
        }

        $projects = $this->projectService->getAll();
        $this->view->projects = $projects;
    }

    public function compareAction()
    {
        $this->view->pageTitle = 'Analytic Tool';

        $this->view->startTime = '';
        $this->view->endTime   = '';
        $this->view->interval  = '';
        $this->view->project1  = '';
        $this->view->project2  = '';
        $this->view->project3  = '';
        $this->view->intervals = [
            1  => ' 1 Minute',
            5  => ' 5 Minute',
            10 => '10 Minute',
            15 => '15 Minute',
        ];

        $data = [];
        if ($this->request->isPost()) {
            // return back to view
            $this->view->startTime = $this->request->getPost('startTime');
            $this->view->endTime   = $this->request->getPost('endTime');
            $this->view->interval  = $this->request->getPost('interval');
            $this->view->project1  = $this->request->getPost('project1');
            $this->view->project2  = $this->request->getPost('project2');
            $this->view->project3  = $this->request->getPost('project3');

            $info = $this->request->getPost();
            $data = $this->dataService->getDataToCompare($info);
        }

        $this->view->projects = $this->projectService->getAll();
        $this->view->data = $data;
    }
}
