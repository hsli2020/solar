<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class SmartAlertService extends Injectable
{
    protected $alerts;

    public function run()
    {
        $projects = $this->projectService->getAll();

        $this->alerts = [];
        foreach ($projects as $project) {
            foreach ($project->devices as $device) {
                $this->checkNoData($project, $device);
            }
        }

        if ($this->alerts) {
            $this->saveAlerts();
           #$this->sendEmails();
        }
    }

    protected function checkNoData($project, $device)
    {
        $alert = [
            'project' => $project->name,
            'time'    => date('Y-m-d H:i:s'),
            'devtype' => $device->type,
            'devcode' => $device->code,
            'message' => 'No data over 30 minutes',
            'alert'   => '',
           #'level'   => '',
        ];

        $this->alerts[] = $alert;
    }

    protected function generateHtml()
    {
        $alerts = $this->alerts;

        ob_start();
        include("./templates/smart-alert.tpl");
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    protected function saveAlerts()
    {
        // save to file
        $filename = BASE_DIR . "/app/logs/smart-alert.html";
        $html = $this->generateHtml();
        file_put_contents($filename, $html);

        // save to database
        foreach ($this->alerts as $alert) {
            try {
                $this->db->insertAsDict('smart_alert_log', [
                    'time'    => $alert['time'],
                    'project' => $alert['project'],
                    'devtype' => $alert['devtype'],
                    'devcode' => $alert['devcode'],
                    'alert'   => $alert['alert'],
                    'message' => $alert['message'],
                ]);
            } catch (\Exception $e) {
                echo $e->getMessage(), EOL;
            }
        }
    }

    protected function sendEmails()
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
        $html = $this->generateHtml();

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
        $mail->Subject = "Smart Alert: Something is wrong, Take Action Right Now!";
        $mail->Body = $html;
        $mail->AltBody = "Smart Alert can only display in HTML format";

        if (!$mail->send()) {
            $this->log("Mailer Error: " . $mail->ErrorInfo);
        } else {
            $this->log("Daily report sent to $recepient.");
        }
    }
}
