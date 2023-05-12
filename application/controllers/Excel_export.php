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
}