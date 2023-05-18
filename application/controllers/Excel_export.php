<?php

class Excel_export extends CI_Controller
{
    private $colorScheme = [
        'white' => [
            'hex' => '#fff',
            'argb' => 'ffffffff'
        ],
        'primary' => [
            'hex' => '#24695c',
            'argb' => 'ff24695c'
        ],
        'gepee_exec_success' => [
            'hex' => '#2cb198',
            'argb' => 'ff2cb198'
        ],
        'gepee_exec_warning' => [
            'hex' => '#ffe55c',
            'argb' => 'ffffe55c'
        ],
        'gepee_exec_danger' => [
            'hex' => '#ff658d',
            'argb' => 'ffff658d'
        ],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('excel');
        // $this->load->library('user_log');
        // $this->load->library('auth_jwt');
        // $this->auth_jwt->cookieName = 'densus_export_token';
        // $this->load->library('session');
    }

    public function pue()
    {
        $filter = [
            'witel' => $this->input->get('witel'),
            'divre' => $this->input->get('divre'),
            'rtu' => $this->input->get('rtu')
        ];
        
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        
        $this->load->library('datetime_range');
        if(!$year) $year = date('Y');

        if($month) {
            $datetime = $this->datetime_range->get_by_month($month, $year);
        } else {
            $datetime = $this->datetime_range->get_by_year($year);
        }

        $filter['startDate'] = $datetime[0];
        $filter['endDate'] = $datetime[1];

        $this->load->model('Pue_counter2_model');
        $data = $this->Pue_counter2_model->get_all($filter);
        
        $this->excel->setValue('Mulai tanggal :', 2, 2);
        $this->excel->setValue($startDate, 2, 3);
        $this->excel->setValue('Sampai tanggal :', 3, 2);
        $this->excel->setValue($endDate, 3, 3);

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
        
        $this->excel
            ->selectCell([5, 1])
            ->fill($data);

        $this->excel->createDownload('Nilai PUE '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
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
        // dd_json($performanceList);
        
        $this->excel
            ->selectCell([2, 2], [2, 3])
            ->mergeCell()
            ->setAlignment('left')
            ->setValue('Mulai tanggal : '.$datetime[0], 2, 2);
        $this->excel
            ->selectCell([3, 2], [3, 3])
            ->mergeCell()
            ->setAlignment('left')
            ->setValue('Sampai tanggal : '.$datetime[1], 3, 2);
        
        $this->excel
            ->selectCell([5, 2], [6, 2])
            ->mergeCell()
            ->setValue('Regional', 5, 2);
        $this->excel
            ->selectCell([5, 3], [6, 3])
            ->mergeCell()
            ->setValue('Witel', 5, 3);
        $this->excel
            ->selectCell([5, 4], [6, 4])
            ->mergeCell()
            ->setValue('STO', 5, 4);
        $this->excel
            ->selectCell([5, 2], [6, 4])
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true)
            ->setAlignment('center')
            ->setWidthAuto();

        $this->load->library('Month_idn');
        $categoryCount = count($categoryList);
        $monthIndex = 0;
        $monthStartCol = 5;

        foreach($monthList as $month) {

            for($i=0; $i<$categoryCount; $i++) {

                $categoryColIndex = ($monthIndex * $categoryCount) + $i + $monthStartCol;
                $this->excel
                    ->setValue($categoryList[$i]['alias'], 6, $categoryColIndex)
                    ->setAlignment('center')
                    ->setFill($this->colorScheme['primary']['argb'])
                    ->setColor($this->colorScheme['white']['argb'])
                    ->setBorderColor($this->colorScheme['white']['argb'])
                    ->setBold(true)
                    ->setWidth(48, 'px');

            }
            
            $monthStartColIndex = $monthStartCol + ($categoryCount * $monthIndex);
            $monthEndColIndex = $monthStartColIndex + $categoryCount - 1;
            $monthText = Month_idn::getNameByNumber($month);
            if(!$monthText) {
                $monthText = 'Bulan '.$month;
            }

            $this->excel
                ->selectCell([5, $monthStartColIndex], [5, $monthEndColIndex])
                ->mergeCell()
                ->setBorderColor($this->colorScheme['white']['argb'])
                ->setAlignment('center')
                ->setFill($this->colorScheme['primary']['argb'])
                ->setColor($this->colorScheme['white']['argb'])
                ->setBold(true)
                ->setValue($monthText, 5, $monthStartColIndex);

            $monthIndex++;
        }

        for($x=0; $x<count($performanceList); $x++) {

            $rowNumber = $x + 7;
            
            $divreName = $performanceList[$x]['location']['divre_name'];
            $this->excel->setValue($divreName, $rowNumber, 2);

            $witelName = $performanceList[$x]['location']['witel_name'];
            $this->excel->setValue($witelName, $rowNumber, 3);
            
            $stoName = $performanceList[$x]['location']['sto_name'];
            $this->excel->setValue($stoName, $rowNumber, 4);

            for($y=0; $y<count($performanceList[$x]['item']); $y++) {

                $item = $performanceList[$x]['item'][$y];

                if($item['isExists']) {
                    $percentValue = $item['percent'];
                } else {
                    $percentValue = '';
                }

                $colNumber = $monthStartCol + $y;
                $this->excel
                    ->setValue($percentValue, $rowNumber, $colNumber)
                    ->setAlignment('center');

            }
        }
        
        $this->excel->createDownload('GEPEE Activity Performansi '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
    }

    public function activity_performance_test()
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
        $data = $this->activity_execution_model->get_performance_v3_test($filter);
        $monthList = isset($data['month_list']) ? $data['month_list'] : [];
        $categoryList = isset($data['category_list']) ? $data['category_list'] : [];
        $performanceList = isset($data['performance']) ? $data['performance'] : [];
        
        dd_json($performanceList);
    }

