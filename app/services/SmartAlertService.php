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

        $this->checkLowEnergy();
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
        $alertType = 'NO-DATA';

        $sql = "SELECT * FROM latest_data";
        $rows = $this->db->fetchAll($sql);

        $now = time();
        foreach ($rows as $data) {
            if ($this->alertTriggered($data['project_id'], $alertType)) {
                continue;
            }
            $time = strtotime($data['time'].' UTC'); // UTC to LocalTime
            if ($time > 0 && $now - $time >= 35*60) {
                $this->alerts[] = [
                    'time'         => date('Y-m-d H:i:s'),
                    'project_id'   => $data['project_id'],
                    'project_name' => $data['project_name'],
                    'devtype'      => $data['devtype'],
                    'devcode'      => $data['devcode'],
                    'alert'        => $alertType,
                    'message'      => 'No data received over 30 minutes',
                ];
            }
        }
    }

    protected function checkLowEnergy()
    {
        $alertType = 'LOW-ENERGY';

        $projects = $this->projectService->getAll();

        foreach ($projects as $project) {
            if ($this->alertTriggered($project->id, $alertType)) {
                continue;
            }

            $irr = $project->getLatestIRR();
            $kw = $project->getLatestKW();

            if ($irr > 100 && $kw < 5) {
                $this->alerts[] = [
                    'time'         => date('Y-m-d H:i:s'),
                    'project_id'   => $project->id,
                    'project_name' => $project->name,
                    'devtype'      => '', // $data['devtype'],
                    'devcode'      => '', // $data['devcode'],
                    'alert'        => $alertType,
                    'message'      => 'Low energy while irradiance is great than 100',
                ];
            }
        }
    }

    protected function alertTriggered($projectId, $alertType)
    {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM smart_alert_log WHERE project_id=$projectId AND alert='$alertType' AND date(time)='$today'";
        $result = $this->db->fetchOne($sql);
        return $result;
    }

    protected function generateHtml($user)
    {
        $alerts = $this->getUserSpecificAlerts($user);

        ob_start();
        include("./templates/smart-alert.tpl");
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    protected function getUserSpecificAlerts($user)
    {
        if (!$user) {
            return $this->alerts;
        }

        $alerts = [];

        $projects = $this->userService->getUserProjects($user['id']);

        foreach ($this->alerts as $alert) {
            if (in_array($alert['project_id'], $projects)) {
                $alerts[] = $alert;
            }
        }

        return $alerts;
    }

    protected function saveAlerts()
    {
        // save to file
        $filename = BASE_DIR . "/app/logs/smart-alert.html";
        $html = $this->generateHtml(null);
        file_put_contents($filename, $html);

        // save to database
        foreach ($this->alerts as $alert) {
            try {
                $this->db->insertAsDict('smart_alert_log', [
                    'time'         => $alert['time'],
                    'project_id'   => $alert['project_id'],
                    'project_name' => $alert['project_name'],
                    'devtype'      => $alert['devtype'],
                    'devcode'      => $alert['devcode'],
                    'alert'        => $alert['alert'],
                    'message'      => $alert['message'],
                ]);
            } catch (\Exception $e) {
                echo $e->getMessage(), EOL;
            }
        }
    }

    protected function sendAlerts()
    {
        $users = $this->userService->getAll();

        foreach ($users as $user) {
            if ($user['id'] > 1) break;

            $html = $this->generateHtml($user);
            $this->sendEmail($user['email'], $html);
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
