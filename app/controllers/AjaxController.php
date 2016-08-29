<?php

namespace App\Controllers;

class AjaxController extends ControllerBase
{
    public function initialize()
    {
        $this->view->disable();
    }

    public function dataAction()
    {
        if ($this->request->isPost()) {
            $stn = $this->request->getPost('stn');
            $dev = $this->request->getPost('dev');
            $col = $this->request->getPost('col');

           #$this->response->setJsonContent(['status' => 'ERROR', 'message' => 'Unknown column']);
           #$this->response->setJsonContent(['status' => 'OK']);

//$stn=1; $dev='mb-001'; $col='dcvolts';

            $sql = 'SELECT * FROM solar_device WHERE stn=? AND dev=? LIMIT 1';
            $result = $this->db->query($sql, array($stn, $dev));
            $row = $result->fetch();
            $table = $row['table'];

            $sql = "SELECT `time`, $col FROM $table WHERE error=0 ORDER BY `time` LIMIT 300";
            $result = $this->db->query($sql);
            $data = [];
            while ($row = $result->fetch()) {
                $data[] = [strtotime($row['time']), floatval($row[$col])];
            }

            $this->response->setJsonContent(['status' => 'OK', 'data' => $data]);

            return $this->response;
        }
    }
}