    public function activity_schedule()
    {
        $divre = $this->input->get('divre');
        $witel = $this->input->get('witel');
        $year = $this->input->get('year');
        $startMonth = $this->input->get('month');
        
        if(!$year) $year = date('Y');
        $endMonth = $startMonth ? $startMonth : date('m');
        if(!$startMonth) $startMonth = 1;
        
        $this->load->library('datetime_range');
        $datetime = $this->datetime_range->get_by_month_range($startMonth, $endMonth, $year);

        $filter = compact('divre', 'witel', 'datetime');
        $this->load->model('activity_schedule_model');
        $data = $this->activity_schedule_model->get_all_v2($filter);
        $monthList = isset($data['month_list']) ? $data['month_list'] : [];
        $categoryList = isset($data['category_list']) ? $data['category_list'] : [];
        $schedule = isset($data['schedule']) ? $data['schedule'] : [];
        
        $this->excel
            ->selectCell([2, 2], [2, 3])
            ->mergeCell()
            ->setAlignment('left')
            ->setValue('Mulai tanggal : '.$datetime[0], 2, 2);
        $this->excel
            ->selectCell([3, 2], [3, 3])
            ->mergeCell()
            ->setAlignment('left')
            ->setValue('Sampai tanggal : '.$datetime[1], 3, 2);
        
        $this->excel->useColSizeAuto = true;
        $this->excel
            ->selectCell([5, 2], [6, 2])
            ->mergeCell()
            ->setValue('Regional', 5, 2);
        $this->excel
            ->selectCell([5, 3], [6, 3])
            ->mergeCell()
            ->setValue('Witel', 5, 3);
        $this->excel
            ->selectCell([5, 4], [6, 4])
            ->mergeCell()
            ->setValue('STO', 5, 4);
        $this->excel
            ->selectCell([5, 2], [6, 4])
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true)
            ->setAlignment('center');

        $this->load->library('Month_idn');
        $categoryCount = count($categoryList);
        $monthIndex = 0;
        $monthStartCol = 5;

