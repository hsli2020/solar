<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ExportService extends Injectable
{
    public function export($params)
    {
        $projectId = max(1, $params['project']); // set project=1 if not specified

        $project = $this->projectService->get($projectId);
        $result = $project->export($params);

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setTitle("export")->setDescription("none");
        $objPHPExcel->setActiveSheetIndex(0);

        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('GCS Export');

        $sheet->mergeCells('B1:D1');
        $sheet->mergeCells('E1:G1');

        $sheet->getStyle('B1:H1')->getFont()->setBold(true);
        $sheet->getStyle('B1:H1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('B1', 'Weather Station');
        $sheet->setCellValue('E1', 'Gen Meter');
        $sheet->setCellValue('H1', 'Inverter');

        $sheet->setCellValue('A2', 'time (UTC)');
        $sheet->setCellValue('B2', 'OAT (Degrees C)');
        $sheet->setCellValue('C2', 'PANELT (Degrees C)');
        $sheet->setCellValue('D2', 'IRR (W/m^2)');
        $sheet->setCellValue('E2', 'kva (kVA)');
        $sheet->setCellValue('F2', 'kwh_del (kWh)');
        $sheet->setCellValue('G2', 'kwh_rec (kWh)');
        $sheet->setCellValue('H2', 'kw (kW)');

        $sheet->getStyle('A2:H2')->getFont()->setBold(true);

        $fields = [ 11, 22, 33, 44 ];

        // Field names in the first row
        $row = 3;
        $col = 0;
        foreach ($fields as $field) {
            $sheet->setCellValueByColumnAndRow($col, $row, $field);
            $col++;
        }

        $filename = $result['filename'];
        $xlsWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $xlsWriter->save($filename);

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
