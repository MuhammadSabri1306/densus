<?php

/*
 * setup DB Query Filter
 */
$filterLocGepee = $this->get_loc_filter($filter, 'loc');
$filterLocRtu = $this->get_loc_filter($filter, 'rtu');
$filterDatePueOff = $this->get_datetime_filter('created_at', $filter, 'pue');
$filterDatePueOn = $this->get_datetime_filter('timestamp', $filter, 'pue');

function getTargetQuery($rawFilter) {
    $filter = [];
    foreach($rawFilter as $key => $val) {
        $splitText = explode('.', $key);
        $field = count($splitText) > 1 ? $splitText[1] : $splitText[0];
        $filter[$field] = "IF($key='$val', 1, 0)";
    }

    if(isset($filter['witel_kode'])) {
        return $filter['witel_kode'];
    } elseif(isset($filter['divre_kode'])) {
        return $filter['divre_kode'];
    } else {
        return '1';
    }
}

/*
 * get Location data
 */
$targetLocQuery = getTargetQuery($filterLocGepee);
$this->db
    ->select("loc.*, IF(rtu.id_lokasi_gepee!='', 1, 0) AS is_online, $targetLocQuery AS is_target")
    ->from("$this->tableLocationName AS loc")
    ->join("$this->tableRtuName AS rtu", 'rtu.id_lokasi_gepee=loc.id', 'left')
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode')
    ->order_by('loc.sto_kode');
$locationData = $this->db->get()->result_array();

$categoryList = $this->db
    ->select()
    ->from($this->tableCategoryName)
    ->get()
    ->result_array();

/*
 * get PUE Offline data
 */
$targetLocQuery = getTargetQuery($filterLocGepee);
$this->db
    ->select("pue.id_location, pue.pue_value, pue.created_at, $targetLocQuery AS is_target")
    ->from("$this->tablePueOfflineName AS pue")
    ->join("$this->tableLocationName AS loc", 'loc.id=pue.id_location')
    ->where($filterDatePueOff)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode');
$pueOfflineData = $this->db->get()->result_array();

/*
 * get PUE Online data
 */
$targetLocQuery = getTargetQuery($filterLocRtu);
$this->db
    ->select("rtu.id_lokasi_gepee, pue.pue_value, pue.timestamp, $targetLocQuery AS is_target")
    ->from("$this->tablePueOnlineName AS pue")
    ->join("$this->tableRtuName AS rtu", 'rtu.rtu_kode=pue.rtu_kode')
    ->where('rtu.id_lokasi_gepee!=""')
    ->where($filterDatePueOn)
    ->order_by('rtu.divre_kode')
    ->order_by('rtu.witel_kode');
$pueOnlineData = $this->db->get()->result_array();

$selectedMonth = (int) date('n', strtotime($filter['datetime'][0]));
$selectedYear = (int) date('Y', strtotime($filter['datetime'][0]));
$monthList = range(1, 12);

$countAllQuery = $this->db
    ->select('COUNT(exc.id)')
    ->from("$this->tableExecutionName AS exc")
    ->where('exc.id_schedule=sch.id')
    ->get_compiled_select();
$countAprQuery = $this->db
    ->select('COUNT(exc.id)')
    ->from("$this->tableExecutionName AS exc")
    ->where('exc.id_schedule=sch.id')
    ->where('exc.status', 'approved')
    ->get_compiled_select();
$countRejtQuery = $this->db
    ->select('COUNT(exc.id)')
    ->from("$this->tableExecutionName AS exc")
    ->where('exc.id_schedule=sch.id')
    ->where('exc.status', 'rejected')
    ->get_compiled_select();
$countSubmQuery = $this->db
    ->select('COUNT(exc.id)')
    ->from("$this->tableExecutionName AS exc")
    ->where('exc.id_schedule=sch.id')
    ->where('exc.status', 'submitted')
    ->get_compiled_select();
$countConfQuery = $this->db
    ->select('COUNT(exc.id)')
    ->from("$this->tableExecutionName AS exc")
    ->where('exc.id_schedule=sch.id')
    ->where('exc.status !=', 'submitted')
    ->get_compiled_select();

$targetLocQuery = getTargetQuery($filterLocGepee);
$selectQuery = [
    'sch.*',
    'MONTH(sch.created_at) AS month',
    "($countAllQuery) AS count_all",
    "($countAprQuery) AS count_approved",
    "($countRejtQuery) AS count_rejected",
    "($countSubmQuery) AS count_submitted",
    "($countConfQuery) AS count_confirmed",
    "$targetLocQuery AS is_target"
];

