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
        $this->view->report = [];

        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return;
        }

       #$user = $this->userService->get($auth['id']);

        if (!$date) {
            $date = date('Y-m-d', strtotime('-1 day'));
        }

        // Load daily report (user specific report)
        $report = $this->dailyReportService->load($date, $auth);

        if ($this->request->isPost()) {
            $filename = $this->dailyReportService->generateXls($report, $date);
            $this->startDownload($filename, 'xls');
        }

        $dateList = $this->dailyReportService->getDateList();

        $this->view->date = $date;
        $this->view->dateList = $dateList;
        $this->view->report = $report;
    }

    public function monthlyAction($month = '')
    {
        $this->view->pageTitle = 'Monthly Report';
        $this->view->report = [];

        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return;
        }

       #$user = $this->userService->get($auth['id']);

        if (!$month) {
            $month = date('Y-m', strtotime('-1 month'));
        }

        // Load monthly report (user specific report)
        $report = $this->monthlyReportService->load($month, $auth);

        if ($this->request->isPost()) {
            $filename = $this->monthlyReportService->generateXls($report, $month);
            $this->startDownload($filename, 'xls');
        }

        $monthList = $this->monthlyReportService->getMonthList();

        $this->view->month = $month;
        $this->view->monthList = $monthList;
        $this->view->report = $report;
    }

    public function budgetAction($prj = 1)
    {
        $this->view->pageTitle = 'Monthly Budgets';
        $this->view->budgets = [];

        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return;
        }

       #$user = $this->userService->get($auth['id']);

        if ($this->request->isPost()) {
            $prj = $this->request->getPost('id');
        }

        // Load monthly report (user specific report)
        $budgets = $this->dataService->loadBudget($prj);

        $this->view->curprj = $prj;
        $this->view->projects = $this->projectService->getAll();
        $this->view->budgets = $budgets;
    }
}
