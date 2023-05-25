<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class DailyReportService extends Injectable
{
    protected $report;

    public function generate()
    {
        echo "Generating Daily Report ...", EOL;

        $projects = $this->projectService->getAll();

        $this->report = [];
        foreach ($projects as $project) {
            $projectId = $project->id;

            if (in_array($projectId, [35, 49])) continue;

            $monthly = $project->getMonthlyBudget(date('Y'), date('m'));

            $Project_Name        = $project->name;
            $Date                = date('d/m/Y');
            $Capacity_AC         = $project->capacityAC;
            $Capacity_DC         = $project->capacityDC;;
            $Monthly_Budget      = $monthly['Budget']; // $this->getMonthlyBudget($monthly);
            $IE_Insolation       = $monthly['IE_POA_Insolation']; // $this->getIEInsolation($monthly);

            $Total_Insolation    = $this->getTotalInsolation($project);
            $Total_Energy        = $this->getTotalEnergy($project);

            $Measured_Insolation = $this->getMeasuredInsolation($project);
            $Measured_Production = $this->getMeasuredProduction($project);
            $Gen_Meter_Reading   = $this->getGenMeterReading($project);

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

#       unset($this->report[7]); // remove Norfolk from DailyReport

        $this->save();

        return $this->report;
    }

    public function save()
    {
        $json = json_encode($this->report, JSON_PRETTY_PRINT);

        if (0) {
            $filename = $this->getFilename(date('Ymd'));
            file_put_contents($filename, $json);
        }

        try {
            $this->db->insertAsDict('daily_reports', [
                'date'   => date('Y-m-d'),
                'report' => $json,
            ]);
        } catch (\Exception $e) {
            echo $e->getMessage(), EOL;
        }
    }

    public function load($date, $user = null)
    {
        $sql = "SELECT * FROM daily_reports WHERE date='$date'";
        $result = $this->db->fetchOne($sql);
        if ($result) {
            $report = json_decode($result['report'], true);
            $report = $this->getUserSpecificReports($user, $report);
            return $report;
        }
        return [];
    }

    public function send()
    {
        echo "Sending Daily Report ...", EOL;

        $this->log('Start sending daily report');

        $users = $this->userService->getAll();

        foreach ($users as $user) {
            if ($user['dailyReport'] == 0) {
                continue;
            }

            if (strpos($user['email'], '@') === false) {
                $this->log("Skip sending daily report to {$user['username']}, no email.");
                continue;
            }

            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $report = $this->load($yesterday, $user);

            $subject = "Daily Solar Energy Production Report ($yesterday)";
            $filename = $this->generateXls($user, $report, $yesterday);
            $body = $this->generateHtml($report);

            $this->sendDailyReport($user['email'], $subject, $body, $filename);
        }

        $this->log("Daily report sending completed.\n");
    }

    protected function getFilename($date)
    {
        return BASE_DIR . "/app/logs/daily-report-$date.json";
    }

    public function getTemplate($user)
    {
        $filename = BASE_DIR."/job/templates/DailyReport-v3.xlsx";
        if ($user['dailyReportTemplate']) {
            $template = $user['dailyReportTemplate'];
            $filename = BASE_DIR."/job/templates/$template.xlsx";
        }
        return $filename;
    }

    public function generateXls($user, $report, $date = null)
    {
        $template = $this->getTemplate($user);

        $excel = \PHPExcel_IOFactory::load($template);
        $excel->setActiveSheetIndex(0);  //set first sheet as active

        $sheet = $excel->getActiveSheet();
        if ($date) {
            $sheet->setCellValue("B3", date('F-d-Y', strtotime($date)));
        } else {
            // current system date
            $sheet->setCellValue("B3", date('F-d-Y'));
        }

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

        // Days in Month & Total Days
        if ($date) {
            $sheet->setCellValue("R3", date("t", strtotime($date)));
            $sheet->setCellValue("R4", date("j", strtotime($date)));
        } else {
            // current system date
            $sheet->setCellValue("R3", date("t"));
            $sheet->setCellValue("R4", date("j"));
        }

        $suffix = $date ? $date : date('Ymd');
        $filename = BASE_DIR . "/app/logs/DailyReport-$suffix.xlsx";

        $xlsWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $xlsWriter->save($filename);

        return $filename;
    }

    protected function generateHtml($report)
    {
        ob_start();
        $date = date('F d, Y', strtotime('yesterday'));
        include("./templates/daily-report.tpl");
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public function getUserSpecificReports($user, $report)
    {
        if (!$user) {
            return $report;
        }

        $result = [];

        $projects = $this->userService->getUserProjects($user['id']);

        foreach ($projects as $id) {
            if (isset($report[$id])) {
                $result[$id] = $report[$id];
            }
        }

        return $result;
    }

    public function getDateList()
    {
        $sql = "SELECT DISTINCT(`date`) FROM daily_reports ORDER BY `date` DESC LIMIT 30";
        $result = $this->db->fetchAll($sql);
        return array_column($result, 'date');
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

    protected function getTotalInsolation($project)
    {
        $result = $project->getIRR('MONTH-TO-DATE');
        return $result / 1000.0;
    }

    protected function getTotalEnergy($project)
    {
        $result = $project->getKWH('MONTH-TO-DATE');
        return $result;
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

    protected function getMeasuredInsolation($project)
    {
        $result = $project->getIRR('TODAY');
        return $result / 1000.0;
    }

    protected function getMeasuredProduction($project)
    {
        $result = $project->getKW('TODAY');
        return $result;
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

    protected function getGenMeterReading($project)
    {
        $result = $project->getKWH('TODAY');
        return $result;
    }

    protected function sendDailyReport($recepient, $subject, $body, $filename)
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
        $mail->Subject = $subject;
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

        if (file_exists($filename) && filesize($filename) > 128*1024) {
            unlink($filename);
        }

        $str = date('Y-m-d H:i:s ') . $str . "\n";

        echo $str;
        error_log($str, 3, $filename);
    }
}
