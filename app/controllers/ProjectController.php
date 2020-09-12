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

        // TODO: error check
        $project = $this->projectService->get($prj);
        $combiner = $project->combiners[$dev];
        $ref = $combiner->reference;
        $inverter = $project->inverters[$ref];

        $this->view->project = $project;
        $this->view->combiner = $combiner;
        $this->view->inverter = $inverter;
        $this->view->data = $data;
    }

    // This is for project-40/48 only
    public function sandhurstAction($prj = '', $devcode = '')
    {
        $this->view->pageTitle = 'Combiner';

        $data = $this->projectService->loadSandhurstInverter($prj, $devcode);
        $project = $this->projectService->get($prj);

        $this->view->project = $project;
        $this->view->devcode = $devcode;
        $this->view->data = $data;
    }

    // This is for project-40/48 only
    public function stringlevelAction($prj = '', $devcode = '')
    {
        $this->view->pageTitle = 'String Level Combiner';

        $data = $this->projectService->loadStringLevelCombiner($prj, $devcode);
        $project = $this->projectService->get($prj);

        $this->view->project = $project;
        $this->view->devcode = $devcode;
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

        $date1 = date('Y-m-d');
        $date2 = date('Y-m-d', strtotime("-1 days"));

        if ($this->request->isPost()) {
            $date2 = $this->request->getPost('date2');
            if ($this->request->getPost('btn') == 'prev') {
                $date2 = date('Y-m-d', strtotime('-1 day', strtotime($date2)));
            }
            if ($this->request->getPost('btn') == 'next') {
                $date2 = date('Y-m-d', strtotime('+1 day', strtotime($date2)));
            }
        }

        $irr1 = $kva1 = '';
        if ($date1) {
            $data = $project->getChartData($date1);
            $irr1 = $data[0];
            $kva1 = $data[1];
        }

        $irr2 = $kva2 = '';
        if ($date2) {
            $data = $project->getChartData($date2);
            $irr2 = $data[0];
            $kva2 = $data[1];
        }

        $this->view->project = $project;
        $this->view->acsize = min($project->getSizeAC(), $project->getSizeDC());

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
            set_time_limit(0);
            $params = $this->request->getPost();
            $filename = $this->exportService->export($params);
            $this->startDownload($filename);
        }

        $projects = $this->projectService->getAll();
        $this->view->projects = $projects;
    }

    public function exportDailyAction()
    {
        $this->view->pageTitle = 'Daily Data Exporting';

        if ($this->request->isPost()) {
            set_time_limit(0);
            $params = $this->request->getPost();
            $filename = $this->exportService->exportDaily($params);
            $this->startDownload($filename);
        }

        $projects = $this->projectService->getAll();
        $this->view->projects = $projects;
    }

    public function exportCombinerAction($prj = '', $dev = '')
    {
        $this->view->pageTitle = 'Combiner Data Exporting';

        if ($this->request->isPost()) {
            set_time_limit(0);
            $params = $this->request->getPost();
            $filename = $this->exportService->exportCombiner($params);
            if ($filename) {
                $this->startDownload($filename);
            }
        }

        $projects = $this->projectService->getCombinerProjects();
        $this->view->projects = $projects;
        $this->view->prj = $prj;
        $this->view->dev = $dev;
    }

    public function dumpDataAction($prj = '', $dev = '')
    {
        if ($prj && $dev) {
            set_time_limit(0);
            $filename = $this->exportService->exportTable($prj, $dev);
            if ($filename) {
                $this->startDownload($filename);
            }
        }
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
            1  => 'Raw data',
            5  => '5 Minutes',
            15 => '15 Minutes',
            30 => '30 Minutes',
            60 => '60 Minutes',
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

    // pic can be id or date
    public function cameraAction($prj = '', $cam = '', $pic = '')
    {
        $this->view->pageTitle = 'Camera';

        if ($prj == '') {
            $prj = 9;
        }
        $project = $this->projectService->get($prj);

        $this->view->project = $project;
       #$this->view->camera  = $cam;
       #$this->view->picture = $pic;

        $this->view->pictures = $project->getLatestCameraPictures();
    }

    public function newboro4Action()
    {
        $this->view->pageTitle = 'Newboro4';
    }

    public function crhAction($id = '')
    {
        $n = ($id - 50); // 51 -> 1, 52 -> 2
        $this->view->pageTitle = "CRH $n Dashboard";

        $date = date('Y-m-d');
        $now = date('Y-m-d H:00');
        $temp = $this->dataService->getCrhData($id, $date);

        $data = $base = $load = [];
        foreach ($temp as $hour => $d) {
            if ($hour > 7 && $hour < 21) {
                $data[] = $d;
                $base[] = [ $d[0], $d[1] ];
                $load[] = [ $d[0], $d[2] ];
            }
        }

        $this->view->date = $date;
        $this->view->now  = $now;
        $this->view->data = $data;

        $this->view->jsonBase = json_encode($base);
        $this->view->jsonLoad = json_encode($load);
    }

    public function testAction()
    {
        echo __METHOD__;
    }
}
