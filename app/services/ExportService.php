<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ExportService extends Injectable
{
    /**
     * @see doc/GCS-Export-Calc.txt
     */
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

        $col = 7; // 'H'
        $row = 7;
        for ($i = 1; $i <= $inverterCnt; $i++) {
            $sheet->setCellValueByColumnAndRow($col++, $row, "Inverter $i");
        }

        $maxCol = 7 + $inverterCnt - 1; // chr(ord('H') + $inverterCnt - 1);

        for ($i = 0; $i <= $maxCol; $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $sheet->mergeCellsByColumnAndRow(7, 6, $maxCol, 6);

        $sheet->getStyleByColumnAndRow(0, 7, $maxCol, 7)->getFont()->setBold(true);
        $sheet->getStyleByColumnAndRow(7, 6, $maxCol, 6)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A1:A4')->getFont()->setBold(true);
    }

    public function exportDaily($params)
    {
        $projectId = max(1, $params['project']); // set project=1 if not specified

        $project = $this->projectService->get($projectId);

        $startTime = $params['start-time'];
        $endTime   = $params['end-time'];

        if ($startTime == $endTime) {
            $endTime = date('Y-m-d', strtotime('1 day', strtotime($startTime)));
        }

        $excel = \PHPExcel_IOFactory::load(BASE_DIR."/job/templates/Daily-Export-Template.xlsx");
        $excel->setActiveSheetIndex(0);  //set first sheet as active
        $sheet = $excel->getActiveSheet();

        $sheet->setCellValue("B1", $project->name);
        $sheet->setCellValue("B3", $startTime);
        $sheet->setCellValue("B4", $endTime);

        $sql = "SELECT * FROM daily_reports WHERE date>='$startTime' AND date<='$endTime'";
        $rows = $this->db->fetchAll($sql);

        $row = 8;
        foreach ($rows as $data) {
            $json = json_decode($data['report'], true);
            if (!isset($json[$projectId])) {
                continue;
            }

            $report = $json[$projectId];
            $date = $report['Date']; // format: DD/MM/YYYY

            $ymd = date('Y-m-d', strtotime($date)); // format: YYYY-MM-DD
            $ambtmp = $project->getAvgOAT($ymd);
            $modtmp = $project->getAvgTMP($ymd);

            $col = 0;
            $sheet->setCellValueByColumnAndRow($col++, $row, $date);
            $sheet->setCellValueByColumnAndRow($col++, $row, $ambtmp);
            $sheet->setCellValueByColumnAndRow($col++, $row, $modtmp);
            $sheet->setCellValueByColumnAndRow($col++, $row, $report['Measured_Insolation']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $report['Daily_Expected']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $report['Measured_Production']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $report['Gen_Meter_Reading']);
            $row++;
        }

        $filename = BASE_DIR.'/tmp/exportdaily-'.str_replace(' ', '-', $project->name).'-'.date('Ymd-His').'.xlsx';
        $xlsWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $xlsWriter->save($filename);

        return $filename;
    }
}
