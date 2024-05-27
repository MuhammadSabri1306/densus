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

    private function valueToText(
        $value,
        string $fallbackText = '-',
        string $patternText = '[[VAL]]',
        $customChecker = null
    )
    {
        $checker = $customChecker;
        if(!is_callable($checker)) {
            $checker = fn($val) => !empty($val);
        }

        if(!$checker($value)) {
            return $fallbackText;
        }
        return str_replace('[[VAL]]', $value, $patternText);
    }

    public function pue_online_monitoring()
    {
        $filter = [
            'witel' => $this->input->get('witel'),
            'divre' => $this->input->get('divre')
        ];

        $this->load->model('pue_counter2_model');
        $pueData = $this->pue_counter2_model->get_pue_monitoring_excel($filter);
        // dd_json($pue);
        
        $this->excel->useColSizeAuto = true;
        $startRow = 1; $currRow = $startRow;
        $startCol = 1; $currCol = $startCol;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('Kode Regional', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('Regional', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('Kode Witel', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('Witel', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('Kode RTU', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('RTU', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [$currRow, ($currCol + 2)])
            ->mergeCell()
            ->setValue('Rata-Rata PUE', $currRow, $currCol);
        $this->excel->setValue('Hari ini', ($currRow + 1), $currCol);
        $currCol++;
        $this->excel->setValue('Minggu ini', ($currRow + 1), $currCol);
        $currCol++;
        $this->excel->setValue('Bulan ini', ($currRow + 1), $currCol);
        
        $currRow++;
        $this->excel
            ->selectCell([$startRow, $startCol], [$currRow, $currCol])
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true)
            ->setAlignment('center');
        
        $currRow++;
        foreach($pueData as $item) {

            $currCol = $startCol;

            $this->excel->setValue($item['divre_kode'], $currRow, $currCol);
            $currCol++;
            $this->excel->setValue($item['divre_name'], $currRow, $currCol);
            $currCol++;
            
            $this->excel->setValue($item['witel_kode'], $currRow, $currCol);
            $currCol++;
            $this->excel->setValue($item['witel_name'], $currRow, $currCol);
            $currCol++;

            $this->excel->setValue($item['rtu_kode'], $currRow, $currCol);
            $currCol++;
            $this->excel->setValue($item['rtu_name'], $currRow, $currCol);
            $currCol++;
            
            $this->excel
                ->setValue($item['currDay'], $currRow, $currCol)
                ->setAlignment('center');
            // if($item[])
            // $this->excel->setFill($this->colorScheme['primary']['argb'])
            $currCol++;

            $this->excel
                ->setValue($item['currWeek'], $currRow, $currCol)
                ->setAlignment('center');
            $currCol++;

            $this->excel
                ->setValue($item['currMonth'], $currRow, $currCol)
                ->setAlignment('center');

            $currRow++;

        }

        $fileName = 'Nilai PUE';
        if($filter['witel']) {
            $fileName .= ' '.$filter['witel'];
        } elseif($filter['divre']) {
            $fileName .= ' '.$filter['divre'];
        }
        $fileName .= ' '.date('Y_m_d_\jH_\mi_\ds_\w\i\b');

        $this->excel->createDownload($fileName);
    }

    public function pue_rtu($rtuCode)
    {
        $zone = [];
        $this->load->library('datetime_range');
        $datetime = $this->datetime_range->get_by_year( date('Y') );

        $this->load->model('pue_counter2_model');
        $data = $this->pue_counter2_model->get_rtu_pue_hourly_excel($zone, $rtuCode);
        
        $this->excel->useColSizeAuto = true;
        $this->excel->setValue('Mulai tanggal :', 2, 2);
        $this->excel->setValue($datetime[0], 2, 3);
        $this->excel->setValue('Sampai tanggal :', 3, 2);
        $this->excel->setValue($datetime[1], 3, 3);

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

        $this->excel
            ->selectCell([5, 1], [5, 11])
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true)
            ->setAlignment('center')
            ->setWidthAuto();

        $this->excel->createDownload('Nilai PUE '.$rtuCode.' '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
    }

    public function pue_group_detail()
    {
        $filter = [
            'witel' => $this->input->get('witel'),
            'divre' => $this->input->get('divre')
        ];

        $this->load->library('datetime_range');
        $datetime = $this->datetime_range->get_by_year( date('Y') );

        $this->load->model('pue_counter2_model');
        $data = $this->pue_counter2_model->get_pue_hourly_excel($filter);
        
        $this->excel->useColSizeAuto = true;
        $this->excel->setValue('Mulai tanggal :', 2, 2);
        $this->excel->setValue($datetime[0], 2, 3);
        $this->excel->setValue('Sampai tanggal :', 3, 2);
        $this->excel->setValue($datetime[1], 3, 3);

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

        $this->excel
            ->selectCell([5, 1], [5, 11])
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true)
            ->setAlignment('center')
            ->setWidthAuto();

        $groupName = '';
        if($filter['witel']) {
            $groupName = '_witel_' . $filter['witel'] . '_';
        } else if($filter['divre']) {
            $groupName = '_divre_' . $filter['divre'] . '_';
        }
        $this->excel->createDownload('Nilai PUE '.$groupName.' '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
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
        $year = $this->input->get('year') ?? date('Y');
        $month = $this->input->get('month') ?? date('m');

        $this->load->library('datetime_range');
        $datetime = $this->datetime_range->get_by_month($month, $year);
        $datetimeYear = $this->datetime_range->get_by_year($year);

        $filter = [
            'divre' => $divreCode,
            'witel' => $witelCode,
            'datetime' => $datetime,
            'datetimeYear' => $datetimeYear,
            'year' => (int) $year,
            'month' => (int) $month,
        ];

        $this->load->model('gepee_management_model');
        $this->load->model('activity_category_model');
        
        $categoryList = $this->activity_category_model->get();
        $data = $this->gepee_management_model->get_report_v3($filter, false);

        $monthList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
            'Oktober', 'November', 'Desember'];
        $monthName = $monthList[$filter['month'] - 1];

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
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('Tipe Perhitungan', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue("IKE (bulan $monthName)", $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [$currRow, ($currCol + 1)])
            ->mergeCell()
            ->setValue("PUE (bulan $monthName)", $currRow, $currCol);
        $this->excel->setValue('Nilai PUE', ($currRow + 1), $currCol);
        $currCol++;
        $this->excel->setValue('PUE <= 1.8', ($currRow + 1), $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [$currRow, ($currCol + 2)])
            ->mergeCell()
            ->setValue('Tagihan PLN', $currRow, $currCol);
        $this->excel->setValue("Rp. Tagihan PLN ($monthName)", ($currRow + 1), $currCol);
        $currCol++;
        $this->excel->setValue('Jumlah Saving', ($currRow + 1), $currCol);
        $currCol++;
        $this->excel->setValue('% Saving', ($currRow + 1), $currCol);
        $currCol++;
        
        $this->excel
            ->selectCell([$currRow, $currCol], [$currRow, ($currCol + count($categoryList))])
            ->mergeCell()
            ->setValue('Presentase Pencapaian Aktivitas GePEE (Dihitung 100% jika sudah dilaksanakan)', $currRow, $currCol);

        foreach($categoryList as $cat) {
            $this->excel->setValue($cat->alias, ($currRow + 1), $currCol);
            $currCol++;
        }
        
        $this->excel->setValue('Replacement', ($currRow + 1), $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue("% GEPEE Activity ($monthName)", $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue("% GEPEE Activity ($year)", $currRow, $currCol);

        $currRow++;
        $this->excel
            ->selectCell([$startRow, $startCol], [$currRow, $currCol])
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true)
            ->setAlignment('center');

        $currRow++;
        foreach($data['gepee'] as $item) {

            $currCol = $startCol;

            $this->excel->setValue($item['location']['divre_name'], $currRow, $currCol);
            $currCol++;
            
            $this->excel->setValue($item['location']['witel_name'], $currRow, $currCol);
            $currCol++;

            $this->excel->setValue($item['location']['sto_name'], $currRow, $currCol);
            $currCol++;

            $this->excel->setValue($item['location']['id_pel_pln'], $currRow, $currCol);
            $currCol++;

            $typeText = $item['is_pue'] ? 'PUE' : ($item['is_ike'] ? 'IKE' : ''); 
            $this->excel
                ->setValue($typeText, $currRow, $currCol)
                ->setAlignment('center');
            $currCol++;

            $this->excel
                ->setValue(( $item['is_ike'] ? $item['ike'] : null ), $currRow, $currCol)
                ->setAlignment('center');
            $currCol++;

            $this->excel->setValue(( !$item['is_pue'] ? null : $item['pue']['online'] ), $currRow, $currCol);
            $currCol++;

            $isReachTargetText = !$item['is_pue'] ? null : ($item['pue']['isReachTarget'] ? 'YA' : 'TIDAK');
            $this->excel
                ->setValue($isReachTargetText, $currRow, $currCol)
                ->setAlignment('center');
            $currCol++;
            
            $this->excel->setValue($item['tagihan_pln'], $currRow, $currCol);
            $currCol++;

            if(!is_null($item['pln_saving'])) {
                $this->excel->setValue($item['pln_saving'], $currRow, $currCol);
            }
            $currCol++;

            if(!is_null($item['pln_saving_percent'])) {
                $this->excel
                    ->setValue($item['pln_saving_percent'], $currRow, $currCol)
                    ->setAlignment('center');
            }

            foreach($item['performance'] as $perf) {
                $currCol++;
                if(!is_null($perf['percentage'])) {
                    $this->excel
                        ->setValue($perf['percentage'], $currRow, $currCol)
                        ->setAlignment('center');
                }
            }

            $currCol += 2;
            $percentText = (string) $item['performance_summary'];
            $percentText .= "%";
            $this->excel
                ->setValue($percentText, $currRow, $currCol)
                ->setAlignment('center');
            $currCol++;

            $percentText = (string) $item['performance_summary_yearly'];
            $percentText .= "%";
            $this->excel
                ->setValue($percentText, $currRow, $currCol)
                ->setAlignment('center');

            $currRow++;

        }
        
        $this->excel->createDownload('GEPEE Management Report '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
    }

    public function gepee_report_v2()
    {
        $divreCode = $this->input->get('divre');
        $witelCode = $this->input->get('witel');
        $year = $this->input->get('year') ?? date('Y');
        $month = $this->input->get('month') ?? date('m');

        $this->load->library('datetime_range');
        $datetime = $this->datetime_range->get_by_month($month, $year);
        $datetimeYear = $this->datetime_range->get_by_year($year);

        $filter = [
            'divre' => $divreCode,
            'witel' => $witelCode,
            'datetime' => $datetime,
            'datetimeYear' => $datetimeYear,
            'year' => (int) $year,
            'month' => (int) $month,
        ];

        $this->load->model('gepee_management_model');
        $this->load->model('activity_category_model');
        
        $categoryList = $this->activity_category_model->get();
        $data = $this->gepee_management_model->get_report_v4($filter, false);

        $monthList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
            'Oktober', 'November', 'Desember'];
        $monthName = $monthList[$filter['month'] - 1];

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
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('Tipe Perhitungan', $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue("IKE (bulan $monthName)", $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [$currRow, ($currCol + 1)])
            ->mergeCell()
            ->setValue("PUE (bulan $monthName)", $currRow, $currCol);
        $this->excel->setValue('Nilai PUE', ($currRow + 1), $currCol);
        $currCol++;
        $this->excel->setValue('PUE <= 1.8', ($currRow + 1), $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [$currRow, ($currCol + 2)])
            ->mergeCell()
            ->setValue('Tagihan PLN', $currRow, $currCol);
        $this->excel->setValue("Rp. Tagihan PLN ($monthName)", ($currRow + 1), $currCol);
        $currCol++;
        $this->excel->setValue('Jumlah Saving', ($currRow + 1), $currCol);
        $currCol++;
        $this->excel->setValue('% Saving', ($currRow + 1), $currCol);
        $currCol++;
        
        $this->excel
            ->selectCell([$currRow, $currCol], [$currRow, ($currCol + count($categoryList))])
            ->mergeCell()
            ->setValue('Presentase Pencapaian Aktivitas GePEE (Dihitung 100% jika sudah dilaksanakan)', $currRow, $currCol);

        foreach($categoryList as $cat) {
            $this->excel->setValue($cat->alias, ($currRow + 1), $currCol);
            $currCol++;
        }
        
        $this->excel->setValue('Replacement', ($currRow + 1), $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue("% GEPEE Activity ($monthName)", $currRow, $currCol);
        $currCol++;

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue("% GEPEE Activity ($year)", $currRow, $currCol);

        $currRow++;
        $this->excel
            ->selectCell([$startRow, $startCol], [$currRow, $currCol])
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true)
            ->setAlignment('center');

        $currRow++;
        foreach($data['gepee'] as $item) {

            $currCol = $startCol;

            $this->excel->setValue($item['location']['divre_name'], $currRow, $currCol);
            $currCol++;
            
            $this->excel->setValue($item['location']['witel_name'], $currRow, $currCol);
            $currCol++;

            $this->excel->setValue($item['location']['sto_name'], $currRow, $currCol);
            $currCol++;

            $this->excel->setValue($item['location']['id_pel_pln'], $currRow, $currCol);
            $currCol++;

            $typeText = $item['is_pue'] ? 'PUE' : ($item['is_ike'] ? 'IKE' : ''); 
            $this->excel
                ->setValue($typeText, $currRow, $currCol)
                ->setAlignment('center');
            $currCol++;

            $this->excel
                ->setValue(( $item['is_ike'] ? $item['ike'] : null ), $currRow, $currCol)
                ->setAlignment('center');
            $currCol++;

            $this->excel->setValue(( !$item['is_pue'] ? null : $item['pue']['online'] ), $currRow, $currCol);
            $currCol++;

            $isReachTargetText = !$item['is_pue'] ? null : ($item['pue']['isReachTarget'] ? 'YA' : 'TIDAK');
            $this->excel
                ->setValue($isReachTargetText, $currRow, $currCol)
                ->setAlignment('center');
            $currCol++;
            
            $this->excel->setValue($item['tagihan_pln'], $currRow, $currCol);
            $currCol++;

            if(!is_null($item['pln_saving'])) {
                $this->excel->setValue($item['pln_saving'], $currRow, $currCol);
            }
            $currCol++;

            if(!is_null($item['pln_saving_percent'])) {
                $this->excel
                    ->setValue($item['pln_saving_percent'], $currRow, $currCol)
                    ->setAlignment('center');
            }

            foreach($item['performance'] as $perf) {
                $currCol++;
                if(!is_null($perf['percentage'])) {
                    $this->excel
                        ->setValue($perf['percentage'], $currRow, $currCol)
                        ->setAlignment('center');
                }
            }

            $currCol += 2;
            $percentText = (string) $item['performance_summary'];
            $percentText .= "%";
            $this->excel
                ->setValue($percentText, $currRow, $currCol)
                ->setAlignment('center');
            $currCol++;

            $percentText = (string) $item['performance_summary_yearly'];
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

    public function gepee_evidence()
    {
        $divreCode = $this->input->get('divre');
        $witelCode = $this->input->get('witel');
        $idLocation = $this->input->get('idLocation');
        $year = $this->input->get('year');
        $semester = $this->input->get('semester');

        if(!$year) $year = date('Y');
        if(!$semester) $semester = 1;
        
        $this->load->library('datetime_range');
        if($semester) {
            $datetime = $this->datetime_range->get_by_semester($semester, $year);
        } else {
            $datetime = $this->datetime_range->get_by_year($year);
        }

        $filter = [
            'divre' => $divreCode,
            'witel' => $witelCode,
            'idLocation' => $idLocation,
            'datetime' => $datetime
        ];

        $this->load->model('gepee_evidence_model');
        $data = $this->gepee_evidence_model->get_excel_data($filter);
        $gepeeEvd = $data['location_list'];
        $categoryList = $data['category'];
        
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
        $c = 0;
        foreach($categoryList as $cat) {
            if($cat['sub']) {
                $this->excel
                    ->selectCell([$currRow, $currCol], [$currRow, ($currCol + count($cat['sub']) - 1)])
                    ->mergeCell()
                    ->setValue($cat['category'], $currRow, $currCol);
                for($i=0; $i<count($cat['sub']); $i++) {
                    $this->excel->setValue($i + 1, ($currRow + 1), $currCol);
                    $currCol++;
                }
                
            } else {
                
                $this->excel
                    ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
                    ->mergeCell()
                    ->setValue($cat['category'], $currRow, $currCol);
                $currCol++;

            }
        }

        $this->excel
            ->selectCell([$currRow, $currCol], [($currRow + 1), $currCol])
            ->mergeCell()
            ->setValue('Persentase (%)', $currRow, $currCol);

        $currRow++;
        $this->excel
            ->selectCell([$startRow, $startCol], [$currRow, $currCol])
            ->setAlignment('center')
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true);

        foreach($gepeeEvd as $item) {
            
            $currCol = $startCol;
            $currRow++;
            $this->excel->setValue($item['location']['divre_name'], $currRow, $currCol);
            
            $currCol++;
            $this->excel->setValue($item['location']['witel_name'], $currRow, $currCol);
            
            $currCol++;
            foreach($item['category_data'] as $catItem) {
                if($catItem['use_target']) {
                    for($i=0; $i<count($catItem['checklist']); $i++) {
                        if($catItem['checklist'][$i]) {
                            $this->excel
                                ->setValue('V', $currRow, $currCol)
                                ->setAlignment('center')
                                ->setFill($this->colorScheme['gepee_exec_success']['argb'])
                                ->setBorderColor($this->colorScheme['white']['argb']);
                        }
                        $currCol++;
                    }
                } else {
                    $this->excel
                        ->setValue($catItem['count'], $currRow, $currCol)
                        ->setAlignment(true)
                        ->setAlignment('center');
                    $currCol++;
                }
            }
            
            $this->excel->setValue($item['summary']['percentage'], $currRow, $currCol);

        }

        // if($cat['sub']) {

        //     $currCol++;
        //     $this->excel
        //         ->selectCell([$currRow, $currCol], [$currRow, ($currCol + count($cat['sub']) - 1)])
        //         ->mergeCell()
        //         ->setValue($cat['category'], $currRow, $currCol);
        //     for($i=0; $i<count($cat['checklis']); $i++) {
        //         $isCheck = $cat['checklis'][$i];
        //         if($isCheck) {
        //             $this->excel
        //                 ->setValue('V', ($currRow + 1), $currCol + $i)
        //                 ->setAlignment('center')
        //                 ->setFill($this->colorScheme['primary']['argb'])
        //                 ->setColor($this->colorScheme['white']['argb'])
        //                 ->setBorderColor($this->colorScheme['white']['argb'])
        //                 ->setBold(true);
        //         }
        //     }

        // }
        $this->excel->createDownload('GEPEE Evidence '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
    }

    public function pi_laten_gepee()
    {
        $divre = $this->input->get('divre');
        $witel = $this->input->get('witel');
        $year = (int) ($this->input->get('year') ?? date('Y'));
        $month = (int) ($this->input->get('month') ?? date('n'));

        $filter = compact('divre', 'witel', 'year', 'month');
        $this->load->model('pi_laten_model');
        $piLaten = $this->pi_laten_model->get_gepee_v2($filter);

        $yearSrc = $piLaten['years']['source'];
        $yearCmp = $piLaten['years']['comparison'];

        $monthList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
            'Oktober', 'November', 'Desember'];
        $targetMonthName = $monthList[$piLaten['filter_month'] - 1];
        $startMonthCol = substr($monthList[0], 0, 3);
        $endMonthCol = substr($targetMonthName, 0, 3);

        $this->excel
            ->selectCell([2, 2], [2, 3])
            ->mergeCell()
            ->setAlignment('left')
            ->setValue("Target : $targetMonthName Tahun $yearSrc", 2, 2);
        
        $this->excel->useColSizeAuto = true;
        $startRow = 5; $currRow = $startRow;
        $startCol = 2; $currCol = $startCol;

        $this->excel->setValue('Regional', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('Witel', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('STO', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('ID Pelanggan PLN', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("Full Tahun $yearCmp", $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("$startMonthCol - $endMonthCol $yearCmp", $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("$startMonthCol - $endMonthCol $yearSrc", $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("Presentasi Kontribusi Tagihan $yearCmp", $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('Target Saving', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("Saving $startMonthCol - $endMonthCol Tahun $yearSrc - $yearCmp", $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('Selisih Target & Saving', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("CER $endMonthCol (Real)", $currRow, $currCol);

        $this->excel
            ->selectCell([$startRow, $startCol], [$currRow, $currCol])
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true)
            ->setAlignment('center');
            
        foreach($piLaten['sto_list'] as $item) {
            
            $currRow++;
            $currCol = $startCol;
            $this->excel->setValue($item['location']['divre_name'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['location']['witel_name'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['location']['sto_name'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['location']['id_pel_pln'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['bill']['cmp_full'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['bill']['cmp'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['bill']['src'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['bill_fraction'] / 100, $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['saving']['target'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['saving']['value'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['saving']['achievement'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['cer']['src_real'] / 100, $currRow, $currCol);

        }

        $this->excel
            ->selectCell([$startRow + 1, 5], [$currRow, 5])
            ->getCellStyle()
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

        $this->excel
            ->selectCell([$startRow + 1, 6], [$currRow, 8])
            ->getCellStyle()
            ->getNumberFormat()
            ->setFormatCode(\Excel::CUSTOM_NUMBER_FORMAT_CURRENCY_IDR);

        $this->excel
            ->selectCell([$startRow + 1, 9], [$currRow, 9])
            ->getCellStyle()
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

        $this->excel
            ->selectCell([$startRow + 1, 10], [$currRow, 12])
            ->getCellStyle()
            ->getNumberFormat()
            ->setFormatCode(\Excel::CUSTOM_NUMBER_FORMAT_CURRENCY_IDR);

        $this->excel
            ->selectCell([$startRow + 1, 13], [$currRow, 13])
            ->getCellStyle()
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);


        $condStyleGreen = new PhpOffice\PhpSpreadsheet\Style\Style(false, true);
        $condStyleGreen->getFont()
            ->getColor()
            ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN);
        $condStyleGreen->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('ffc6efce');
        $condStyleGreen->getFill()
            ->getEndColor()
            ->setARGB('ffc6efce');

        $condStyleRed = new PhpOffice\PhpSpreadsheet\Style\Style(false, true);
        $condStyleRed->getFont()
            ->getColor()
            ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
        $condStyleRed->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('ffffc7ce');
        $condStyleRed->getFill()
            ->getEndColor()
            ->setARGB('ffffc7ce');

        $condStyleSavingVal = $this->excel
            ->selectCell([$startRow + 1, 11], [$currRow, 11])
            ->getCellStyle()
            ->getConditionalStyles();

        $condSavingVal1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $condSavingVal1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $condSavingVal1->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
        $condSavingVal1->addCondition(0);
        $condSavingVal1->setStyle($condStyleGreen);
        $condSavingVal1->setStopIfTrue(false);
        array_push($condStyleSavingVal, $condSavingVal1);

        $condSavingVal0 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $condSavingVal0->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $condSavingVal0->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHANOREQUAL);
        $condSavingVal0->addCondition(0);
        $condSavingVal0->setStyle($condStyleRed);
        $condSavingVal0->setStopIfTrue(true);
        array_push($condStyleSavingVal, $condSavingVal0);

        $this->excel
            ->selectCell([$startRow + 1, 11], [$currRow, 11])
            ->getCellStyle()
            ->setConditionalStyles($condStyleSavingVal);


        $condStyleSavingDiff = $this->excel
            ->selectCell([$startRow + 1, 12], [$currRow, 12])
            ->getCellStyle()
            ->getConditionalStyles();

        $condSavingDiff0 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $condSavingDiff0->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $condSavingDiff0->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHANOREQUAL);
        $condSavingDiff0->addCondition(0);
        $condSavingDiff0->setStyle($condStyleGreen);
        $condSavingDiff0->setStopIfTrue(false);
        array_push($condStyleSavingDiff, $condSavingDiff0);

        $condSavingDiff1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $condSavingDiff1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $condSavingDiff1->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHAN);
        $condSavingDiff1->addCondition(0);
        $condSavingDiff1->setStyle($condStyleRed);
        $condSavingDiff1->setStopIfTrue(true);
        array_push($condStyleSavingDiff, $condSavingDiff1);

        $this->excel
            ->selectCell([$startRow + 1, 12], [$currRow, 12])
            ->getCellStyle()
            ->setConditionalStyles($condStyleSavingDiff);


        $this->excel->createDownload('PI Laten (GePEE Location) '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
    }

    public function pi_laten_amc()
    {
        $divre = $this->input->get('divre');
        $witel = $this->input->get('witel');
        $year = (int) ($this->input->get('year') ?? date('Y'));
        $month = (int) ($this->input->get('month') ?? date('n'));

        $filter = compact('divre', 'witel', 'year', 'month');
        $this->load->model('pi_laten_model');
        $piLaten = $this->pi_laten_model->get_amc_v2($filter);

        $yearSrc = $piLaten['years']['source'];
        $yearCmp = $piLaten['years']['comparison'];

        $monthList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
            'Oktober', 'November', 'Desember'];
        $targetMonthName = $monthList[$piLaten['filter_month'] - 1];
        $startMonthCol = substr($monthList[0], 0, 3);
        $endMonthCol = substr($targetMonthName, 0, 3);

        $this->excel
            ->selectCell([2, 2], [2, 3])
            ->mergeCell()
            ->setAlignment('left')
            ->setValue("Target : $targetMonthName Tahun $yearSrc", 2, 2);
        
        $this->excel->useColSizeAuto = true;
        $startRow = 5; $currRow = $startRow;
        $startCol = 2; $currCol = $startCol;

        $this->excel->setValue('Regional', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('Witel', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('Nama Pelanggan PLN', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('Alamat', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('Lokasi', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('ID Pelanggan PLN', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("Full Tahun $yearCmp", $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("$startMonthCol - $endMonthCol $yearCmp", $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("$startMonthCol - $endMonthCol $yearSrc", $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("Presentasi Kontribusi Tagihan $yearCmp", $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('Target Saving', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("Saving $startMonthCol - $endMonthCol Tahun $yearSrc - $yearCmp", $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue('Selisih Target & Saving', $currRow, $currCol);
        
        $currCol++;
        $this->excel->setValue("CER $endMonthCol (Real)", $currRow, $currCol);

        $this->excel
            ->selectCell([$startRow, $startCol], [$currRow, $currCol])
            ->setFill($this->colorScheme['primary']['argb'])
            ->setColor($this->colorScheme['white']['argb'])
            ->setBorderColor($this->colorScheme['white']['argb'])
            ->setBold(true)
            ->setAlignment('center');
            
        foreach($piLaten['sto_list'] as $item) {
            
            $currRow++;
            $currCol = $startCol;
            $this->excel->setValue($item['location']['divre_name'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['location']['witel_name'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['location']['nama_pelanggan'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['location']['alamat'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['location']['lokasi'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['location']['id_pelanggan'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['bill']['cmp_full'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['bill']['cmp'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['bill']['src'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['bill_fraction'] / 100, $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['saving']['target'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['saving']['value'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['saving']['achievement'], $currRow, $currCol);

            $currCol++;
            $this->excel->setValue($item['cer']['src_real'] / 100, $currRow, $currCol);

        }
        
        $this->excel
            ->selectCell([$startRow + 1, 7], [$currRow, 7])
            ->getCellStyle()
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

        $this->excel
            ->selectCell([$startRow + 1, 8], [$currRow, 10])
            ->getCellStyle()
            ->getNumberFormat()
            ->setFormatCode(\Excel::CUSTOM_NUMBER_FORMAT_CURRENCY_IDR);

        $this->excel
            ->selectCell([$startRow + 1, 11], [$currRow, 11])
            ->getCellStyle()
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

        $this->excel
            ->selectCell([$startRow + 1, 12], [$currRow, 14])
            ->getCellStyle()
            ->getNumberFormat()
            ->setFormatCode(\Excel::CUSTOM_NUMBER_FORMAT_CURRENCY_IDR);

        $this->excel
            ->selectCell([$startRow + 1, 15], [$currRow, 15])
            ->getCellStyle()
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);


        $condStyleGreen = new PhpOffice\PhpSpreadsheet\Style\Style(false, true);
        $condStyleGreen->getFont()
            ->getColor()
            ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN);
        $condStyleGreen->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('ffc6efce');
        $condStyleGreen->getFill()
            ->getEndColor()
            ->setARGB('ffc6efce');

        $condStyleRed = new PhpOffice\PhpSpreadsheet\Style\Style(false, true);
        $condStyleRed->getFont()
            ->getColor()
            ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
        $condStyleRed->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('ffffc7ce');
        $condStyleRed->getFill()
            ->getEndColor()
            ->setARGB('ffffc7ce');

        $condStyleSavingVal = $this->excel
            ->selectCell([$startRow + 1, 13], [$currRow, 13])
            ->getCellStyle()
            ->getConditionalStyles();

        $condSavingVal1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $condSavingVal1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $condSavingVal1->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
        $condSavingVal1->addCondition(0);
        $condSavingVal1->setStyle($condStyleGreen);
        $condSavingVal1->setStopIfTrue(false);
        array_push($condStyleSavingVal, $condSavingVal1);

        $condSavingVal0 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $condSavingVal0->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $condSavingVal0->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHANOREQUAL);
        $condSavingVal0->addCondition(0);
        $condSavingVal0->setStyle($condStyleRed);
        $condSavingVal0->setStopIfTrue(true);
        array_push($condStyleSavingVal, $condSavingVal0);

        $this->excel
            ->selectCell([$startRow + 1, 13], [$currRow, 13])
            ->getCellStyle()
            ->setConditionalStyles($condStyleSavingVal);


        $condStyleSavingDiff = $this->excel
            ->selectCell([$startRow + 1, 14], [$currRow, 14])
            ->getCellStyle()
            ->getConditionalStyles();

        $condSavingDiff0 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $condSavingDiff0->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $condSavingDiff0->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHANOREQUAL);
        $condSavingDiff0->addCondition(0);
        $condSavingDiff0->setStyle($condStyleGreen);
        $condSavingDiff0->setStopIfTrue(false);
        array_push($condStyleSavingDiff, $condSavingDiff0);

        $condSavingDiff1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $condSavingDiff1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $condSavingDiff1->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHAN);
        $condSavingDiff1->addCondition(0);
        $condSavingDiff1->setStyle($condStyleRed);
        $condSavingDiff1->setStopIfTrue(true);
        array_push($condStyleSavingDiff, $condSavingDiff1);

        $this->excel
            ->selectCell([$startRow + 1, 14], [$currRow, 14])
            ->getCellStyle()
            ->setConditionalStyles($condStyleSavingDiff);


        $this->excel->createDownload('PI Laten (ALL Location) '.date('Y_m_d_\jH_\mi_\ds_\w\i\b'));
    }
}