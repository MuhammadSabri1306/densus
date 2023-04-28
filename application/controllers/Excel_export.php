<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel_export extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
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
        
        $this->load->library('excel');
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
}