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

        $filename = $this->generateDailyReport();

        $users = $this->userService->getAll();

        foreach ($users as $user) {
            $email = $user['email'];
            $this->sendDailyReport($email, $filename);
        }

        $this->log("Daily report sending completed.\n");
    }

    protected function generateDailyReport()
    {
		$excel = PHPExcel_IOFactory::load("./templates/DailyReport-v1.xls");
		$excel->setActiveSheetIndex(0);  //set first sheet as active

		$sheet = $excel->getActiveSheet();
		$sheet->setCellValue("B3", date('F-d-Y'));

		$row = 10;
        $index = 1;

        $projects = $this->projectService->getAll();
        $devices  = $this->deviceService->getAll();

		foreach ($projects as $project) {
			/*
			Array
			(
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
			)
			*/
			$refdata = $this->dataService->getRefData($project['id'], date('Y'), date('m'));

			$project_Name        = $project['name'];
			$date                = date('d/m/Y', strtotime('yesterday'));
			$capacity_AC         = $project['AC_Nameplate_Capacity'];
			$capacity_DC         = $project['DC_Nameplate_Capacity'];;
			$budget              = $refdata['Stonebridge_Base']);
			$expected            = $refdata['Expected_Production'];
			$measured_Production = $refdata['Measured_Production'];
			$measured_Insolation = $refdata['Measured_Insolation'];
			$IE_POA_Insolation   = $refdata['IE_Snow_Loss_Estimate'];
			$actual_Budget       = $refdata['Actual_Production'];
			$actual_Expected     = $refdata['Actual_Production'];
			$weather_Performance = $refdata['Actual_Production'];

			$sheet->setCellValue("A$row", $index++);
			$sheet->setCellValue("B$row", $project_Name);
			$sheet->setCellValue("C$row", $date);
			$sheet->setCellValue("D$row", $capacity_AC);
			$sheet->setCellValue("E$row", $capacity_DC);
			$sheet->setCellValue("F$row", $budget);
			$sheet->setCellValue("G$row", $expected);
			$sheet->setCellValue("H$row", $measured_Production);
			$sheet->setCellValue("I$row", $measured_Insolation);
			$sheet->setCellValue("J$row", $IE_POA_Insolation);
			$sheet->setCellValue("K$row", $actual_Budget);
			$sheet->setCellValue("L$row", $actual_Expected);
			$sheet->setCellValue("M$row", $weather_Performance);
			$row++;
		}

		$today = date('Ymd');
		$filename = BASE_DIR . "/app/logs/DailyReport-$today.xls";

		//downloadable file is in Excel 2003 format (.xls)
		$xlsWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
		$xlsWriter->save($filename);

        return $filename;
    }

    protected function sendDailyReport($recepient, $filename)
    {
        $mail = new PHPMailer();

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->SMTPDebug = 3;
        $mail->isSMTP();
        $mail->Host = '10.6.200.200';
        $mail->Port = 25;
        $mail->SMTPAuth = false;
        $mail->SMTPSecure = false;

        //$filename = str_replace('\\', '/', $filename);

        // From email address and name
        $mail->From = "no-reply@greatcirclesolar.com";
        $mail->FromName = "Great Circle Solar";

        // To address and name
        $mail->addAddress($recepient);

        // Provide file path and name of the attachments
        $mail->addAttachment($filename, basename($filename));

        // Send HTML or Plain Text email
        $mail->isHTML(true);

        $today = date('Y-m-d');
        $mail->Subject = "Daily Solar Energy Production Report ($today)";
        $mail->Body = "Please find the <b>Daily Report</b> in attachment.";
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
