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
            $this->inverters[$code] = new Inverter($this, $code, $table, $model);
            break;

        case 'ENVKIT':
            $this->envkits[$code] = new EnvKit($this, $code, $table, $model);
            break;

        case 'GENMETER':
            $this->genmeters[$code] = new GenMeter($this, $code, $table, $model);
            break;

        default:
            throw new \InvalidArgumentException("Unknown device type '$type'");
            break;
        }
    }

    public function getDeviceCount()
    {
        return count($this->inverters) + count($this->envkits) + count($this->genmeters);
    }

    public function getInverters()
    {
        return $this->inverters;
    }

    public function getEnvKits()
    {
        return $this->envkits;
    }

    public function getGenMeters()
    {
        return $this->genmeters;
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

    public function getIRR($period)
    {
        $envkit = $this->envkits[0];
        return $envkit->getIRR($period);
    }

    public function getLatestIRR()
    {
        $envkit = $this->envkits[0];
        return $envkit->getLatestIRR($period);
    }

    public function getTMP($period)
    {
        $envkit = $this->envkits[0];
        return $envkit->getTMP($period);
    }

    public function getKW($period)
    {
        $sum = 0;
        foreach ($this->inverters as $inverter) {
            $sum += $inverter->getKW($period);
        }
        return $sum;
    }

    public function getLatestKW($period)
    {
        $sum = 0;
        foreach ($this->inverters as $inverter) {
            $sum += $inverter->getLatestKW($period);
        }
        return $sum;
    }

    public function __get($prop)
    {
        if (isset($this->$prop)) {
            return $this->$prop;
        }

        return null;
    }

    public function getPR()
    {
        $DC_Nameplate_Capacity    = $this->capacityDC;
        $AC_Nameplate_Capacity    = $this->capacityAC;

        $Module_Power_Coefficient = $this->modulePowerCoefficient;
        $Inverter_Efficiency      = $this->inverterEfficiency;
        $Transformer_Loss         = $this->transformerLoss;
        $Other_Loss               = $this->otherLoss;

        $Avg_Irradiance_POA       = $this->getIRR('HOURLY') / 60.0; // avg 60 minutes
        $Avg_Module_Temp          = $this->getTMP('HOURLY') / 60.0; // PANELT
        $Measured_Energy          = $this->getKW('HOURLY');        // sum 60 minutes

        if ($DC_Nameplate_Capacity == 0) return 0;

        $Maximum_Theory_Output = ($Avg_Irradiance_POA / 1000) * $DC_Nameplate_Capacity;

        if ($Maximum_Theory_Output == 0) return 0;

        $Temperature_Losses = ($Maximum_Theory_Output * ($Module_Power_Coefficient * (25 - $Avg_Module_Temp))) / 1000.0;
        $Inverter_Losses = (1 - $Inverter_Efficiency) * ($Maximum_Theory_Output - $Temperature_Losses);

        if (($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses) > $AC_Nameplate_Capacity) {
            $Inverter_Clipping_Loss = $Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $AC_Nameplate_Capacity;
        } else {
            $Inverter_Clipping_Loss = 0;
        }

        $Transformer_Losses  = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss) * $Transformer_Loss;
        $Other_System_Losses = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss - $Transformer_Loss) * $Other_Loss;
        $Total_Losses = ($Temperature_Losses + $Inverter_Losses + $Inverter_Clipping_Loss + $Transformer_Loss + $Other_System_Losses) / $Maximum_Theory_Output;
        $Theoretical_Output = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss - $Transformer_Loss - $Other_System_Losses);

        if ($Theoretical_Output == 0) return 0;

        $GCS_Performance_Index = $Measured_Energy / $Theoretical_Output;

        return $GCS_Performance_Index;
    }
}
