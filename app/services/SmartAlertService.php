<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class SmartAlertService extends Injectable
{
    protected $alerts;

    public function run()
    {
        echo "Smart Alert is running ...", EOL;

        $this->alerts = [];

        $this->checkNoData();
       #$this->checkFault();

       #$this->checkLowEnergy();
       #$this->checkOverHeat();

       #$this->checkInverters();
       #$this->checkEnvkits();
       #$this->checkGenMeters();

        if ($this->alerts) {
            $this->saveAlerts();
            $this->sendAlerts();
        }
    }

    protected function checkNoData()
    {
        $sql = "SELECT * FROM latest_data";
        $rows = $this->db->fetchAll($sql);

        $now = time();
        foreach ($rows as $data) {
            $time = strtotime($data['time'].' UTC'); // UTC to LocalTime
            if ($time > 0 && $now - $time >= 35*60) {
                $this->alerts[] = [
                    'project' => $data['project_name'],
                    'time'    => date('Y-m-d H:i:s'),
                    'devtype' => $data['device'],
                    'devcode' => $data['device'],
                    'message' => 'No data received over 30 minutes',
                    'alert'   => '',
                   #'level'   => '',
                ];
            }
        }
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

    protected function sendAlerts()
    {
        $html = $this->generateHtml();
$cnt = 1;
        $users = $this->userService->getAll();
        foreach ($users as $user) {
            $this->sendEmail($user['email'], $html);
if ($cnt++ == 2) break;
        }
    }

    protected function sendEmail($recepient, $body)
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
        $mail->isHTML(true);
        $mail->Subject = "Smart Alert: Something is wrong, Take Action Right Now!";
        $mail->Body = $body;
        $mail->AltBody = "Smart Alert can only display in HTML format";

        if (!$mail->send()) {
            $this->log("Mailer Error: " . $mail->ErrorInfo);
        } else {
            $this->log("Smart Alert sent to $recepient.");
        }
    }

    protected function log($str)
    {
        $filename = BASE_DIR . '/app/logs/alert.log';
        $str = date('Y-m-d H:i:s ') . $str . "\n";

        echo $str;
        error_log($str, 3, $filename);
    }
}
