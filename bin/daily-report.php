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

			$sheet->setCellValue("A$row", $project['id']);
			$sheet->setCellValue("B$row", $project['name']);
			$sheet->setCellValue("C$row", date('d/m/Y', strtotime('yesterday')));
			$sheet->setCellValue("D$row", $project['DC_Nameplate_Capacity']);
			$sheet->setCellValue("E$row", $project['AC_Nameplate_Capacity']);
			$sheet->setCellValue("F$row", $refdata['Reference_Production']); // ??
			$sheet->setCellValue("G$row", $refdata['Expected_Production']); // ??
			$sheet->setCellValue("H$row", $refdata['Measured_Production']); // ??
			$sheet->setCellValue("I$row", $refdata['Measured_Insolation']); // ??
			$sheet->setCellValue("J$row", $refdata['IE_Snow_Loss_Estimate']); // ??
			$sheet->setCellValue("K$row", $refdata['Actual_Production']); // ??
			$sheet->setCellValue("L$row", $refdata['Actual_Production']); // ??
			$sheet->setCellValue("M$row", $refdata['Actual_Production']); // ??
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

       #$mail->SMTPDebug = 3;
        $mail->isSMTP();
        $mail->Host = '10.6.200.200';
        $mail->SMTPAuth = false;
       #$mail->Username = 'user@example.com';
       #$mail->Password = 'secret';
       #$mail->SMTPSecure = 'tls';
       #$mail->Port = 587;

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
