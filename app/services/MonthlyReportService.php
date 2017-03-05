<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class MonthlyReportService extends Injectable
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
            $Date                = date('M-Y', strtotime('-1 month'));
            $Monthly_Budget      = $monthly['Budget'];
            $IE_Insolation       = $monthly['IE_POA_Insolation'];

            $Insolation_Actual   = $this->getInsolationActual();
            $Insolation_Reference= $this->getInsolationReference();
            $Energy_Expected     = $this->getEnergyExpected();
            $Energy_Measured     = $this->getEnergyMeasured();
            $Energy_Budget       = $this->getEnergyBudget();

            $Weather_Performance = $this->getWeatherPerformance();
            $Actual_Budget       = $this->getActualBudget();
            $Actual_Expected     = $this->getActualExpected();

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

        return $this->report;
    }

    public function send($debug = false)
    {
        $this->log('Start sending monthly report');

        $report = $this->report;

        $users = $this->userService->getAll();

        foreach ($users as $user) {
            $filename = $this->generateXls($user, $report);
            $html = $this->generateHtml($user, $report);

            if ($debug) {
                $uid = $user['id'];
                file_put_contents(BASE_DIR . "/app/logs/m-u-$uid.html", $html);
                continue;
            }

            $this->sendMonthlyReport($user['email'], $html, $filename);
        }

        $this->log("Monthly report sending completed.\n");
    }

    protected function generateXls($user, $report)
    {
        $report = $this->getUserSpecificReport($user, $report);

        $excel = \PHPExcel_IOFactory::load("./templates/MonthlyReport-v1.xlsx");
        $excel->setActiveSheetIndex(0);  //set first sheet as active

        $monthYear = date('F Y', strtotime('-1 month'));
        $sheet = $excel->getActiveSheet();
        $sheet->setCellValue("B1", "MONTHLY REPORT SUMMARY\n$monthYear");

        $row = 5;

        foreach ($report as $data) {
            $sheet->setCellValue("B$row", $data['Project_Name']);
            $sheet->setCellValue("C$row", $data['Date']);
            $sheet->setCellValue("D$row", $data['Insolation_Actual']);
            $sheet->setCellValue("E$row", $data['Insolation_Reference']);
            $sheet->setCellValue("F$row", $data['Energy_Expected']);
            $sheet->setCellValue("G$row", $data['Energy_Measured']);
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
        $report = $this->getUserSpecificReport($user, $report);

        ob_start();
        $date = date('F, Y', strtotime('-1 month'));
        include("./templates/monthly-report.tpl");
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

    protected function getInsolationActual()
    {
        return 0;
    }

    protected function getInsolationReference()
    {
        return 0;
    }

    protected function getEnergyExpected()
    {
        return 0;
    }

    protected function getEnergyMeasured()
    {
        return 0;
    }

    protected function getEnergyBudget()
    {
        return 0;
    }

    protected function getWeatherPerformance()
    {
        return 0;
    }

    protected function getActualBudget()
    {
        return 0;
    }

    protected function getActualExpected()
    {
        return 0;
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