$this->db
    ->select(implode(', ', $selectQuery))
    ->from("$this->tableScheduleName AS sch")
    ->join("$this->tableLocationName AS loc", 'loc.id=sch.id_lokasi')
    ->where('sch.value', 1)
    ->where('YEAR(sch.created_at)', $selectedYear)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode')
    ->order_by('loc.sto_kode')
    ->order_by('month')
    ->order_by('sch.id_category');

$sch = $this->db->get()->result_array();
$schData = array_reduce($sch, function($res, $row) {

    $idLoc = $row['id_lokasi'];
    if(!isset($res[$idLoc])) {
        $res[$idLoc] = [];
    }

    $month = (int) $row['month'];
    if(!isset($res[$idLoc][$month])) {
        $res[$idLoc][$month] = [];
    }
    
    $idCat = (int) $row['id_category'];
    if(!isset($res[$idLoc][$month][$idCat])) {
        $res[$idLoc][$month][$idCat] = [
            'countAll' => 0,
            'countApr' => 0,
            'countRejt' => 0,
            'countSubm' => 0,
            'countConf' => 0,
        ];
    }

    $res[$idLoc][$month][$idCat]['countAll'] += $row['count_all'];
    $res[$idLoc][$month][$idCat]['countApr'] += $row['count_approved'];
    $res[$idLoc][$month][$idCat]['countRejt'] += $row['count_rejected'];
    $res[$idLoc][$month][$idCat]['countSubm'] += $row['count_submitted'];
    $res[$idLoc][$month][$idCat]['countConf'] += $row['count_confirmed'];
    return $res;

}, []);

$data = [];
$dataNasional = [];
foreach($locationData as $location) {

    $isTarget = (bool) $location['is_target'];
    unset($location['is_target']);

    $idLoc = $location['id'];
    $perfMonth = [];
    $perfPercentTotal = 0;
    $perfPercentCount = 0;
    foreach($monthList as $month) {

        $perfMonthData = [];
        $monthPercentTotal = 0;
        $monthPercentCount = 0;
        foreach($categoryList as $cat) {

            $idCat = $cat['id'];
            $hasSchedule = isset($schData[$idLoc]);
            $hasSchedule = $hasSchedule && isset($schData[$idLoc][$month]);
            $hasSchedule = $hasSchedule && isset($schData[$idLoc][$month][$idCat]);
            $schItem = $hasSchedule ? $schData[$idLoc][$month][$idCat] : [];
            
            $item = [
                'count_all' => $hasSchedule ? (int) $schItem['countAll'] : 0,
                'count_approved' => $hasSchedule ? (int) $schItem['countApr'] : 0,
                'count_rejected' => $hasSchedule ? (int) $schItem['countRejt'] : 0,
                'count_submitted' => $hasSchedule ? (int) $schItem['countSubm'] : 0,
                'count_confirmed' => $hasSchedule ? (int) $schItem['countConf'] : 0,
                'percentage' => !$hasSchedule ? 100 : 0,
                'has_schedule' => $hasSchedule,
                'id_schedule' => $hasSchedule ? $schItem['id'] : null
            ];
            
            if($item['count_all'] > 0) {
                $item['percentage'] = $item['count_confirmed'] / $item['count_all'] * 100;
            }
            
            array_push($perfMonthData, $item);
            $monthPercentTotal += $item['percentage'];
            $monthPercentCount++;
        }
        
        $monthPercent = ($monthPercentCount > 0) ? $monthPercentTotal / $monthPercentCount : 100;
        array_push($perfMonth, [
            'data' => $perfMonthData,
            'percent' => $monthPercent
        ]);

        $perfPercentTotal += $monthPercent;
        $perfPercentCount++;
    }

    $perf = $perfMonth[$selectedMonth - 1]['data'];
    $perfSummary = $perfMonth[$selectedMonth - 1]['percent'];
    $perfSummaryYear = $perfPercentTotal / $perfPercentCount;

    $row = [
        'location' => $location,
        'performance' => $perf,
        'performance_summary' => $perfSummary,
        'performance_summary_yearly' => $perfSummaryYear,
        'pue' => [
            'offline' => null,
            'online' => null,
            'isReachTarget' => false
        ],
        'tagihan_pln' => null,
        'replacement' => null
    ];
    
    /*
     * join PUE Offline data to Result
     */
    $pueOffline = [ 'sum' => 0, 'count' => 0 ];
    for($i=0; $i<count($pueOfflineData); $i++) {

        if($pueOfflineData[$i]['id_location'] == $location['id']) {
            $pueOffline['sum'] += (double) $pueOfflineData[$i]['pue_value'];
            $pueOffline['count']++;
            $pueOfflineData[$i] = null;
        }

    }
    
    if($pueOffline['count'] > 0) {
        $row['pue']['offline'] = $pueOffline['sum'] / $pueOffline['count'];
        $pueOfflineData = array_filter($pueOfflineData);
    }

    /*
     * join PUE Online data to Result
     */
    if($location['is_online']) {
        $pueOnline = [ 'sum' => 0, 'count' => 0 ];
        for($i=0; $i<count($pueOnlineData); $i++) {

            if($pueOnlineData[$i]['id_lokasi_gepee'] == $location['id']) {
                $pueOnline['sum'] += (double) $pueOnlineData[$i]['pue_value'];
                $pueOnline['count']++;
                $pueOnlineData[$i] = null;
            }
            // if($location['id'] == 305) {
            //     dd_json($pueOnlineData);
            // }

        }
        
        if($pueOnline['count'] > 0) {
            $row['pue']['online'] = $pueOnline['sum'] / $pueOnline['count'];
            $pueOnlineData = array_filter($pueOnlineData);
        }
    }
    
    if(!is_null($row['pue']['online'])) {

        $row['pue']['isReachTarget'] = $row['pue']['online'] > $pueLowLimit;
        
    } elseif(!is_null($row['pue']['offline'])) {

        $row['pue']['isReachTarget'] = $row['pue']['offline'] > $pueLowLimit;

    }
    
    if($isTarget) {
        array_push($data, $row);
    }

    $perfPercent = array_map(function($percentItem) {
        return ($percentItem['has_schedule']) ? $percentItem['percentage'] : null;
    }, $row['performance']);
    
    array_push($dataNasional, [
        'divre_kode' => $row['location']['divre_kode'],
        'witel_kode' => $row['location']['witel_kode'],
        'performance' => $perfPercent,
        'performance_summary' => $row['performance_summary'],
        'performance_summary_yearly' => $row['performance_summary_yearly'],
        'pue_online' => $row['pue']['online'],
        'pue_offline' => $row['pue']['offline']
    ]);
}

