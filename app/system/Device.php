<?php

namespace App\System;

abstract class Device
{
    protected $project;
    protected $code;
    protected $table;
    protected $model;

    protected $di;
    protected $db;

    public function __construct($project, $code, $table, $model)
    {
        $this->project = $project;
        $this->code    = $code;
        $this->table   = $table;
        $this->model   = $model;

        $this->di = \Phalcon\Di::getDefault();
        $this->db = $this->di->get('db');
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getTableColumns()
    {
        $table = $this->getTable();
        $columns = $this->db->fetchAll("DESC $table");
        unset($columns[0]); // remove id
        unset($columns[1]); // remove project_id
        unset($columns[2]); // remove devcode
        return array_column($columns, 'Field');
    }

    public function getClassName()
    {
        $modelMap = [
            'solar_data_inverter_tcp'    => 'DataInverterTcp',
            'solar_data_inverter_serial' => 'DataInverterSerial',
            'solar_data_inverter_sma'    => 'DataInverterSma',
            'solar_data_inverter_pvp'    => 'DataInverterPvp',
            'solar_data_genmeter'        => 'DataGenMeters',
            'solar_data_envkit'          => 'DataEnvKits',
        ];

        $table = $this->getTable();

        return 'App\\Models\\'.$modelMap[$table];
    }

    protected function getPeriod($period)
    {
        switch (strtoupper($period)) {
        case 'LAST-HOUR':
            $start = gmdate('Y-m-d H:00:00', strtotime('-1 hours'));
            $end   = gmdate('Y-m-d H:00:00');
            break;

        case 'TODAY':
            $start = gmdate('Y-m-d h:i:s', mktime(0, 0, 0));
            $end   = gmdate('Y-m-d h:i:s', mktime(23, 59, 59));
            break;

        case 'MONTH-TO-DATE':
            $start = gmdate('Y-m-d h:i:s', mktime(0, 0, 0, date('n'), 1));
            $end   = gmdate('Y-m-d h:i:s', mktime(23, 59, 59));
            break;

        case 'THIS-MONTH':
            $start = gmdate('Y-m-01 h:i:s', mktime(0, 0, 0));
            $end   = gmdate('Y-m-t h:i:s', mktime(23, 59, 59));
            break;

        case 'SNAPSHOT':
            // last minute (15 minutes ago)
            $start = gmdate('Y-m-d H:i:00', strtotime('-16 minute'));
            $end   = gmdate('Y-m-d H:i:30', strtotime('-15 minute'));
            break;

        default:
            throw new \InvalidArgumentException("Bad argument '$period'");
            break;
        }

        return [ $start, $end ];
    }

    public function __get($prop)
    {
        if (isset($this->$prop)) {
            return $this->$prop;
        }

        return null;
    }

    public function getLatestData()
    {
        $projectId = $this->project->id;
        $devcode   = $this->code;
        $table     = $this->table;

        $sql = "SELECT *"
             . "  FROM $table"
             . " WHERE project_id=$projectId AND devcode='$devcode' AND time=("
             .        " SELECT MAX(time)"
             .        "   FROM $table"
             .        "  WHERE project_id=$projectId AND devcode='$devcode')";

        return $this->db->fetchOne($sql);
    }

    public function getLatestTime()
    {
        $data = $this->getLatestData();
        if ($data) {
            return $data['time'];
        }
        return false;
    }
}
