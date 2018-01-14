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

        try {
            $details = $this->projectService->getDetails($id);
            $this->view->details = $details;
        } catch (\Exception $e) {
           #$this->response->redirect('/error/404');
            $this->dispatcher->forward([
                'controller' => 'error',
                'action'     => 'error404'
            ]);
        }
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

        try {
            $project = $this->projectService->get($id);
        } catch (\Exception $e) {
            $this->dispatcher->forward([
                'controller' => 'error',
                'action'     => 'error404'
            ]);
            return;
        }

        $envkit = $project->getFirstEnvKit();
        $genmeter = $project->getFirstGenMeter();

        $date1 = date('Y-m-d');
        $date2 = '';

        if ($this->request->isPost()) {
           #$date1 = $this->request->getPost('date1');
            $date2 = $this->request->getPost('date2');
        }

        $irr1 = $kva1 = '';
        if ($date1) {
            $irr1 = $envkit->getChartData($date1);
            $kva1 = $genmeter->getChartData($date1);
        }

        $irr2 = $kva2 = '';
        if ($date2) {
            $irr2 = $envkit->getChartData($date2);
            $kva2 = $genmeter->getChartData($date2);
        }

        $this->view->project = $project;
        $this->view->acsize = $project->getSizeAC();

        $this->view->date1 = $date1;
        $this->view->irr1 = json_encode($irr1);
        $this->view->kva1 = json_encode($kva1);

        $this->view->date2 = $date2;
        $this->view->irr2 = json_encode($irr2);
        $this->view->kva2 = json_encode($kva2);
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
            $this->view->projects  = $this->request->getPost('projects');

            $info = $this->request->getPost();
            $data = $this->dataService->getDataToCompare($info);

            if (isset($info['export'])) {
                $filename = $this->saveToFile($info, $data, $allProjects);
                $this->startDownload($filename);
            }
        }

        foreach ($this->view->projects as $project) {
            $allProjects[$project]->selected = true;
        }

        $this->view->allProjects = $allProjects;
        $this->view->data = $data;
    }

    protected function saveToFile($info, $data, $allProjects)
    {
        $startTime = $info['startTime'];
        $endTime   = $info['endTime'];
        $interval  = $info['interval'];
        $projects  = $info['projects'];

        $filename = BASE_DIR . '/tmp/site-analytic.csv';

        $fp = fopen($filename, 'w');
        fputs($fp, "Site Analystics\n\n");
        fputs($fp, "Start Time: $startTime\n");
        fputs($fp, "End Time:   $endTime\n");
        fputs($fp, "Interval:   $interval minute\n\n");

        // first line of title
        fputs($fp, ", ");
        foreach ($projects as $project) {
            fputs($fp, $allProjects[$project]->name);
            fputs($fp, ",,,");
        }
        fputs($fp, "\n");

        // second line of title
        fputs($fp, "Time");
        foreach ($projects as $project) {
            fputs($fp, ", ");
            fputs($fp, "Inverter, EnvKit, GenMeter");
        }
        fputs($fp, "\n");

        // third line of title
        foreach ($projects as $project) {
            fputs($fp, ", ");
            fputs($fp, "KW, IRR, KWH");
        }
        fputs($fp, "\n");

        // data
        foreach ($data as $time => $row) {
            fputs($fp, $time);
            foreach ($row as $prj => $vals) {
                fputs($fp, ', ');
                fputs($fp, $vals['kw']);
                fputs($fp, ', ');
                fputs($fp, $vals['irr']);
                fputs($fp, ', ');
                fputs($fp, $vals['kwh']);
            }
            fputs($fp, "\n");
        }

        fclose($fp);

        return $filename;
    }
}