function getAvgPerformance($data) {
    $rowCount = count($data);
    $colCount = count($data[0]);
    $averages = [];
    
    for($col = 0; $col < $colCount; $col++) {
        $sum = 0;
        $count = 0;
        
        for($row = 0; $row < $rowCount; $row++) {
			if(isset($data[$row][$col]) && $data[$row][$col] !== null) {
				$sum += $data[$row][$col];
				$count++;
			}
		}

		$average = ($count > 0) ? $sum / $count : null;
		$averages[] = $average;
	}

	return $averages;
}

$groupedData = [];
foreach ($dataNasional as $item) {
    $divreKode = $item['divre_kode'];
    $witelKode = $item['witel_kode'];

    // Check if the divre_kode exists as a key in $groupedData
    if (array_key_exists($divreKode, $groupedData)) {
        // Check if the witel_kode exists as a key in the inner array
        if (array_key_exists($witelKode, $groupedData[$divreKode])) {
            // If both keys exist, update the averages with the new values
            array_push($groupedData[$divreKode][$witelKode]['performance'], $item['performance']);
            $groupedData[$divreKode][$witelKode]['performance_summary'] += $item['performance_summary'];
            $groupedData[$divreKode][$witelKode]['performance_summary_yearly'] += $item['performance_summary_yearly'];
            if ($item['pue_online'] !== null) {
                if ($groupedData[$divreKode][$witelKode]['pue_online'] === null) {
                    $groupedData[$divreKode][$witelKode]['pue_online'] = $item['pue_online'];
                } else {
                    $groupedData[$divreKode][$witelKode]['pue_online'] += $item['pue_online'];
                }
                $groupedData[$divreKode][$witelKode]['count_pue_online']++;
            }
            if ($item['pue_offline'] !== null) {
                if ($groupedData[$divreKode][$witelKode]['pue_offline'] === null) {
                    $groupedData[$divreKode][$witelKode]['pue_offline'] = $item['pue_offline'];
                } else {
                    $groupedData[$divreKode][$witelKode]['pue_offline'] += $item['pue_offline'];
                }
                $groupedData[$divreKode][$witelKode]['count_pue_offline']++;
            }
            $groupedData[$divreKode][$witelKode]['count']++;
        } else {
            // If the witel_kode key doesn't exist, create a new entry with the current values
            $groupedData[$divreKode][$witelKode] = [
                'performance' => [ $item['performance'] ],
                'performance_summary' => $item['performance_summary'],
                'performance_summary_yearly' => $item['performance_summary_yearly'],
                'pue_online' => $item['pue_online'],
                'pue_offline' => $item['pue_offline'],
                'count' => 1,
                'count_pue_online' => ($item['pue_online'] !== null) ? 1 : 0,
                'count_pue_offline' => ($item['pue_offline'] !== null) ? 1 : 0,
            ];
        }
    } else {
        // If the divre_kode key doesn't exist, create a new entry with the witel_kode and current values
        $groupedData[$divreKode] = [
            $witelKode => [
                'performance' => [ $item['performance'] ],
                'performance_summary' => $item['performance_summary'],
                'performance_summary_yearly' => $item['performance_summary_yearly'],
                'pue_online' => $item['pue_online'],
                'pue_offline' => $item['pue_offline'],
                'count' => 1,
                'count_pue_online' => ($item['pue_online'] !== null) ? 1 : 0,
                'count_pue_offline' => ($item['pue_offline'] !== null) ? 1 : 0,
            ],
        ];
    }
}

