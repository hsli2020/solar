<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class DailyReportService extends Injectable
{
    protected $report;

    public function generate()
    {
        $projects = $this->projectService->getAll();

        $this->report = [];
        foreach ($projects as $project) {
            $projectId = $project['id'];
            $monthly = $this->dataService->getMonthlyBudget($projectId, date('Y'), date('m'));

            $Project_Name        = $project['name'];
            $Date                = date('d/m/Y', strtotime('yesterday'));
            $Capacity_AC         = $project['AC_Nameplate_Capacity'];
            $Capacity_DC         = $project['DC_Nameplate_Capacity'];;
            $Monthly_Budget      = $monthly['Budget']; // $this->getMonthlyBudget($monthly);
            $IE_Insolation       = $monthly['IE_POA_Insolation']; // $this->getIEInsolation($monthly);

            $Total_Insolation    = $this->getTotalInsolation($projectId);
            $Total_Energy        = $this->getTotalEnergy($projectId);

            $Measured_Insolation = $this->getMeasuredInsolation($projectId);
            $Measured_Production = $this->getMeasuredProduction($projectId);
            $Gen_Meter_Reading   = $this->getGenMeterReading($projectId);

            $Daily_Budget        = $this->getDailyProduction($Monthly_Budget);
            $Daily_Insolation    = $this->getDailyInsolation($IE_Insolation);
            $Daily_Expected      = $this->getDailyExpected($Measured_Insolation, $Daily_Insolation, $Daily_Budget);

            $Weather_Performance = $this->getWeatherPerformance($Total_Insolation, $Daily_Insolation);
            $Actual_Budget       = $this->getActualBudget($Total_Energy, $Daily_Budget);
            $Actual_Expected     = $this->getActualExpected($Total_Energy, $Daily_Budget, $Weather_Performance);

            $this->report[$projectId] = [
                'Project_Name'          =>  $Project_Name,
                'Date'                  =>  $Date,
                'Capacity_AC'           =>  number_format($Capacity_AC,         1, '.', ''),
                'Capacity_DC'           =>  number_format($Capacity_DC,         1, '.', ''),
                'Monthly_Budget'        =>  number_format($Monthly_Budget,      1, '.', ''),
                'IE_Insolation'         =>  number_format($IE_Insolation,       1, '.', ''),
                'Total_Insolation'      =>  number_format($Total_Insolation,    1, '.', ''),
                'Total_Energy'          =>  number_format($Total_Energy,        1, '.', ''),
                'Daily_Expected'        =>  number_format($Daily_Expected,      1, '.', ''),
                'Daily_Budget'          =>  number_format($Daily_Budget,        1, '.', ''),
                'Daily_Insolation'      =>  number_format($Daily_Insolation,    1, '.', ''),
                'Measured_Insolation'   =>  number_format($Measured_Insolation, 1, '.', ''),
                'Measured_Production'   =>  number_format($Measured_Production, 1, '.', ''),
                'Actual_Budget'         => (number_format($Actual_Budget,       3, '.', '')*100).'%',
                'Actual_Expected'       => (number_format($Actual_Expected,     3, '.', '')*100).'%',
                'Weather_Performance'   => (number_format($Weather_Performance, 3, '.', '')*100).'%',
                'Gen_Meter_Reading'     =>  number_format($Gen_Meter_Reading,   1, '.', ''),
            ];
        }

        return $this->report;
    }

    public function send($debug = false)
    {
        $this->log('Start sending daily report');

        $report = $this->report;

        $users = $this->userService->getAll();

        foreach ($users as $user) {
            $filename = $this->generateXls($user, $report);
            $html = $this->generateHtml($user, $report);

            if ($debug) {
                $uid = $user['id'];
                file_put_contents(BASE_DIR . "/app/logs/d-u-$uid.html", $html);
                continue;
            }

            $this->sendDailyReport($user['email'], $html, $filename);
        }

        $this->log("Daily report sending completed.\n");
    }

    protected function generateXls($user, $report)
    {
        $report = $this->getUserSpecificReport($user, $report);

        $excel = \PHPExcel_IOFactory::load("./templates/DailyReport-v3.xlsx");
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
            $sheet->setCellValue("J$row", $data['Daily_Budget']);
           #$sheet->setCellValue("K$row", $data['Daily_Expected']);
            $sheet->setCellValue("L$row", $data['Daily_Insolation']);
            $sheet->setCellValue("M$row", $data['Measured_Insolation']);
            $sheet->setCellValue("N$row", $data['Measured_Production']);
            $sheet->setCellValue("O$row", $data['Gen_Meter_Reading']);
            $sheet->setCellValue("P$row", $data['Actual_Budget']);
            $sheet->setCellValue("Q$row", $data['Actual_Expected']);
            $sheet->setCellValue("R$row", $data['Weather_Performance']);
            $row++;
        }

        $sheet->setCellValue("B22", date("t"));
        $sheet->setCellValue("B23", date("j"));

        $today = date('Ymd');
        $filename = BASE_DIR . "/app/logs/DailyReport-$today.xlsx";

        $xlsWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $xlsWriter->save($filename);

        return $filename;
    }

    protected function generateHtml($user, $report)
    {
        $report = $this->getUserSpecificReport($user, $report);

        ob_start();
        $date = date('F d, Y', strtotime('yesterday'));
        include("./templates/daily-report.tpl");
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    protected function getUserSpecificReport($user, $report)
    {
        $result = [];

        $projects = $this->userService->getSpecificProjects($user['id']);

        foreach ($projects as $id) {
            if (isset($report[$id])) {
                $result[$id] = $report[$id];
            }
        }

        return $result;
    }

    protected function getMonthlyBudget($monthly)
    {
        return $monthly['Budget'];
    }

    protected function getIEInsolation($monthly)
    {
        return $monthly['IE_POA_Insolation'];
       #$days = date("t");
       #return $montyly['IE_POA_Insolation'] / $days;
    }

    protected function getTotalInsolation($prj)
    {
        $result = $this->dataService->getIRR($prj, 'MONTH-TO-DATE');
        return $result / 60.0 / 1000.0;
    }

    protected function getTotalEnergy($prj)
    {
        $result = $this->dataService->getKW($prj, 'MONTH-TO-DATE');
        return $result / 60.0;
    }

    protected function getDailyExpected($Measured_Insolation, $Daily_Insolation, $Daily_Budget)
    {
        if (!$Daily_Insolation) {
            return 0;
        }

        return ($Measured_Insolation / $Daily_Insolation) * $Daily_Budget;
    }

    protected function getDailyProduction($Monthly_Budget)
    {
        $days = date("t");
        return $Monthly_Budget / $days;
    }

    protected function getDailyInsolation($IE_Insolation)
    {
        $days = date("t");
        return $IE_Insolation / $days;
    }

    protected function getMeasuredInsolation($prj)
    {
        $result = $this->dataService->getIRR($prj, 'DAILY');
        return $result / 60.0 / 1000.0;
    }

    protected function getMeasuredProduction($prj)
    {
        $result = $this->dataService->getKW($prj, 'DAILY');
        return $result / 60.0;
    }

    protected function getActualBudget($Total_Energy, $Daily_Budget)
    {
        if (empty($Daily_Budget)) {
            return 0;
        }

        $days = date("j");
        return $Total_Energy / ($Daily_Budget * $days);
    }

    protected function getActualExpected($Total_Energy, $Daily_Production, $Weather_Performance)
    {
        if (empty($Daily_Production)) {
            return 0;
        }

        $days = date("j");
        return $Total_Energy / ($Daily_Production * $days * $Weather_Performance);
    }

    protected function getWeatherPerformance($Total_Insolation, $Daily_Insolation)
    {
        if (empty($Daily_Insolation)) {
            return 0;
        }

        $days = date("j");
        return $Total_Insolation / ($Daily_Insolation * $days);
    }

    protected function getGenMeterReading($prj)
    {
        $result = $this->dataService->getKWHREC($prj, 'DAILY');
        return $result;
    }

    protected function sendDailyReport($recepient, $body, $filename)
    {
        $mail = new \PHPMailer();

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
        } else {
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
