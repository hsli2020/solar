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

        $this->setSheetTitle($sheet, $result);

        $data = $this->reindexData($result);

        $row = 7;
        foreach ($data as $item) {
            $col = 0;
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['time']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['oat']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['panelt']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['irr']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['kva']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['kwh_del']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['kwh_rec']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['kw']);
            $row++;
        }

        for ($i = 'A'; $i <= 'H'; $i++) {
            $sheet->getColumnDimension($i)->setAutoSize(true);
        }

        $filename = $result['filename'];
        $xlsWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $xlsWriter->save($filename);

        return $filename;
    }

    protected function reindexData($data)
    {
        $result = [];

        foreach ($data['envkits'] as $time => $info) {
            $result[$time] = $info;
        }
        foreach ($data['genmeters'] as $time => $info) {
            $result[$time] = array_merge($result[$time], $info);
        }
        foreach ($data['inverters'] as $time => $info) {
            $result[$time] = array_merge($result[$time], $info);
        }

        return $result;
    }

    protected function setSheetTitle($sheet, $info)
    {
        $sheet->setTitle('GCS Export');

        $sheet->setCellValue('A1', 'Project');
        $sheet->setCellValue('B1', $info['project']);

        $sheet->setCellValue('A2', 'Start Time');
        $sheet->setCellValue('B2', $info['starttime']);

        $sheet->setCellValue('A3', 'End Time');
        $sheet->setCellValue('B3', $info['endtime']);

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        $sheet->mergeCells('B5:D5');
        $sheet->mergeCells('E5:G5');

        $sheet->getStyle('B5:H5')->getFont()->setBold(true);
        $sheet->getStyle('B5:H5')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('B5', 'Weather Station');
        $sheet->setCellValue('E5', 'Gen Meter');
        $sheet->setCellValue('H5', 'Inverter');

        $sheet->setCellValue('A6', 'time (UTC)');
        $sheet->setCellValue('B6', 'OAT (Degrees C)');
        $sheet->setCellValue('C6', 'PANELT (Degrees C)');
        $sheet->setCellValue('D6', 'IRR (W/m^2)');
        $sheet->setCellValue('E6', 'kva (kVA)');
        $sheet->setCellValue('F6', 'kwh_del (kWh)');
        $sheet->setCellValue('G6', 'kwh_rec (kWh)');
        $sheet->setCellValue('H6', 'kw (kW)');

        $sheet->getStyle('A6:H6')->getFont()->setBold(true);
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
