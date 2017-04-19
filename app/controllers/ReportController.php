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
        $this->view->today = date('l, F jS Y');
        $this->view->report = [];

        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return;
        }

        // Load monthly report
       #$user = $this->userService->get($auth['id']);
        $date = date('Y-m-d', strtotime('-1 day'));
        $report = $this->dailyReportService->load($date);

        // Get user specific report
        $report = $this->dailyReportService->getUserSpecificReports($auth, $report);

        $this->view->report = $report;
    }

    public function monthlyAction()
    {
        $this->view->pageTitle = 'Monthly Report';
        $this->view->today = date('l, F jS Y');
        $this->view->report = [];

        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return;
        }

        // Load monthly report
       #$user = $this->userService->get($auth['id']);
        $date = date('Y-m', strtotime('-1 month'));
        $report = $this->monthlyReportService->load($date);

        // Get user specific report
        $report = $this->monthlyReportService->getUserSpecificReports($auth, $report);

        $this->view->report = $report;
    }
}
