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

        $row = 8;
        foreach ($data as $item) {
            $col = 0;
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['time']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['oat']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['panelt']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['irr']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['kva']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['kwh_del']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $item['kwh_rec']);

            foreach($item['kw'] as $kw) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $kw);
            }

            $row++;
        }

        $inverterCnt = $result['inverterCnt'];
        $maxCol = chr(ord('H') + $inverterCnt - 1);

        $filename = $result['filename'];
        $xlsWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $xlsWriter->save($filename);

        return $filename;
    }

    protected function reindexData($data)
    {
        $result = [];

        foreach ($data['envkits'] as $time => $vals) {
            $result[$time] = $vals;
        }
        foreach ($data['genmeters'] as $time => $vals) {
            $result[$time] = array_merge($result[$time], $vals);
        }
        foreach ($data['inverters'] as $inverter) {
            foreach ($inverter as $time => $vals) {
                $result[$time]['kw'][] = $vals['kw'];
            }
        }

        return $result;
    }

    protected function setSheetTitle($sheet, $info)
    {
        $sheet->setTitle('GCS Export');

        $sheet->setCellValue('A1', 'Project');
        $sheet->setCellValue('B1', $info['project']);

        $sheet->setCellValue('A2', 'Interval');
        $sheet->setCellValue('B2', $info['interval']);

        $sheet->setCellValue('A3', 'Start Time');
        $sheet->setCellValue('B3', $info['starttime']);

        $sheet->setCellValue('A4', 'End Time');
        $sheet->setCellValue('B4', $info['endtime']);

        $sheet->getStyle('A1:A4')->getFont()->setBold(true);

        $sheet->mergeCells('B6:D6');
        $sheet->mergeCells('E6:G6');

        $sheet->getStyle('B6:H6')->getFont()->setBold(true);
        $sheet->getStyle('B6:H6')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('B6', 'Weather Station');
        $sheet->setCellValue('E6', 'Gen Meter');
        $sheet->setCellValue('H6', 'Inverter (kW)');

        $sheet->setCellValue('A7', 'time (UTC)');
        $sheet->setCellValue('B7', 'OAT (Degrees C)');
        $sheet->setCellValue('C7', 'PANELT (Degrees C)');
        $sheet->setCellValue('D7', 'IRR (W/m^2)');
        $sheet->setCellValue('E7', 'kva (kVA)');
        $sheet->setCellValue('F7', 'kwh_del (kWh)');
        $sheet->setCellValue('G7', 'kwh_rec (kWh)');
       #$sheet->setCellValue('H7', '');

        $inverterCnt = $info['inverterCnt'];

        $col = 'H';
        for ($i = 1; $i <= $inverterCnt; $i++) {
            $sheet->setCellValue($col.'7', "Inverter $i");
            $col = chr(ord($col) + 1);
        }

        $maxCol = chr(ord('H') + $inverterCnt - 1);

        for ($i = 'A'; $i <= $maxCol; $i++) {
            $sheet->getColumnDimension($i)->setAutoSize(true);
        }

        $sheet->getStyle("A7:$maxCol".'7')->getFont()->setBold(true);

        $sheet->mergeCells("H6:$maxCol".'6');
       #$sheet->mergeCells("H7:$maxCol".'7');

        $sheet->getStyle("H6:$maxCol".'6')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H7:$maxCol".'7')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }

    protected function formatCells()
    {
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