        foreach($monthList as $month) {

            for($i=0; $i<$categoryCount; $i++) {
                $categoryColIndex = ($monthIndex * $categoryCount) + $i + $monthStartCol;
                $this->excel
                    ->setValue($categoryList[$i]['alias'], 6, $categoryColIndex)
                    ->setAlignment('center')
                    ->setFill($this->colorScheme['primary']['argb'])
                    ->setColor($this->colorScheme['white']['argb'])
                    ->setBorderColor($this->colorScheme['white']['argb'])
                    ->setBold(true)
                    ->setWidth(48, 'px');
            }

            $monthStartColIndex = $monthStartCol + ($categoryCount * $monthIndex);
            $monthEndColIndex = $monthStartColIndex + $categoryCount - 1;
            $monthText = Month_idn::getNameByNumber($month);
            if(!$monthText) {
                $monthText = 'Bulan '.$month;
            }
            
            $this->excel
                ->selectCell([5, $monthStartColIndex], [5, $monthEndColIndex])
                ->mergeCell()
                ->setAlignment('center')
                ->setFill($this->colorScheme['primary']['argb'])
                ->setColor($this->colorScheme['white']['argb'])
                ->setBorderColor($this->colorScheme['white']['argb'])
                ->setBold(true)
                ->setValue($monthText, 5, $monthStartColIndex);

            $monthIndex++;
        }
        
        for($x=0; $x<count($schedule); $x++) {

            $rowNumber = $x + 7;
            
            $divreName = $schedule[$x]['location']['divre_name'];
            $this->excel->setValue($divreName, $rowNumber, 2);

            $witelName = $schedule[$x]['location']['witel_name'];
            $this->excel->setValue($witelName, $rowNumber, 3);
            
            $stoName = $schedule[$x]['location']['sto_name'];
            $this->excel->setValue($stoName, $rowNumber, 4);

            for($y=0; $y<count($schedule[$x]['month_item']); $y++) {

                $item = $schedule[$x]['month_item'][$y];
                for($z=0; $z<count($item['category_item']); $z++) {

                    $scheduleItem = $item['category_item'][$z]['schedule_data'];
                    $isExists = $scheduleItem && $scheduleItem['value'] ? true : false;
                    if($isExists) {
                        
                        $colNumber = $monthStartCol + (($y + 1) * $z);
                        $this->excel
                            ->setValue("V", $rowNumber, $colNumber)
                            ->setAlignment('center')
                            ->setFill($this->colorScheme['gepee_exec_success']['argb'])
                            ->setBorderColor($this->colorScheme['white']['argb']);

                    }
                }
            }
        }
        
        $this->excel->createDownload('GEPEE Activity Penjadwalan '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
    }

    public function activity_execution()
    {
        $divre = $this->input->get('divre');
        $witel = $this->input->get('witel');
        $year = $this->input->get('year');
        $startMonth = $this->input->get('month');
        
        if(!$year) $year = date('Y');
        $endMonth = $startMonth ? $startMonth : date('m');
        if(!$startMonth) $startMonth = 1;
        
        $this->load->library('datetime_range');
        $datetime = $this->datetime_range->get_by_month_range($startMonth, $endMonth, $year);

        $filter = compact('divre', 'witel', 'datetime');
        $this->load->model('activity_schedule_model');
        $data = $this->activity_schedule_model->get_all_v2($filter);
        $monthList = isset($data['month_list']) ? $data['month_list'] : [];
        $categoryList = isset($data['category_list']) ? $data['category_list'] : [];
        $schedule = isset($data['schedule']) ? $data['schedule'] : [];
        
        $this->excel
            ->selectCell([2, 2], [2, 3])
            ->mergeCell()
            ->setAlignment('left')
            ->setValue('Mulai tanggal : '.$datetime[0], 2, 2);
        $this->excel
            ->selectCell([3, 2], [3, 3])
            ->mergeCell()
            ->setAlignment('left')
            ->setValue('Sampai tanggal : '.$datetime[1], 3, 2);
        
        $this->excel->useColSizeAuto = true;
        $this->excel
            ->selectCell([5, 2], [6, 2])
            ->mergeCell()
            ->setValue('Regional', 5, 2);
        $this->excel
            ->selectCell([5, 3], [6, 3])
            ->mergeCell()
            ->setValue('Witel', 5, 3);
        $this->excel
            ->selectCell([5, 4], [6, 4])
            ->mergeCell()
            ->setValue('STO', 5, 4);
        $this->excel
            ->selectCell([5, 2], [6, 4])
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true)
            ->setAlignment('center');

        $this->load->library('Month_idn');
        $categoryCount = count($categoryList);
        $monthIndex = 0;
        $monthStartCol = 5;

