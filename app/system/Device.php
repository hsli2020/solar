<?php

namespace App\System;

abstract class Device
{
    protected $project;
    protected $type;
    protected $code;
    protected $table;
    protected $model;
    protected $reference;

    public function __construct($project, $info)
    {
        $this->project   = $project;
        $this->type      = $info['type'];
        $this->code      = $info['devcode'];
        $this->table     = $info['table'];
        $this->model     = $info['model'];
        $this->reference = $info['reference'];
    }

    public function __toString()
    {
       #return 'P' .$this->project->id. ' ' .$this->type. ' ' .$this->code;
        return $this->type. ' ' .$this->code. ' of Project ' .$this->project->name;
    }

    protected function getDb()
    {
        $di = \Phalcon\Di::getDefault();
        return $di->get('db');
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getDeviceTable()
    {
        return 'p'.$this->project->id.'_'.
               str_replace('-', '_', $this->code).'_'.
               strtolower($this->type);
    }

    public function getTableColumns()
    {
        $table = $this->getTable();
        $columns = $this->getDb()->fetchAll("DESC $table");
        return array_column($columns, 'Field');
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
            $start = gmdate('Y-m-01 h:i:s', mktime(0, 0, 0)); // first day of this month
            $end   = gmdate('Y-m-01 h:i:s', mktime(23, 59, 59)); // first day of next month
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

        $sql = "SELECT * FROM latest_data WHERE project_id=$projectId AND devcode='$devcode'";
        $result = $this->getDb()->fetchOne($sql);

        return json_decode($result['data'], true);
    }

    public function getLatestTime()
    {
        $data = $this->getLatestData();
        return $data['time'];
    }

    public function getSnapshotData()
    {
        $table = $this->getDeviceTable();

        list($start, $end) = $this->getPeriod('SNAPSHOT');

        $sql = "SELECT * FROM $table WHERE time>='$start' AND error=0";
        $data = $this->getDb()->fetchOne($sql);

        return $data;
    }

    public function getSnapshotTime()
    {
        $data = $this->getSnapshotData();
        return $data ? toLocaltime($data['time']) : date('Y-m-d H:i:00', strtotime('-16 minute'));
    }

    public function export($file, $interval, $start, $end)
    {
        // nothing to do here
    }
}