// Calculate the averages for each group
foreach ($groupedData as $divreKode => &$divreGroup) {
    foreach ($divreGroup as $witelKode => &$group) {
        $group['performance'] = getAvgPerformance($group['performance']);
        $group['performance_summary'] /= $group['count'];
        $group['performance_summary_yearly'] /= $group['count'];
        if ($group['pue_online'] !== null) {
            $group['pue_online'] /= $group['count_pue_online'];
        }
        if ($group['pue_offline'] !== null) {
            $group['pue_offline'] /= $group['count_pue_offline'];
        }
    }
}

// dd_json($groupedData);

foreach ($groupedData as $divreKode => &$divreGroup) {

    $divreItem = [
        'performance' => [],
        'performance_summary' => 0,
        'performance_summary_yearly' => 0,
        'pue_online' => null,
        'pue_offline' => null,
        'count' => 0,
        'count_pue_online' => 0,
        'count_pue_offline' => 0,
    ];

    foreach ($divreGroup as $witelKode => $group) {
        array_push($divreItem['performance'], $group['performance']);
        $divreItem['performance_summary'] += $group['performance_summary'];
        $divreItem['performance_summary_yearly'] += $group['performance_summary_yearly'];
        $divreItem['count']++;
        if($group['pue_online'] !== null) {
            $divreItem['pue_online'] += $group['pue_online'];
            $divreItem['count_pue_online']++;
        }
        if($group['pue_offline'] !== null) {
            $divreItem['pue_offline'] += $group['pue_offline'];
            $divreItem['count_pue_offline']++;
        }
    }

    $divreGroup = [
        'performance' => getAvgPerformance($divreItem['performance']),
        'performance_summary' => $divreItem['performance_summary'] / $divreItem['count'],
        'performance_summary_yearly' => $divreItem['performance_summary_yearly'] / $divreItem['count'],
        'pue_online' => $divreItem['count_pue_online'] < 1 ? null : $divreItem['pue_online'] / $divreItem['count_pue_online'],
        'pue_offline' => $divreItem['count_pue_offline'] < 1 ? null : $divreItem['pue_offline'] / $divreItem['count_pue_offline']
    ];
}

// dd_json($groupedData);
$nasionalItem = [
    'performance' => [],
    'performance_summary' => 0,
    'performance_summary_yearly' => 0,
    'pue_online' => null,
    'pue_offline' => null,
    'count' => 0,
    'count_pue_online' => 0,
    'count_pue_offline' => 0,
];

foreach($groupedData as $group) {
    array_push($nasionalItem['performance'], $group['performance']);
    $nasionalItem['performance_summary'] += $group['performance_summary'];
    $nasionalItem['performance_summary_yearly'] += $group['performance_summary_yearly'];
    $nasionalItem['count']++;
    if($group['pue_online'] !== null) {
        $nasionalItem['pue_online'] += $group['pue_online'];
        $nasionalItem['count_pue_online']++;
    }
    if($group['pue_offline'] !== null) {
        $nasionalItem['pue_offline'] += $group['pue_offline'];
        $nasionalItem['count_pue_offline']++;
    }
}

