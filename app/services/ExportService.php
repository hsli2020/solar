<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ExportService extends Injectable
{
    public function export($params)
    {
        $projectId = max(1, $params['project']); // set project=1 if not specified

        $project = $this->projectService->get($projectId);
        $filename = $project->export($params);

        return $filename;
    }
}
/**
5 Minute Data

	Weather Station			
		time(UTC)	
		OAT (Degrees C)	    Average from: 11:25-11:29
		PANELT (Degrees C)	Average from: 11:25-11:29
		IRR (W/m^2)	        Average from: 11:25-11:29
	Gen Meter			
		kva (kVA)	    Average from: 11:25-11:29
		kwh_del (kWh)	Read whatever the current value is
		kwh_rec (kWh)	Read whatever the current value is
	Inverter
		kw (kW)         Average from: 11:25-11:31

15 Minute Data

	Weather Station | EnvKit mb-071			
		time(UTC)	
		OAT (Degrees C)	    Average from: 11:15-11:29
		PANELT (Degrees C)	Average from: 11:15-11:29
		IRR (W/m^2)	        Average from: 11:15-11:29
	Gen Meter | mb-011			
		kva (kVA)	    Average from: 11:15-11:29
		kwh_del (kWh)	Read whatever the current value is
		kwh_rec (kWh)	Read whatever the current value is
	Inverter | mb-031
		kw (kW)         Average from: 11:15-11:29

1 Hour Data

	Weather Station			
		time(UTC)	
		OAT (Degrees C)	    Add data points from: 11:00 to 11:59 and divide by 60
		PANELT (Degrees C)	Add data points from: 11:00 to 11:59 and divide by 60
		Insolation (wH/m^2)	Add data points from: 11:00 to 11:59 and divide by 60
	Gen Meter			
		kva (kVAH)	    Add data points from: 11:00 to 11:59 and divide by 60
		kwh_del (kWh)	Read whatever the current value is
		kwh_rec (kWh)	Read whatever the current value is
	Inverter
		kwh             Add data points from: 11:00 to 11:59 and divide by 60

Daily

	Weather Station			
		time(UTC)	
		OAT (Degrees C)	       Add data points from: 00:00 to 23:59 and divide by 1440
		PANELT (Degrees C)	   Add data points from: 00:00 to 23:59 and divide by 1440
		Insolation (wH/m^2)	   Add data points from: 00:00 to 23:59 and divide by 1440
	Gen Meter			
		Gen Meter Reading kWh	Subtract kWh received from 
		kwh_del (kWh)	Use same logic we use for the Daily Reports where we subtract the 
		                last reading of the meter and the first reading of the meter.
		kwh_rec (kWh)	Read whatever the current value is
	Inverter
		kwh				Add data points from: 00:00 to 23:59 and divide by 1440
*/