        foreach($monthList as $month) {

            for($i=0; $i<$categoryCount; $i++) {
                $categoryColIndex = ($monthIndex * $categoryCount) + $i + $monthStartCol;
                $this->excel
                    ->setValue($categoryList[$i]['alias'], 6, $categoryColIndex)
                    ->setAlignment('center')
                    ->setFill($this->colorScheme['primary']['argb'])
                    ->setColor($this->colorScheme['white']['argb'])
                    ->setBorderColor($this->colorScheme['white']['argb'])
                    ->setBold(true)
                    ->setWidth(48, 'px');
            }

            $monthStartColIndex = $monthStartCol + ($categoryCount * $monthIndex);
            $monthEndColIndex = $monthStartColIndex + $categoryCount - 1;
            $monthText = Month_idn::getNameByNumber($month);
            if(!$monthText) {
                $monthText = 'Bulan '.$month;
            }
            
            $this->excel
                ->selectCell([5, $monthStartColIndex], [5, $monthEndColIndex])
                ->mergeCell()
                ->setAlignment('center')
                ->setFill($this->colorScheme['primary']['argb'])
                ->setColor($this->colorScheme['white']['argb'])
                ->setBorderColor($this->colorScheme['white']['argb'])
                ->setBold(true)
                ->setValue($monthText, 5, $monthStartColIndex);

            $monthIndex++;
        }
        
        for($x=0; $x<count($schedule); $x++) {

            $rowNumber = $x + 7;
            
            $divreName = $schedule[$x]['location']['divre_name'];
            $this->excel->setValue($divreName, $rowNumber, 2);

            $witelName = $schedule[$x]['location']['witel_name'];
            $this->excel->setValue($witelName, $rowNumber, 3);
            
            $stoName = $schedule[$x]['location']['sto_name'];
            $this->excel->setValue($stoName, $rowNumber, 4);

            for($y=0; $y<count($schedule[$x]['month_item']); $y++) {

                $item = $schedule[$x]['month_item'][$y];
                for($z=0; $z<count($item['category_item']); $z++) {

                    $scheduleItem = $item['category_item'][$z]['schedule_data'];
                    $isExists = $scheduleItem && $scheduleItem['value'] ? true : false;
                    if($isExists) {
                        
                        $colNumber = $monthStartCol + (($y + 1) * $z);
                        $isChecked = $scheduleItem['execution_count'] > 0;
                        $fillKey = null;
                        if($scheduleItem['execution_count'] < 1) {
                            $fillKey = 'gepee_exec_danger';
                        } elseif($scheduleItem['approved_count'] < $scheduleItem['execution_count']) {
                            $fillKey = 'gepee_exec_warning';
                        } else {
                            $fillKey = 'gepee_exec_success';
                        }

                        if($isChecked) {
                            $this->excel
                                ->setValue("V", $rowNumber, $colNumber)
                                ->setAlignment('center');
                        }
                        
                        if($fillKey) {
                            $this->excel
                                ->selectCell([$rowNumber, $colNumber])
                                ->setFill($this->colorScheme[$fillKey]['argb'])
                                ->setBorderColor($this->colorScheme['white']['argb']);
                        }

                    }
                }
            }
        }
        
        $this->excel->createDownload('GEPEE Activity Pelaksanaan '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
    }

