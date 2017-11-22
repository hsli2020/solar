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
        $this->view->refreshInterval = 60;

        $details = $this->projectService->getDetails($id);

        $this->view->details = $details;
    }

    public function combinerAction($key = '')
    {
        $this->view->pageTitle = 'Combiner';

        list($prj, $dev) = explode('_', $key);

        $data = $this->projectService->loadCombiner($prj, $dev);
        $project = $this->projectService->get($prj);

        $this->view->project = $project;
        $this->view->data = $data;
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

        $allProjects = $this->projectService->getAll();

        $this->view->startTime = '';
        $this->view->endTime   = '';
        $this->view->interval  = '';
        $this->view->nozero    = '1';
        $this->view->projects  = [];
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
            $this->view->nozero    = $this->request->getPost('nozero');
            $this->view->projects  = $this->request->getPost('projects');

            $info = $this->request->getPost();
            $data = $this->dataService->getDataToCompare($info);

            if ($this->view->nozero) {
#               $data = array_filter($data, function($row) {
#                   if ($row['project1']['kw'] + $row['project2']['kw'] + $row['project3']['kw'] > 0) {
#                       return $row;
#                   }
#               });
            }
        }

        $this->view->allProjects = $allProjects;
        $this->view->data = $data;
    }
}
