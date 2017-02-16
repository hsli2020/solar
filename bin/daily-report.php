<?php

class DailyReport
{
    public function __construct()
    {
        $di = \Phalcon\Di::getDefault();

        $this->db = $di->get('db');
        $this->projectService = $di->get('projectService');
        $this->deviceService  = $di->get('deviceService');
        $this->userService    = $di->get('userService');
        $this->dataService    = $di->get('dataService');
    }

    public function run()
    {
        date_default_timezone_set("America/Toronto");

        $this->log('Start sending daily report');

        $report   = $this->generateDailyReport();
        $filename = $this->generateXls($report);
        $html     = $this->generateHtml($report);

echo $html; return;

        $users = $this->userService->getAll();

        foreach ($users as $user) {
            $email = $user['email'];
            $this->sendDailyReport($email, $html, $filename);
        }

        $this->log("Daily report sending completed.\n");
    }

    protected function generateDailyReport()
    {
        $projects = $this->projectService->getAll();

        $report = [];
        foreach ($projects as $project) {
            $projectId = $project['id'];
            $refdata = $this->dataService->getRefData($projectId, date('Y'), date('m'));

            $Project_Name        = $project['name'];
            $Date                = date('d/m/Y', strtotime('yesterday'));
            $Capacity_AC         = $project['AC_Nameplate_Capacity'];
            $Capacity_DC         = $project['DC_Nameplate_Capacity'];;
            $Monthly_Budget      = $this->getMonthlyBudget($refdata);
            $IE_Insolation       = $this->getIEInsolation($project);
            $Total_Insolation    = $this->getTotalInsolation($projectId);
            $Total_Energy        = $this->getTotalEnergy($projectId);
            $Measured_Insolation = $this->getMeasuredInsolation($projectId);
            $Daily_Expected      = $this->getDailyExpected($Measured_Insolation, $IE_Insolation, $Monthly_Budget);
            $Daily_Production    = $this->getDailyProduction($projectId);
            $Weather_Performance = $this->getWeatherPerformance($Total_Insolation, $IE_Insolation);
            $Actual_Budget       = $this->getActualBudget($Total_Energy, $Monthly_Budget);
            $Actual_Expected     = $this->getActualExpected($Total_Energy, $Monthly_Budget, $Weather_Performance);

            $Weather_Performance = (round($Weather_Performance, 4) * 100) . '%';

            $report[] = compact(
                'Project_Name',
                'Date',
                'Capacity_AC',
                'Capacity_DC',
                'Monthly_Budget',
                'IE_Insolation',
                'Total_Insolation',
                'Total_Energy',
                'Daily_Expected',
                'Daily_Production',
                'Measured_Insolation',
                'Actual_Budget',
                'Actual_Expected',
                'Weather_Performance'
            );
        }

        return $report;
    }

    protected function generateXls($report)
    {
        $excel = PHPExcel_IOFactory::load(__DIR__ . "/templates/DailyReport-v2.xlsx");
        $excel->setActiveSheetIndex(0);  //set first sheet as active

        $sheet = $excel->getActiveSheet();
        $sheet->setCellValue("B3", date('F-d-Y'));

        $row = 10;
        $index = 1;

        foreach ($report as $data) {
            $sheet->setCellValue("A$row", $index++);
            $sheet->setCellValue("B$row", $data['Project_Name']);
            $sheet->setCellValue("C$row", $data['Date']);
            $sheet->setCellValue("D$row", $data['Capacity_AC']);
            $sheet->setCellValue("E$row", $data['Capacity_DC']);
            $sheet->setCellValue("F$row", $data['Monthly_Budget']);
            $sheet->setCellValue("G$row", $data['IE_Insolation']);
            $sheet->setCellValue("H$row", $data['Total_Insolation']);
            $sheet->setCellValue("I$row", $data['Total_Energy']);
           #$sheet->setCellValue("J$row", $data['Daily_Expected']);
            $sheet->setCellValue("K$row", $data['Daily_Production']);
            $sheet->setCellValue("L$row", $data['Measured_Insolation']);
            $sheet->setCellValue("M$row", $data['Actual_Budget']);
            $sheet->setCellValue("N$row", $data['Actual_Expected']);
            $sheet->setCellValue("O$row", $data['Weather_Performance']);
            $row++;
        }

        $today = date('Ymd');
        $filename = BASE_DIR . "/app/logs/DailyReport-$today.xlsx";

        $xlsWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $xlsWriter->save($filename);

        return $filename;
    }

