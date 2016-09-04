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
            $prj = $this->request->getPost('prj');
            $dev = $this->request->getPost('dev');
            $col = $this->request->getPost('col');

           #$this->response->setJsonContent(['status' => 'ERROR', 'message' => 'Unknown column']);
           #$this->response->setJsonContent(['status' => 'OK']);

//$prj=1; $dev='mb-001'; $col='dcvolts';

            $sql = 'SELECT * FROM solar_device WHERE project_id=? AND devcode=? LIMIT 1';
            $result = $this->db->query($sql, array($prj, $dev));
            $row = $result->fetch();
            $table = $row['table'];

            $sql = "(SELECT `time`, $col FROM $table WHERE error=0 ORDER BY `time` DESC LIMIT 300) ORDER BY `time` ASC";
            $result = $this->db->query($sql);
            $data = [];
            while ($row = $result->fetch()) {
                $data[] = [strtotime($row['time'])*1000, floatval($row[$col])];
            }

            $this->response->setJsonContent(['status' => 'OK', 'data' => $data]);

            return $this->response;
        }
    }
}
