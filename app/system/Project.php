<?php

namespace App\System;

class Project
{
    protected $id;
    protected $name;
    protected $ftpdir;
    protected $desc;

    protected $capacityAC;
    protected $capacityDC;

    protected $modulePowerCoefficient = -0.43;
    protected $inverterEfficiency     = 0.98;
    protected $transformerLoss        = 0.015;
    protected $otherLoss              = 0.02;

    protected $inverters = [];
    protected $envkits   = [];
    protected $genmeters = [];

    protected $di;
    protected $db;

    public function __construct($info)
    {
        $this->id                     = $info['id'];
        $this->name                   = $info['name'];
        $this->ftpdir                 = $info['ftpdir'];
        $this->desc                   = $info['desc'];
        $this->capacityDC             = $info['DC_Nameplate_Capacity'];
        $this->capacityAC             = $info['AC_Nameplate_Capacity'];
        $this->modulePowerCoefficient = $info['Module_Power_Coefficient'];
        $this->inverterEfficiency     = $info['Inverter_Efficiency'];
        $this->transformerLoss        = $info['Transformer_Loss'];
        $this->otherLoss              = $info['Other_Loss'];

        $this->di = \Phalcon\Di::getDefault();
        $this->db = $this->di->get('db');
    }

    public function initDevices($info)
    {
        $type  = $info['type'];
        $code  = $info['devcode'];
        $table = $info['table'];
        $model = $info['model'];

        switch (strtoupper($type)) {
        case 'INVERTER':
            $this->inverters[] = new Inverter($this, $code, $table, $model);
            break;

        case 'ENVKIT':
            $this->envkits[] = new EnvKit($this, $code, $table, $model);
            break;

        case 'GENMETER':
            $this->genmeters[] = new GenMeter($this, $code, $table, $model);
            break;

        default:
            throw new \InvalidArgumentException("Unknown device type '$type'");
            break;
        }
    }

    public function getMonthlyBudget($year, $month)
    {
        $prj = $this->id;

        return $this->db->fetchOne("SELECT * FROM monthly_budget
            WHERE project_id=$prj AND year=$year AND month=$month");
    }

    /**
     * @deprecated
     */
    public function getRefData($year, $month)
    {
        $prj = $this->id;

        return $this->db->fetchOne("SELECT * FROM project_reference_data
            WHERE project_id=$prj AND year=$year AND month=$month");
    }

    public function __get($prop)
    {
        if (isset($this->$prop)) {
            return $this->$prop;
        }

        return null;
    }
}
