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
            $Daily_Expected      = $this->getDailyExpected($projectId);
            $Daily_Production    = $this->getDailyProduction($projectId);
            $Measured_Insolation = $this->getMeasuredInsolation($projectId);
            $Actual_Budget       = $this->getActualBudget($Daily_Production, $Monthly_Budget);
            $Actual_Expected     = $this->getActualExpected($Daily_Production, $Daily_Expected);
            $Weather_Performance = $this->getWeatherPerformance($Measured_Insolation, $IE_Insolation);

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
            $sheet->setCellValue("J$row", $data['Daily_Expected']);
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
        return 1234; //substr(__FUNCTION__, 3);
        $budget = $refdata['Stonebridge_Base'];
        return $budget;
    }

    protected function getIEInsolation($project)
    {
        return 1234; //substr(__FUNCTION__, 3);
        $days = date("t");
        return round($project['IE_Insolation'] / $days, 2);
    }

    protected function getTotalInsolation($prj)
    {
        return 1234; //substr(__FUNCTION__, 3);
        $result = $this->dataService->getIRR($prj, 'DAILY');
        return round($result / 60.0 / 1000.0, 2);
    }

    protected function getTotalEnergy($prj)
    {
        return 1234; //substr(__FUNCTION__, 3);
        $result = $this->dataService->getKW($prj, 'DAILY');
        return round($result / 60.0, 2);
    }

    protected function getDailyExpected($projectId)
    {
        return 1234; //substr(__FUNCTION__, 3);
    }

    protected function getDailyProduction($projectId)
    {
        return 1234; //substr(__FUNCTION__, 3);
    }

    protected function getMeasuredInsolation($projectId)
    {
        return 1234; //substr(__FUNCTION__, 3);
    }

    // getActualBudget($Daily_Production , $Monthly_Budget);
    protected function getActualBudget($measured_Production , $budget)
    {
        return 1234; //substr(__FUNCTION__, 3);
        if (empty($budget)) {
            return '';
        }

        return (round($measured_Production / $budget, 4) * 100) . '%';
    }

    // getActualExpected($Daily_Production , $Daily_Expected);
    protected function getActualExpected($measured_Production , $expected)
    {
        return 1234; //substr(__FUNCTION__, 3);
        if (empty($expected)) {
            return '';
        }

        return (round($measured_Production / $expected, 4) * 100) . '%';
    }

    // getWeatherPerformance($Measured_Insolation, $IE_Insolation);
    protected function getWeatherPerformance($measured_Insolation, $IE_POA_Insolation)
    {
        return 1234; //substr(__FUNCTION__, 3);
        if (empty($IE_POA_Insolation)) {
            return '';
        }

        return (round($measured_Insolation / $IE_POA_Insolation, 4) * 100) . '%';
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
