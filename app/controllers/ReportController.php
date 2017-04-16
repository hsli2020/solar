<?php

namespace App\Controllers;

class ReportController extends ControllerBase
{
    public function indexAction()
    {
        return $this->dispatcher->forward([
            'controller' => 'report',
            'action' => 'daily'
        ]);
    }

    public function dailyAction()
    {
        $this->view->pageTitle = 'Daily Report';

        $date = date('Ymd', strtotime('-1 day'));
        $filename = BASE_DIR . "/app/logs/daily-report-$date.json";

        if (!file_exists($filename)) {
            $this->view->report = [];
            return;
        }

        $json = file_get_contents($filename);
        $report = json_decode($json, true);

        $this->view->today = date('l, F jS Y');
        $this->view->report = $report;
    }

    public function monthlyAction()
    {
        $this->view->pageTitle = 'Monthly Report';

        $date = date('Ymd', strtotime('-1 day'));
        $filename = BASE_DIR . "/app/logs/monthly-report-$date.json";

        if (!file_exists($filename)) {
            $this->view->report = [];
            return;
        }

        $json = file_get_contents($filename);
        $report = json_decode($json, true);

        $this->view->today = date('l, F jS Y');
        $this->view->report = $report;
    }
}