$nasional = [
    'performance' => getAvgPerformance($nasionalItem['performance']),
    'performance_summary' => $nasionalItem['performance_summary'] / $nasionalItem['count'],
    'performance_summary_yearly' => $nasionalItem['performance_summary_yearly'] / $nasionalItem['count'],
    'pue_online' => $nasionalItem['count_pue_online'] < 1 ? null : $nasionalItem['pue_online'] / $nasionalItem['count_pue_online'],
    'pue_offline' => $nasionalItem['count_pue_offline'] < 1 ? null : $nasionalItem['pue_offline'] / $nasionalItem['count_pue_offline']
];

$nasionalPueOnline = $nasional['pue_online'] !== null ? $nasional['pue_online'] : 0;
$nasionalPueOffline = $nasional['pue_offline'] !== null ? $nasional['pue_offline'] : 0;
$nasional['isPueReachTarget'] = max($nasionalPueOnline, $nasionalPueOffline) > $pueLowLimit;

// $dataGroup = [];
// foreach($dataNasional as $item) {

//     $divreCode = $item['location']['divre_kode'];
//     $witelCode = $item['location']['witel_kode'];

//     if(!isset($dataGroup[$divreCode])) {
//         $dataGroup[$divreCode] = [];
//     }
    
//     if(!isset($dataGroup[$witelCode])) {
//         $dataGroup[$divreCode][$witelCode] = [
//             'performance' => [],
//             'performance_summary' => [],
//             'performance_summary_yearly' => [],
//             'pue_online' => [],
//             'pue_offline' => [],
//         ];
//     }

//     for($i=0; $i<count($item['performance']); $i++) {
//         if($i >= count($dataGroup[$divreCode][$witelCode]['performance'])) {
//             $dataGroup[$divreCode][$witelCode]['performance'][$i] = [];
//         }
//         $perfValue = $item['performance'][$i]['has_schedule'] ? $item['performance'][$i]['percentage'] : null;
//         array_push($dataGroup[$divreCode][$witelCode]['performance'][$i], $perfValue);
//     }

//     array_push($dataGroup[$divreCode][$witelCode]['performance_summary'], $item['performance_summary']);
//     array_push($dataGroup[$divreCode][$witelCode]['performance_summary_yearly'], $item['performance_summary_yearly']);
//     array_push($dataGroup[$divreCode][$witelCode]['pue_online'], $item['pue']['online']);
//     array_push($dataGroup[$divreCode][$witelCode]['pue_offline'], $item['pue']['offline']);
// }

// $nasional = [
//     'performance' => [],
//     'performance_summary' => null,
//     'performance_summary_yearly' => null,
//     'pue_online' => null,
//     'pue_offline' => null,
//     'isReachTarget' => false
// ];

// $nasional = [];
// $dataDivre = [];
// foreach($dataGroup as $divreCode => $item) {

//     $divreItem = [
//         'performance' => [],
//         'performance_summary' => [],
//         'performance_summary_yearly' => [],
//         'pue_online' => [],
//         'pue_offline' => []
//     ];
//     foreach($item as $dataWitel) {
//         $perfDivre = [];
//         foreach($dataWitel['performance'] as $perfWitel) {
//             $countPerfWitel = 0;
//             $sumPerfWitel = null;
            
//             foreach($perfWitel as $perfWitelItem) {
//                 if(is_null($sumPerfWitel) && !is_null($perfWitelItem)) {
//                     $sumPerfWitel = $perfWitelItem;
//                 } elseif(!is_null($sumPerfWitel)) {
//                     $sumPerfWitel += $perfWitelItem;
//                 }
//                 $countPerfWitel++;
//             }
            
//             $perfWitelAvg = is_null($sumPerfWitel) ? null : $sumPerfWitel / $countPerfWitel;
//             array_push($perfDivre, $perfWitelAvg);
//         }

//         $perfDivreSummary = count($dataWitel['performance_summary']) < 1 ? null : array_sum($dataWitel['performance_summary']) / count($dataWitel['performance_summary']);
//         $perfDivreSummaryNasional = count($dataWitel['performance_summary_yearly']) < 1 ? null : array_sum($dataWitel['performance_summary_yearly']) / count($dataWitel['performance_summary_yearly']);
        