    public function gepee_report()
    {
        $divreCode = $this->input->get('divre');
        $witelCode = $this->input->get('witel');
        $year = $this->input->get('year');
        $month = $this->input->get('month');

        if(!$year) $year = date('Y');
        // if(!$month) $month = date('m');

        $this->load->library('datetime_range');
        if($month) {
            $datetime = $this->datetime_range->get_by_month($month, $year);
        } else {
            $datetime = $this->datetime_range->get_by_year($year);
        }

        $filter = [
            'divre' => $divreCode,
            'witel' => $witelCode,
            'datetime' => $datetime
        ];

        $this->load->model('gepee_management_model');
        $this->load->model('activity_category_model');

        $pueLowLimit = 1.8;
        $categoryList = $this->activity_category_model->get();
        $dataGepee = $this->gepee_management_model->get_report($filter, $pueLowLimit);
        // $summaryGepeeNasional = null;
        // if(!isset($divreCode) && !isset($witelCode)) {
        //     $summaryGepeeNasional = $this->gepee_management_model->get_report_summary_nasional($filter, $pueLowLimit);
        // }

        $this->excel
            ->selectCell([2, 2], [2, 3])
            ->mergeCell()
            ->setAlignment('left')
            ->setValue('Mulai tanggal : '.$datetime[0], 2, 2);
        $this->excel
            ->selectCell([3, 2], [3, 3])
            ->mergeCell()
            ->setAlignment('left')
            ->setValue('Sampai tanggal : '.$datetime[1], 3, 2);
        
        $this->excel->useColSizeAuto = true;
        $startRow = 5; $currRow = $startRow;
        $startCol = 2; $currCol = $startCol;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('Regional', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('Witel', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('STO', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('ID Pelanggan', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [$currRow, ($currCol + 1)])
            ->mergeCell()
            ->setValue('PUE (Diukur Bulanan)', $currRow, $currCol);
        $this->excel->setValue('OFFLINE', ($currRow + 1), $currCol);
        $currCol++;
        $this->excel->setValue('ONLINE', ($currRow + 1), $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('OFFLINE/ONLINE', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('PUE <= 1.8', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('Tagihan PLN (Bulanan)', $currRow, $currCol);
        $currCol++;
        
        $this->excel
            ->selectCell([$currRow, $currCol], [$currRow, ($currCol + count($categoryList))])
            ->mergeCell()
            ->setValue('Presentase Pencapaian Aktivitas GePEE (Dihitung 100% jika sudah dilaksanakan)', $currRow, $currCol);
        // $currCol++;

        foreach($categoryList as $cat) {
            $this->excel->setValue($cat->alias, ($currRow + 1), $currCol);
            $currCol++;
        }
        
        $this->excel->setValue('Replacement', ($currRow + 1), $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('% GEPEE Activity', $currRow, $currCol);

        $currRow++;
        $this->excel
            ->selectCell([$startRow, $startCol], [$currRow, $currCol])
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true)
            ->setAlignment('center');
        
        $currRow++;
        foreach($dataGepee as $item) {

            $currCol = $startCol;

            $this->excel->setValue($item['location']['divre_name'], $currRow, $currCol);
            $currCol++;
            
            $this->excel->setValue($item['location']['witel_name'], $currRow, $currCol);
            $currCol++;

            $this->excel->setValue($item['location']['sto_name'], $currRow, $currCol);
            $currCol++;

            $this->excel->setValue($item['location']['id_pel_pln'], $currRow, $currCol);
            $currCol++;

            $this->excel->setValue($item['pue']['offline'], $currRow, $currCol);
            $currCol++;

            $this->excel->setValue($item['pue']['online'], $currRow, $currCol);
            $currCol++;

            $isOnlineText = $item['location']['is_online'] ? 'ONLINE' : 'OFFLINE';
            $this->excel
                ->setValue($isOnlineText, $currRow, $currCol)
                ->setAlignment('center');
            $currCol++;

            $isReachTargetText = $item['pue']['isReachTarget'] ? 'TIDAK' : 'YA';
            $this->excel
                ->setValue($isReachTargetText, $currRow, $currCol)
                ->setAlignment('center');
            // $currCol++;
            $currCol++;

            foreach($item['performance'] as $perf) {
                $currCol++;
                $percentText = (string) $perf['percentage'];
                $percentText .= "%";
                $this->excel
                    ->setValue($percentText, $currRow, $currCol)
                    ->setAlignment('center');
            }

            $currCol += 2;
            $percentText = (string) $item['performance_summary']['percentage'];
            $percentText .= "%";
            $this->excel
                ->setValue($percentText, $currRow, $currCol)
                ->setAlignment('center');

            $currRow++;

        }
        
        $this->excel->createDownload('GEPEE Management Report '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
    }

    public function get_opnimus_master_sto()
    {
        $this->load->model('opnimus_master_sto_model');
        $data = $this->opnimus_master_sto_model->get_master_lokasi_gepee_cross_data();
        
        $this->excel->setField([
            'divre_kode' => 'KODE DIVRE',
            'divre_name' => 'NAMA DIVRE',
            'witel_kode' => 'KODE WITEL',
            'witel_name' => 'NAMA WITEL',
            'datel' => 'DATEL',
            'sto_kode' => 'KODE STO',
            'sto_name' => 'NAMA STO'
        ]);
        
        $this->excel
            ->selectCell([1, 1])
            ->fill($data);

        $this->excel->createDownload('master_sto_densus '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
    }
}