    protected function generateHtml($report)
    {
        ob_start();
        $date = date('F d, Y', strtotime('yesterday'));
        include(__DIR__ . "/templates/daily-report.tpl");
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    protected function getMonthlyBudget($refdata)
    {
        $budget = $refdata['Stonebridge_Base'];
        return $budget;
    }

    protected function getIEInsolation($project)
    {
        return $project['IE_Insolation'];
       #$days = date("t");
       #return round($project['IE_Insolation'] / $days, 2);
    }

    protected function getTotalInsolation($prj)
    {
        $result = $this->dataService->getIRR($prj, 'MONTHLY');
        return round($result / 60.0 / 1000.0, 2);
    }

    protected function getTotalEnergy($prj)
    {
        $result = $this->dataService->getKW($prj, 'MONTHLY');
        return round($result / 60.0, 2);
    }

    protected function getDailyExpected($Measured_Insolation, $IE_Insolation, $Monthly_Budget)
    {
        if (!$IE_Insolation) {
            return '';
        }

        return ($Measured_Insolation / $IE_Insolation) * $Monthly_Budget;
    }

    protected function getDailyProduction($prj)
    {
        $result = $this->dataService->getKW($prj, 'DAILY');
        return round($result / 60.0, 2);
    }

    protected function getMeasuredInsolation($prj)
    {
        $result = $this->dataService->getIRR($prj, 'DAILY');
        return round($result / 60.0 / 1000.0, 2);
    }

    protected function getActualBudget($Total_Energy, $Monthly_Budget)
    {
        if (empty($Monthly_Budget)) {
            return '';
        }

        return (round($Total_Energy / $Monthly_Budget, 4) * 100) . '%';
    }

    protected function getActualExpected($Total_Energy, $Monthly_Budget, $Weather_Performance)
    {
        if (empty($Monthly_Budget)) {
            return '';
        }

        return (round($Total_Energy / $Monthly_Budget * $Weather_Performance, 4) * 100) . '%';
    }

    protected function getWeatherPerformance($Total_Insolation, $IE_Insolation)
    {
        if (empty($IE_Insolation)) {
            return '';
        }

        return $Total_Insolation / $IE_Insolation;
    }

    protected function sendDailyReport($recepient, $body, $filename)
    {
        $mail = new PHPMailer();

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $today = date('Y-m-d');

#       $mail->SMTPDebug = 3;
        $mail->isSMTP();
        $mail->Host = '10.6.200.200';
        $mail->Port = 25;
        $mail->SMTPAuth = false;
        $mail->SMTPSecure = false;
        $mail->From = "OMS@greatcirclesolar.ca";
        $mail->FromName = "Great Circle Solar";
        $mail->addAddress($recepient);
        $mail->addAttachment($filename, basename($filename));
        $mail->isHTML(true);
        $mail->Subject = "Daily Solar Energy Production Report ($today)";
        $mail->Body = $body;
        $mail->AltBody = "Please find the Daily Report in attachment.";

        if (!$mail->send()) {
            $this->log("Mailer Error: " . $mail->ErrorInfo);
        }
        else {
            $this->log("Daily report sent to $recepient.");
        }
    }

    protected function log($str)
    {
        $filename = BASE_DIR . '/app/logs/report.log';
        $str = date('Y-m-d H:i:s ') . $str . "\n";

        echo $str;
        error_log($str, 3, $filename);
    }
}

include __DIR__ . '/../public/init.php';

$job = new DailyReport();
$job->run();
