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

    public function dailyAction($date = '')
    {
        $this->view->pageTitle = 'Daily Report';
        $this->view->today = date('l, F jS Y');
        $this->view->report = [];

        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return;
        }

       #$user = $this->userService->get($auth['id']);

        // Load daily report
        if (!$date) {
            $date = date('Y-m-d', strtotime('-1 day'));
        }
        $report = $this->dailyReportService->load($date);

        // Get user specific report
        $report = $this->dailyReportService->getUserSpecificReports($auth, $report);
        $dateList = $this->dailyReportService->getDateList();

        $this->view->date = $date;
        $this->view->dateList = $dateList;
        $this->view->report = $report;
    }

    public function monthlyAction($month = '')
    {
        $this->view->pageTitle = 'Monthly Report';
        $this->view->today = date('l, F jS Y');
        $this->view->report = [];

        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return;
        }

       #$user = $this->userService->get($auth['id']);

        // Load monthly report
        if (!$month) {
            $month = date('Y-m', strtotime('-1 month'));
        }
        $report = $this->monthlyReportService->load($month);

        // Get user specific report
        $report = $this->monthlyReportService->getUserSpecificReports($auth, $report);
        $monthList = $this->monthlyReportService->getMonthList();

        $this->view->month = $month;
        $this->view->monthList = $monthList;
        $this->view->report = $report;
    }
}
