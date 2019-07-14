<?php

namespace App\System;

class Project
{
    const GENMETERS = [7, 16, 17, 19, 20, 25, 28, 29];

    protected $id;
    protected $name;
    protected $ftpdir;
    protected $cbdir;
    protected $interval;
    protected $offset;

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
    protected $combiners = [];

    protected $cameras = [];

    public function __construct($info)
    {
        $this->id                     = $info['id'];
        $this->name                   = $info['name'];
        $this->ftpdir                 = $info['ftpdir'];
        $this->cbdir                  = $info['cbdir'];
        $this->interval               = $info['interval'];
        $this->offset                 = $info['offset'];
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

        case 'COMBINER':
            $combiner = new Combiner($this, $info);
            $this->combiners[$code] = $combiner;
            $this->devices[$code] = $combiner;
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

    public function getSizeAC()
    {
        return $this->capacityAC;
    }

    public function getSizeDC()
    {
        return $this->capacityDC;
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

    public function addCamera($camera)
    {
        $this->cameras[] = $camera;
    }

    public function getCameras()
    {
        return $this->cameras;

       #$prj = $this->id;
       #return $this->getDb()->fetchAll("SELECT * FROM project_camera WHERE project_id=$prj");
    }

    public function getLatestCameraPictures()
    {
        $pictures = [];

        $cameras = $this->getCameras();

        if ($cameras) {
            $prj = $this->id;
            foreach ($cameras as $camera) {
                $camid = $camera['id'];
                $sql = "SELECT * FROM camera_picture WHERE project_id=$prj AND camera_id=$camid ORDER BY id DESC";
                $picture = $this->getDb()->fetchOne($sql);
                $picture['camera'] = $camera['camera_name'];
                $pictures[] = $picture;
            }
        }

        return $pictures;
    }

    public function getIRR($period)
    {
        $envkit = current($this->envkits);
        return $envkit->getIRR($period) / $this->interval;
    }

    public function getTMP($period)
    {
        $envkit = current($this->envkits);
        return $envkit->getTMP($period) / $this->interval;
    }

    public function getKW($period)
    {
        return $this->getPower($period);

        //return $this->getKWH($period);
        //return $this->getKVA($period);
    }

    public function getPower($period)
    {
        $sum = 0;
        $inverters = $this->inverters;
        foreach ($inverters as $inverter) {
            $sum += $inverter->getKW($period);
        }
        return $sum / $this->interval;
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

    public function getAvgOAT($period)
    {
        $envkit = current($this->envkits);
        return $envkit->getAvgOAT($period);
    }

    public function getAvgKW($period)
    {
        $genmeter = current($this->genmeters);
        return $genmeter->getAvgKVA($period);
    }

    public function getKWH($period)
    {
        $col = in_array($this->id, self::GENMETERS) ? 'del' : 'rec';
        $genmeter = current($this->genmeters);
        return $genmeter->getKWH($period, $col);
    }

    public function getKVA($period)
    {
        $genmeter = current($this->genmeters);
        return $genmeter->getKVA($period) / $this->interval;
    }

    public function getChartData($date)
    {
        $envkit = current($this->envkits);
        $irr = $envkit->getChartData($date) + $this->getEmptyData($date);

        $kva = [];
        $inverters = $this->inverters;
        foreach ($inverters as $inverter) {
            $tmp = $inverter->getChartData($date);
            // [
            //     $time1 => [ $time1, $kw1 ]
            //     $time2 => [ $time2, $kw2 ]
            //     $time3 => [ $time3, $kw3 ]
            // ]
            foreach ($tmp as $time => $vals) {
                $time -= $time%60; // floor to minute (no seconds)
                if (isset($kva[$time])) {
                    $kva[$time][1] += $vals[1];
                } else {
                    $kva[$time] = $vals;
                }
            }
        }
        $kva = $kva + $this->getEmptyData($date);

        ksort($irr);
        ksort($kva);

        return [array_values($irr), array_values($kva)];
    }

    protected function getEmptyData($date)
    {
        $values = [];

        list($y, $m, $d) = explode('-', $date);
        $start = mktime(0, 0, 0, $m, $d, $y);
        for ($i = 0; $i < 24*3600/300; $i++) {
            $time = $start + $i*300;
            $values[$time] = [ $time*1000, 'NaN' ];
        }

        return $values;
    }

    public function export($params)
    {
        $result = [];

        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('1 day'));

        $interval  = empty($params['interval'])   ? 1         : $params['interval'];   // set interval=1 if not specified
        $startTime = empty($params['start-time']) ? $today    : $params['start-time'];
        $endTime   = empty($params['end-time'])   ? $tomorrow : $params['end-time'];

        if ($startTime == $endTime) {
            $endTime = date('Y-m-d', strtotime('1 day', strtotime($startTime)));
        }

        $result['project']   = $this->name;
        $result['interval']  = $interval == 'daily' ? "Daily" : "$interval Minutes";
        $result['starttime'] = $startTime;
        $result['endtime']   = $endTime;

        if ($interval == 'daily') {
            $interval = 24*60; // convert to minutes
        }

        foreach ($this->envkits as $envkit) {
            $result['envkits'] = $envkit->export($interval, $startTime, $endTime);
            break; // only first EnvKit
        }

        foreach ($this->genmeters as $genmeter) {
            $result['genmeters'] = $genmeter->export($interval, $startTime, $endTime);
            break; // only first GenMeter
        }

        foreach ($this->inverters as $inverter) {
            $result['inverters'][] = $inverter->export($interval, $startTime, $endTime);
        }

        $result['inverterCnt'] = count($this->inverters);

        $result['filename'] = BASE_DIR.'/tmp/export-'.str_replace(' ', '-', $this->name).'-'.date('Ymd-His').'.xlsx';

        return $result;
    }

    public function getDataToCompare($startTime, $endTime, $interval)
    {
        $envkitData = [];
        foreach ($this->envkits as $envkit) {
            $envkitData = $envkit->getDataToCompare($startTime, $endTime, $interval);
        }

        $genmeterData = [];
        foreach ($this->genmeters as $genmeter) {
            $col = in_array($this->id, self::GENMETERS) ? 'kwh_del' : 'kwh_rec';
            $genmeterData = $genmeter->getDataToCompare($startTime, $endTime, $interval, $col);
        }

        $inverterData = [];
        foreach ($this->inverters as $i => $inverter) {
            $inverterData[$i] = $inverter->getDataToCompare($startTime, $endTime, $interval);
        }

        $result = [];

        foreach ($envkitData as $time => $irr) {
            $key = substr($time, 0, 16); // remove second
            $result[$key]['irr'] = $irr;
        }

        foreach ($genmeterData as $time => $kwh) {
            $key = substr($time, 0, 16); // remove second
            $result[$key]['kwh'] = $kwh;
        }

        foreach ($inverterData as $data) {
            foreach ($data as $time => $kw) {
                $key = substr($time, 0, 16); // remove second
                if (isset($result[$key]['kw'])) {
                    $result[$key]['kw'] += $kw;
                } else {
                    $result[$key]['kw'] = $kw;
                }
            }
        }

        return $result;
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

    // only for project-37/38/39 right now
    public function getLatestCombiner()
    {
        $combiner = current($this->combiners);
        return $combiner->getLatestData();
    }

    /**
     * Snapshot
     */
    public function getSnapshotIRR()
    {
        $envkits = $this->envkits;
        foreach ($envkits as $envkit) {
            $irr = $envkit->getSnapshotIRR();
            if ($irr) {
                return $irr;
            }
        }
        return 0;
    }

    public function getSnapshotOAT()
    {
        $envkits = $this->envkits;
        foreach ($envkits as $envkit) {
            $oat = $envkit->getSnapshotOAT();
            if ($oat) {
                return $oat;
            }
        }
        return 0;
    }

    public function getSnapshotKW()
    {
        $inverters = $this->inverters;
        if (count($inverters) > 0) {
            $sum = 0;
            foreach ($inverters as $inverter) {
                $sum += $inverter->getSnapshotKW();
            }
            return $sum;
        }

        // get KW from genmeter if there is no inverter
        $genmeter = current($this->genmeters);
        return $genmeter->getSnapshotKVA();
    }

    public function getSnapshotTime()
    {
        $envkit = current($this->envkits);
        return $envkit->getSnapshotTime();
    }

    public function getTotalInverters()
    {
        return max(count($this->inverters), 1);
    }

    public function getGeneratingInverters()
    {
        $min30ago = gmdate('Y-m-d H:i:s', strtotime('-30 minutes'));

        $prj = $this->id;
        $sql = "SELECT data FROM latest_data WHERE project_id=$prj AND devtype='Inverter' AND time>'$min30ago'";
        $rows = $this->getDb()->fetchAll($sql);

        $cnt = 0;
        foreach ($rows as $row) {
            $json = $row['data'];
            $data = json_decode($json, true);
            if (isset($data['kw']) && $data['kw'] > 4) {
                $cnt++;
            } else if (isset($data['line_kw']) && $data['line_kw'] > 4) {
                $cnt++;
            }
        }

        if (count($this->inverters) == 0) {
            $cnt += 1;
        }

        return $cnt;
    }

    public function getTotalDevices()
    {
        // old code: return count($this->devices);
        return max(count($this->inverters), 1)
             + count($this->envkits)
             + count($this->genmeters);
    }

    public function getCommunicatingDevices()
    {
        $min30ago = gmdate('Y-m-d H:i:s', strtotime('-30 minutes'));

        $prj = $this->id;
        $sql = "SELECT count(*) AS cnt FROM latest_data WHERE project_id=$prj AND devtype<>'Combiner' AND time>'$min30ago'";
        $result = $this->getDb()->fetchOne($sql);

        $cnt = 0;

        if ($result) {
            $cnt = $result['cnt'];
            if (count($this->inverters) == 0) {
                $cnt += 1;
            }
        }

        return $cnt;
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