//         $countPueOnline = 0;
//         $sumPueOnline = null;
//         foreach($dataWitel['pue_online'] as $pueOnlineItem) {
//             if(is_null($sumPueOnline) && !is_null($pueOnlineItem)) {
//                 $sumPueOnline = $pueOnlineItem;
//             } elseif(!is_null($sumPueOnline)) {
//                 $sumPueOnline += $pueOnlineItem;
//             }
//             $countPueOnline++;
//         }
//         $pueOnlineAvg = is_null($sumPueOnline) ? null : $sumPueOnline / $countPueOnline;
        
//         $countPueOffline = 0;
//         $sumPueOffline = null;
//         foreach($dataWitel['pue_offline'] as $pueOfflineItem) {
//             if(is_null($sumPueOffline) && !is_null($pueOfflineItem)) {
//                 $sumPueOffline = $pueOfflineItem;
//             } elseif(!is_null($sumPueOffline)) {
//                 $sumPueOffline += $pueOfflineItem;
//             }
//             $countPueOffline++;
//         }
//         $pueOfflineAvg = is_null($sumPueOffline) ? null : $sumPueOffline / $countPueOffline;

//         array_push($divreItem['performance'], $perfDivre);
//         array_push($divreItem['performance_summary'], $perfDivreSummary);
//         array_push($divreItem['performance_summary_yearly'], $perfDivreSummaryNasional);
//         array_push($divreItem['pue_online'], $pueOnlineAvg);
//         array_push($divreItem['pue_offline'], $pueOfflineAvg);
        
//     }
    
//     $dataDivre[$divreCode] = [
//         'performance' => [],
//         'performance_summary' => null,
//         'performance_summary_yearly' => null,
//         'pue_online' => null,
//         'pue_offline' => null
//     ];

//     $countDivreItem = 0;
//     $sumDivreItem = null;
//     for($i=0; $i<count($divreItem['performance']); $i++) {
//         foreach($divreItem['performance'][$i] as $perfDivreItem) {
//             if(is_null($sumDivreItem) && !is_null($perfDivreItem)) {
//                 $sumDivreItem = $perfDivreItem;
//             } elseif(!is_null($sumDivreItem)) {
//                 $sumDivreItem = $perfDivreItem;
//             }
//             $countDivreItem++;
//         }
//         $perfDivreItemAvg = is_null($sumDivreItem) ? null : $sumDivreItem / $countDivreItem;
//         array_push($dataDivre[$divreCode]['performance'], $perfDivreItemAvg);
//     }

//     $dataDivre[$divreCode]['performance_summary'] = count($divreItem['performance_summary']) < 1 ? 0 : array_sum($divreItem['performance_summary']) / count($divreItem['performance_summary']);
//     $dataDivre[$divreCode]['performance_summary_yearly'] = count($divreItem['performance_summary_yearly']) < 1 ? 0 : array_sum($divreItem['performance_summary_yearly']) / count($divreItem['performance_summary_yearly']);

//     $countDivreItem = 0;
//     $sumDivreItem = null;
//     foreach($divreItem['pue_online'] as $divrePueOnlineItem) {
//         if(is_null($sumDivreItem) && !is_null($divrePueOnlineItem)) {
//             $sumDivreItem = $divrePueOnlineItem;
//         } else if(!is_null($sumDivreItem)) {
//             $sumDivreItem += $divrePueOnlineItem;
//         }
//         $countDivreItem++;
//     }
//     $dataDivre[$divreCode]['pue_online'] = is_null($sumDivreItem) ? null : $sumDivreItem / $countDivreItem;

//     $countDivreItem = 0;
//     $sumDivreItem = null;
//     foreach($divreItem['pue_offline'] as $divrePueOfflineItem) {
//         if(is_null($sumDivreItem) && !is_null($divrePueOfflineItem)) {
//             $sumDivreItem = $divrePueOfflineItem;
//         } else if(!is_null($sumDivreItem)) {
//             $sumDivreItem += $divrePueOfflineItem;
//         }
//         $countDivreItem++;
//     }
//     $dataDivre[$divreCode]['pue_offline'] = is_null($sumDivreItem) ? null : $sumDivreItem / $countDivreItem;
// }


// dd_json($dataDivre);

$this->result = [
    'gepee' => $data,
    'gepee_summary_nasional' => $nasional
];