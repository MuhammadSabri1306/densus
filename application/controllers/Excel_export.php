<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel_export extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('excel');
        // $this->load->library('user_log');
        // $this->load->library('auth_jwt');
        // $this->auth_jwt->cookieName = 'densus_export_token';
        // $this->load->library('session');
    }

    private function createDownload($spreadsheet, $filename)
    {
        $filename .= '.xls';

        // $writer = new Xlsx($spreadsheet);
        // ob_start();
        // $writer->save('php://output');
        // echo 'Hello ';
        // echo 'World';
        // $content = ob_get_contents();
        // ob_end_clean();

        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // header('Content-Disposition: attachment;filename="'. $filename .'"');
        // header('Cache-Control: max-age=0');
        // echo $content;

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        header('Content-Type: text/xls');
        header('Content-Disposition: attachment;filename="'. $filename .'"');
        $writer->save('php://output');
    }

    public function pue()
    {
        $filter = [
            'witel' => $this->input->get('witel'),
            'divre' => $this->input->get('divre'),
            'rtu' => $this->input->get('rtu')
        ];
        $filterDate = [
            'year' => $this->input->get('year'),
            'month' => $this->input->get('month')
        ];

        $filterYear = isset($filterDate['year']) ? $filterDate['year'] : date('Y');
        if(isset($filterDate['month'])) {
            $filterMonth = $filterDate['month'];

            $dateTime = new DateTime("$filterYear-$filterMonth-01");
            $startDate = $dateTime->format('Y-m-d H:i:s');

            $dateTime->modify('last day of this month');
            $endDate = $dateTime->format('Y-m-d H:i:s');
        } else {
            $dateTime = new DateTime("$filterYear-01-01");
            $startDate = $dateTime->format('Y-m-d H:i:s');

            $dateTime = new DateTime("$filterYear-12-31");
            $endDate = $dateTime->format('Y-m-d H:i:s');
        }

        $filter['startDate'] = $startDate;
        $filter['endDate'] = $endDate;

        $this->load->model('Pue_counter2_model');
        $data = $this->Pue_counter2_model->get_all($filter);
        
        $this->excel->setCellValue('B2', 'Mulai tanggal :');
        $this->excel->setCellValue('B3', $startDate);
        $this->excel->setCellValue('E2', 'Sampai tanggal :');
        $this->excel->setCellValue('E3', $endDate);

        $this->excel->setField([
            'divre_kode' => 'KODE DIVRE',
            'divre_name' => 'NAMA DIVRE',
            'witel_kode' => 'KODE WITEL',
            'witel_name' => 'NAMA WITEL',
            'rtu_kode' => 'KODE RTU',
            'rtu_name' => 'NAMA RTU',
            'sto_kode' => 'KODE STO',
            'lokasi' => 'LOKASI',
            'no_port' => 'NO. PORT',
            'pue_value' => 'NILAI PUE',
            'satuan' => 'SATUAN',
            'timestamp' => 'WAKTU'
        ]);
        
        $this->excel->fill($data, $startRow = 5, $startColumn = 1);
        $this->createDownload($this->excel->spreadsheet, 'DATA_PUE');
    }

    public function activity_performance()
    {
        $divre = $this->input->get('divre');
        $witel = $this->input->get('witel');
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        
        $this->load->library('datetime_range');
        if(!$year) $year = date('Y');
        if($month) {
            $datetime = $this->datetime_range->get_by_month($month, $year);
        } else {
            $datetime = $this->datetime_range->get_by_year($year);
        }

        $filter = compact('divre', 'witel', 'datetime');

        $this->load->model('activity_execution_model');
        $data = $this->activity_execution_model->get_performance_v3($filter);
        $monthList = isset($data['month_list']) ? $data['month_list'] : [];
        $categoryList = isset($data['category_list']) ? $data['category_list'] : [];
        $performanceList = isset($data['performance']) ? $data['performance'] : [];
        
        $this->excel->setCellAlignment(2, 3, 'left');
        $this->excel->setCellMergeValue(2, 3, 2, 5, 'Mulai tanggal :');
        $this->excel->setCellAlignment(3, 3, 'left');
        $this->excel->setCellMergeValue(3, 3, 3, 5, $datetime[0]);
        $this->excel->setCellAlignment(2, 6, 'left');
        $this->excel->setCellMergeValue(2, 6, 2, 8, 'Sampai tanggal :');
        $this->excel->setCellAlignment(3, 6, 'left');
        $this->excel->setCellMergeValue(3, 6, 3, 8, $datetime[1]);
        
        $this->excel->setCellMergeValue(5, 2, 6, 2, 'Lingkup Kerja');
        $this->excel->setColSizeAuto(2);

        $categoryCount = count($categoryList);
        $monthIndex = 0;
        foreach($monthList as $month) {

            for($i=0; $i<$categoryCount; $i++) {

                $categoryColIndex = ($monthIndex * $categoryCount) + $i + 3;
                $cellKey = $this->excel->getCellKey(6, $categoryColIndex);
                $this->excel->setCellValue($cellKey, $categoryList[$i]['alias']);
                $this->excel->setCellAlignment(6, $categoryColIndex, 'center');
                $this->excel->setColSizeAuto($categoryColIndex);

            }

            $monthStartColIndex = 3 + ($categoryCount * $monthIndex);
            $monthEndColIndex = $monthStartColIndex + $categoryCount - 1;
            switch($month) {
                case 1: $monthText = 'Januari'; break;
                case 2: $monthText = 'Februari'; break;
                case 3: $monthText = 'Maret'; break;
                case 4: $monthText = 'April'; break;
                case 5: $monthText = 'Mei'; break;
                case 6: $monthText = 'Juni'; break;
                case 7: $monthText = 'Juli'; break;
                case 8: $monthText = 'Agustus'; break;
                case 9: $monthText = 'September'; break;
                case 10: $monthText = 'November'; break;
                case 11: $monthText = 'Oktober'; break;
                case 12: $monthText = 'Desember'; break;
                default: $monthText = 'Bulan '.$month; break;
            }

            $this->excel->setCellMergeValue(5, $monthStartColIndex, 5, $monthEndColIndex, $monthText);
            $this->excel->setCellAlignment(5, $monthStartColIndex, 'center');

            $monthIndex++;
        }

        for($x=0; $x<count($performanceList); $x++) {

            $rowNumber = $x + 7;
            $locationName = $performanceList[$x]['location']['sto_name'];
            $cellKey = $this->excel->getCellKey($rowNumber, 2);
            $this->excel->setCellValue($cellKey, $locationName);
            for($y=0; $y<count($performanceList[$x]['item']); $y++) {

                $item = $performanceList[$x]['item'][$y];

                if($item['isExists']) {
                    $percentValue = $item['percent'];
                } else {
                    $percentValue = '';
                }

                $colNumber = 3 + $y;
                $cellKey = $this->excel->getCellKey($rowNumber, $colNumber);
                $this->excel->setCellValue($cellKey, $percentValue);
                $this->excel->setCellAlignment($colNumber, $colNumber, 'center');

            }
        }
        
        $this->createDownload($this->excel->spreadsheet, 'DATA_ACTIVITY_PERFORMANCE');
    }
}