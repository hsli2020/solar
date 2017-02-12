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
echo $html;
        $users = $this->userService->getAll();

        foreach ($users as $user) {
            $email = $user['email'];
#           $this->sendDailyReport($email, $html, $filename);
        }

        $this->log("Daily report sending completed.\n");
    }

    protected function generateDailyReport()
    {
        $projects = $this->projectService->getAll();
        $devices  = $this->deviceService->getAll();

        $report = [];
        foreach ($projects as $project) {
            $projectId = $project['id'];
            /*(
                [Reference_Insolation] => 80.50
                [Reference_Production] => 38388.37
                [Stonebridge_Base] => 36628.72
                [Measured_Insolation] =>
                [Measured_Production] =>
                [Expected_Production] =>
                [Actual_Production] =>
                [IE_Snow_Loss_Estimate] => 0.00
                [Plant_Availability] =>
                [Grid_Availability] =>
            )*/
            $refdata = $this->dataService->getRefData($projectId, date('Y'), date('m'));

            $project_Name        = $project['name'];
            $date                = date('d/m/Y', strtotime('yesterday'));
            $capacity_AC         = $project['AC_Nameplate_Capacity'];
            $capacity_DC         = $project['DC_Nameplate_Capacity'];;
            $budget              = $refdata['Stonebridge_Base'];

            $measured_Production = $this->getMeasuredProduction($projectId);
            $measured_Insolation = $this->getMeasuredInsolation($projectId);
            $IE_POA_Insolation   = $this->getIEPOAInsolation($projectId);

            $expected            = $this->getExpected($measured_Insolation, $IE_POA_Insolation, $budget);
            $actual_Budget       = $this->getActualBudget();
            $actual_Expected     = $this->getActualExpected();
            $weather_Performance = $this->getWeatherPerformance($measured_Insolation, $IE_POA_Insolation);

            $report[] = compact(
                'project_Name',
                'date',
                'capacity_AC',
                'capacity_DC',
                'budget',
                'measured_Production',
                'measured_Insolation',
                'IE_POA_Insolation',
                'expected',
                'actual_Budget',
                'actual_Expected',
                'weather_Performance'
            );
        }

        return $report;
    }

    protected function generateXls($report)
    {
        $excel = PHPExcel_IOFactory::load("./templates/DailyReport-v1.xls");
        $excel->setActiveSheetIndex(0);  //set first sheet as active

        $sheet = $excel->getActiveSheet();
        $sheet->setCellValue("B3", date('F-d-Y'));

        $row = 10;
        $index = 1;

        foreach ($report as $data) {
            $sheet->setCellValue("A$row", $index++);
            $sheet->setCellValue("B$row", $data['project_Name']);
            $sheet->setCellValue("C$row", $data['date']);
            $sheet->setCellValue("D$row", $data['capacity_AC']);
            $sheet->setCellValue("E$row", $data['capacity_DC']);
            $sheet->setCellValue("F$row", $data['budget']);
            $sheet->setCellValue("G$row", $data['expected']);
            $sheet->setCellValue("H$row", $data['measured_Production']);
            $sheet->setCellValue("I$row", $data['measured_Insolation']);
            $sheet->setCellValue("J$row", $data['IE_POA_Insolation']);
            $sheet->setCellValue("K$row", $data['actual_Budget']);
            $sheet->setCellValue("L$row", $data['actual_Expected']);
            $sheet->setCellValue("M$row", $data['weather_Performance']);
            $row++;
        }

        $today = date('Ymd');
        $filename = BASE_DIR . "/app/logs/DailyReport-$today.xls";

        //downloadable file is in Excel 2003 format (.xls)
        $xlsWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $xlsWriter->save($filename);

        return $filename;
    }

    protected function generateHtml($report)
    {
        ob_start();
        include("templates/daily-report.tpl");
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    protected function getMeasuredProduction($prj)
    {
        return $this->dataService->getKW($prj, 'DAILY');
    }

    protected function getMeasuredInsolation($prj)
    {
        return $this->dataService->getIRR($prj, 'DAILY');
    }

    protected function getIEPOAInsolation($prj)
    {
        $project = $this->projectService->get($prj);
        if ($project) {
            return $project['IE_Insolation'];
        }
        return 0;
    }

    protected function getExpected($measured_Insolation, $IE_POA_Insolation, $budget)
    {
        return 0;

        if (empty($IE_POA_Insolation)) {
            return 0;
        }

        return ($measured_Insolation / $IE_POA_Insolation) * $budget;
    }

    protected function getActualBudget()
    {
        return 1;
        return 'TODO';
    }

    protected function getActualExpected()
    {
        return 1;
        return 'TODO';
    }

    protected function getWeatherPerformance($measured_Insolation, $IE_POA_Insolation)
    {
        return 0;

        if (empty($IE_POA_Insolation)) {
            return 0;
        }

        return ($measured_Insolation / $IE_POA_Insolation);
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

        $mail->SMTPDebug = 3;
        $mail->isSMTP();
        $mail->Host = '10.6.200.200';
        $mail->Port = 25;
        $mail->SMTPAuth = false;
        $mail->SMTPSecure = false;
        $mail->From = "no-reply@greatcirclesolar.com";
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
