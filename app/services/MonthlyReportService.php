<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class MonthlyReportService extends Injectable
{
    protected $report;

    public function generate()
    {
        echo "Generating Monthly Report ...", EOL;

        $projects = $this->projectService->getAll();

        $this->report = [];
        foreach ($projects as $project) {
            $projectId = $project->id;
            $monthly = $project->getMonthlyBudget(date('Y'), date('m'));

            $Project_Name        = $project->name;
            $Date                = date('M-Y');
            $Monthly_Budget      = $monthly['Budget'];
            $IE_Insolation       = $monthly['IE_POA_Insolation'];

            $Insolation_Actual   = $this->getInsolationActual($project);
            $Insolation_Reference= $this->getInsolationReference($monthly);
            $Energy_Measured     = $this->getEnergyMeasured($project);
            $Energy_Expected     = $this->getEnergyExpected($Insolation_Actual, $Insolation_Reference, $Monthly_Budget);
            $Energy_Budget       = $this->getEnergyBudget($monthly);

            $Weather_Performance = $this->getWeatherPerformance($Energy_Expected, $Energy_Budget);
            $Actual_Budget       = $this->getActualBudget($Energy_Measured, $Energy_Budget);
            $Actual_Expected     = $this->getActualExpected($Energy_Measured, $Energy_Expected);

            $this->report[$projectId] = [
                'Project_Name'          =>  $Project_Name,
                'Date'                  =>  $Date,
                'Insolation_Actual'     =>  number_format($Insolation_Actual,    1, '.', ''),
                'Insolation_Reference'  =>  number_format($Insolation_Reference, 1, '.', ''),
                'Energy_Expected'       =>  number_format($Energy_Expected,      1, '.', ''),
                'Energy_Measured'       =>  number_format($Energy_Measured,      1, '.', ''),
                'Energy_Budget'         =>  number_format($Energy_Budget,        1, '.', ''),
                'Actual_Budget'         => (number_format($Actual_Budget,        3, '.', '')*100).'%',
                'Actual_Expected'       => (number_format($Actual_Expected,      3, '.', '')*100).'%',
                'Weather_Performance'   => (number_format($Weather_Performance,  3, '.', '')*100).'%',
            ];
        }

        $this->save();

        return $this->report;
    }

    public function save()
    {
        $filename = $this->getFilename(date('Ymd'));
        $json = json_encode($this->report, JSON_PRETTY_PRINT);
        file_put_contents($filename, $json);

        try {
            $this->db->insertAsDict('monthly_reports', [
                'month'  => date('Y-m'),
                'report' => $json,
            ]);
        } catch (\Exception $e) {
            echo $e->getMessage(), EOL;
        }
    }

    public function load($month)
    {
        $sql = "SELECT * FROM monthly_reports WHERE month='$month'";
        $result = $this->db->fetchOne($sql);
        if ($result) {
            return json_decode($result['report'], true);
        }
        return [];
    }

    public function send()
    {
        echo "Sending Monthly Report ...", EOL;

        $this->log('Start sending monthly report');

        $report = $this->load(date('Y-m', strtotime('-1 month')));

        $users = $this->userService->getAll();

        foreach ($users as $user) {
            if (strpos($user['email'], '@') === false) {
                $this->log("Skip sending monthly report to {$user['username']}, no email.");
                continue;
            }

            $filename = $this->generateXls($user, $report);
            $html = $this->generateHtml($user, $report);

            $this->sendMonthlyReport($user['email'], $html, $filename);
        }

        $this->log("Monthly report sending completed.\n");
    }

    protected function getFilename($date)
    {
        return BASE_DIR . "/app/logs/monthly-report-$date.json";
    }

    protected function generateXls($user, $report)
    {
        $report = $this->getUserSpecificReports($user, $report);

        $excel = \PHPExcel_IOFactory::load("./templates/MonthlyReport-v1.xlsx");
        $excel->setActiveSheetIndex(0);  //set first sheet as active

        $monthYear = date('F Y');
        $sheet = $excel->getActiveSheet();
        $sheet->setCellValue("B1", "MONTHLY REPORT SUMMARY\n$monthYear");

        $row = 5;

        foreach ($report as $data) {
            $sheet->setCellValue("B$row", $data['Project_Name']);
            $sheet->setCellValue("C$row", $data['Date']);
            $sheet->setCellValue("D$row", $data['Insolation_Actual']);
            $sheet->setCellValue("E$row", $data['Insolation_Reference']);
            $sheet->setCellValue("F$row", $data['Energy_Measured']);
            $sheet->setCellValue("G$row", $data['Energy_Expected']);
            $sheet->setCellValue("H$row", $data['Energy_Budget']);
            $sheet->setCellValue("I$row", $data['Actual_Budget']);
            $sheet->setCellValue("J$row", $data['Actual_Expected']);
            $sheet->setCellValue("K$row", $data['Weather_Performance']);
            $row++;
        }

        $month = date('Y-m');
        $filename = BASE_DIR . "/app/logs/MonthlyReport-$month.xlsx";

        $xlsWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $xlsWriter->save($filename);

        return $filename;
    }

    protected function generateHtml($user, $report)
    {
        $report = $this->getUserSpecificReports($user, $report);

        ob_start();
        $date = date('F, Y', strtotime('-1 month'));
        include("./templates/monthly-report.tpl");
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public function getUserSpecificReports($user, $report)
    {
        $result = [];

        $projects = $this->userService->getUserProjects($user['id']);

        foreach ($projects as $id) {
            if (isset($report[$id])) {
                $result[$id] = $report[$id];
            }
        }

        return $result;
    }

    protected function getInsolationActual($project)
    {
        return $project->getIRR('THIS-MONTH') / 60.0 / 1000.0;
    }

    protected function getInsolationReference($monthly)
    {
         return $monthly['IE_POA_Insolation'];
    }

    protected function getEnergyExpected($Insolation_Actual, $Insolation_Reference, $Monthly_Budget)
    {
        // ("MEASURED INSOLATION"/INSOLATION REFERENCE") * (REFERENCE PRODUCTION KWH)

        if ($Insolation_Reference == 0) {
            return 0;
        }

        return ($Insolation_Actual / $Insolation_Reference) * $Monthly_Budget;
    }

    protected function getEnergyMeasured($project)
    {
        return $project->getKW('THIS-MONTH') / 60.0;
    }

    protected function getEnergyBudget($monthly)
    {
        return $monthly['Budget'];
    }

    protected function getWeatherPerformance($Energy_Expected, $Energy_Budget)
    {
        // "EXPECTED KWH" / "REFERENCE KWH"

        if ($Energy_Budget == 0) {
            return 0;
        }

        return $Energy_Expected / $Energy_Budget;
    }

    protected function getActualBudget($Energy_Measured, $Energy_Budget)
    {
        // "MEASURED PRODUCTION" / "REFERENCE PRODUCTION"

        if ($Energy_Budget == 0) {
            return 0;
        }

        return $Energy_Measured / $Energy_Budget;
    }

    protected function getActualExpected($Energy_Measured, $Energy_Expected)
    {
        // "MEASURED PRODUCTION" / "EXPECTED PRODUCTION"

        if ($Energy_Expected == 0) {
            return 0;
        }

        return $Energy_Measured / $Energy_Expected;
    }

    protected function sendMonthlyReport($recepient, $body, $filename)
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
        $mail->Subject = "Monthly Solar Energy Production Report ($today)";
        $mail->Body = $body;
        $mail->AltBody = "Please find the Daily Report in attachment.";

        if (!$mail->send()) {
            $this->log("Mailer Error: " . $mail->ErrorInfo);
        } else {
            $this->log("Monthly report sent to $recepient.");
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
