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

    protected $devices   = [];  // all devices
    protected $inverters = [];
    protected $envkits   = [];
    protected $genmeters = [];

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
    }

    public function initDevices($info)
    {
        $type = $info['type'];
        $code = $info['devcode'];

        switch (strtoupper($type)) {
        case 'INVERTER':
            $inverter = new Inverter($this, $info);
            $this->inverters[$code] = $inverter;
            $this->devices[$code] = $inverter;
            break;

        case 'ENVKIT':
            $envkit = new EnvKit($this, $info);
            $this->envkits[$code] = $envkit;
            $this->devices[$code] = $envkit;
            break;

        case 'GENMETER':
            $genmeter = new GenMeter($this, $info);
            $this->genmeters[$code] = $genmeter;
            $this->devices[$code] = $genmeter;
            break;

        default:
            throw new \InvalidArgumentException("Unknown device type '$type'");
            break;
        }
    }

    protected function getDb()
    {
        $di = \Phalcon\Di::getDefault();
        return $di->get('db');
    }

    public function getDevices()
    {
        return $this->devices;
    }

    public function getInverters()
    {
        return $this->inverters;
    }

    public function getFirstInverter()
    {
        return current($this->inverters);
    }

    public function getEnvKits()
    {
        return $this->envkits;
    }

    public function getFirstEnvKit()
    {
        return current($this->envkits);
    }

    public function getGenMeters()
    {
        return $this->genmeters;
    }

    public function getFirstGenMeter()
    {
        return current($this->genmeters);
    }

    public function getMonthlyBudget($year, $month)
    {
        $prj = $this->id;
        return $this->getDb()->fetchOne("SELECT * FROM monthly_budget
            WHERE project_id=$prj AND year=$year AND month=$month");
    }

    /**
     * @deprecated
     */
    public function getRefData($year, $month)
    {
        $prj = $this->id;
        return $this->getDb()->fetchOne("SELECT * FROM project_reference_data
            WHERE project_id=$prj AND year=$year AND month=$month");
    }

    public function getIRR($period)
    {
        $envkit = current($this->envkits);
        return $envkit->getIRR($period) / 12.0;
    }

    public function getTMP($period)
    {
        $envkit = current($this->envkits);
        return $envkit->getTMP($period) / 12.0;
    }

    public function getKW($period)
    {
        $genmeter = current($this->genmeters);
        return $genmeter->getKVA($period) / 12.0;

        //return $this->getKWH($period);
    }

    public function getAvgIRR($period)
    {
        $envkit = current($this->envkits);
        return $envkit->getAvgIRR($period);
    }

    public function getAvgTMP($period)
    {
        $envkit = current($this->envkits);
        return $envkit->getAvgTMP($period);
    }

    public function getAvgKW($period)
    {
        $genmeter = current($this->genmeters);
        return $genmeter->getAvgKVA($period);
    }

    public function getKWH($period)
    {
        $col = ($this->id == 7) ? 'del' : 'rec';
        $genmeter = current($this->genmeters);
        return $genmeter->getKWH($period, $col);
    }

    public function getChartData()
    {
        $envkit = current($this->envkits);
        $irr = $envkit->getChartData();

        $genmeters = current($this->genmeters);
        $kva = $envkit->getChartData();

        return [$irr, $kva];
    }

    /**
     * Latest
     */
    public function getLatestIRR()
    {
        $envkit = current($this->envkits);
        return $envkit->getLatestIRR();
    }

    public function getLatestKW()
    {
        $genmeter = current($this->genmeters);
        return $genmeter->getLatestKVA();
    }

    public function getLatestTime()
    {
        $envkit = current($this->envkits);
        return $envkit->getLatestTime();
    }

    /**
     * Snapshot
     */
    public function getSnapshotIRR()
    {
        $envkit = current($this->envkits);
        return $envkit->getSnapshotIRR();
    }

    public function getSnapshotKW()
    {
        $genmeter = current($this->genmeters);
        return $genmeter->getSnapshotKVA();
    }

    public function getSnapshotTime()
    {
        $envkit = current($this->genmeters);
        return $envkit->getSnapshotTime();
    }

    public function getGeneratingInverters()
    {
        // TODO: temp code
        return max(count($this->inverters), 1);
    }

    public function getCommunicatingDevices()
    {
        // TODO: temp code
        return count($this->devices);
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

        $Avg_Irradiance_POA       = $this->getAvgIRR('LAST-HOUR');
        $Avg_Module_Temp          = $this->getAvgTMP('LAST-HOUR');
        $Measured_Energy          = $this->getAvgKW('LAST-HOUR');

       #$Avg_Irradiance_POA       = $this->getAvgIRR('LAST-HOUR'); // avg 60 minutes
       #$Avg_Module_Temp          = $this->getAvgTMP('LAST-HOUR'); // PANELT
       #$Measured_Energy          = $this->getAvgKW('LAST-HOUR');  // sum 60 minutes

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
