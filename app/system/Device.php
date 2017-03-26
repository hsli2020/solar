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

    protected function getTable()
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
        case 'HOURLY':
        case 'LAST-HOUR':
            // last hour
            $start = gmdate('Y-m-d H:00:00', strtotime('-1 hours'));
            $end   = gmdate('Y-m-d H:00:00');
            break;

        case 'DAILY':
        case 'YESTERDAY':
            // yesterday
            $yesterday = strtotime('-1 day');
            $start = gmdate('Y-m-d 00:00:00', $yesterday);
            $end   = gmdate('Y-m-d 23:59:59', $yesterday);
            break;

        case 'MONTH-TO-DATE':
            // month-to-date
            $start = gmdate('Y-m-01 00:00:00');
            $end   = gmdate('Y-m-d 00:00:00');

            // first day of the month, go back to last month
            if (date('d') == '01') {
                $start = gmdate('Y-m-01 00:00:00', strtotime('-1 month'));  // first day of last month
                #$end  = gmdate('Y-m-d 00:00:00',  strtotime('-1 day'));
                $end   = gmdate('Y-m-01 00:00:00');     // first day of current month
            }
            break;

        case 'LAST-MONTH':
            // last-month
            $start = gmdate('Y-m-01 00:00:00', strtotime('-1 month'));  // first day of last month
            #$end  = gmdate('Y-m-t 23:59:59',  strtotime('-1 month'));
            $end   = gmdate('Y-m-01 00:00:00');     // first day of current month
            break;

        case 'LATEST':
            // last minute (15 minutes ago)
            $start = gmdate('Y-m-d H:i:00', strtotime('-20 minute'));
            $end   = gmdate('Y-m-d H:i:30', strtotime('-19 minute'));
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

    protected function getLatestTime()
    {
    }
}
