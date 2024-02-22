<?php

/*
 * setup DB Query Filter
 */
$filterLocGepee = $this->get_loc_filter($filter, 'loc');
$filterLocRtu = $this->get_loc_filter($filter, 'rtu');
$filterDatePueOff = $this->get_datetime_filter('created_at', $filter, 'pue');
$filterDatePueOn = $this->get_datetime_filter('timestamp', $filter, 'pue');
$filterDateIke = $this->get_datetime_filter('created_at', $filter, 'ike');

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
$selectQuery = [
    'loc.*',
    'IF(loc.tipe_perhitungan=\'ike\', NULL, IF(rtu.id_lokasi_gepee!=\'\', 1, 0)) AS is_pue_online',
    "$targetLocQuery AS is_target"
];

$this->db
    ->select(implode(', ', $selectQuery), false)
    ->from("$this->tableLocationName AS loc")
    ->join("$this->tableRtuName AS rtu", 'rtu.id_lokasi_gepee=loc.id', 'left')
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_name')
    ->order_by('loc.sto_name');
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

// tableIkeName
/*
 * get IKE data
 */
$targetLocQuery = getTargetQuery($filterLocGepee);
$this->db
    ->select("ike.id_location, ike.ike_value, ike.created_at, $targetLocQuery AS is_target")
    ->from("$this->tableIkeName AS ike")
    ->join("$this->tableLocationName AS loc", 'loc.id=ike.id_location')
    ->where($filterDateIke)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode');
$ikeData = $this->db->get()->result_array();


/*
 * get Schedule Data for Performance Calculation
 */
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

/*
 * get PLN Bill Data
 */
$dbAmc = $this->load->database('amc', TRUE);

$dbAmc->select()
    ->from("$dbAmc->database.t_pln_transaksi")
    ->where('tahun', (int) $filter['year'])
    ->order_by('id_pelanggan');
$plnCurrYear = $dbAmc->get()->result_array();

$dbAmc->select()
    ->from("$dbAmc->database.t_pln_transaksi")
    ->where('tahun', (int) ($filter['year'] - 1))
    ->order_by('id_pelanggan');
$plnPrevYearData = $dbAmc->get()->result_array();

$plnMonthKeys = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus',
    'september', 'oktober', 'november', 'desember'];

$plnCurrMonth = array_column($plnCurrYear, $plnMonthKeys[($filter['month'] - 1)]);
$plnPrevMonth = array_column($plnCurrYear, $plnMonthKeys[($filter['month'] - 2)]);
if($filter['month'] === 1) $plnPrevMonth = array_column($plnPrevYearData, $plnMonthKeys[11]);
$plnPrevYear = array_column($plnPrevYearData, $plnMonthKeys[($filter['month'] - 1)]);

$data = [];
$dataNasional = [];
foreach($locationData as $location) {

    $isTarget = (bool) $location['is_target'];
    $isPueOnline = is_null($location['is_pue_online']) ? null : (bool) $location['is_pue_online'];
    unset($location['is_target'], $location['is_pue_online']);
    $location['is_online'] = $isPueOnline; // Hapus Brii!! Ntar klo datatable dan excel beres 

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

    $plnIndex = findArrayIndex($plnCurrYear, fn($item) => $item['id_pelanggan'] == $location['id_pel_pln']);
    $plnPrevYearIndex = findArrayIndex($plnPrevYearData, fn($item) => $item['id_pelanggan'] == $location['id_pel_pln']);
    $plnBill = null;
    $plnSaving = null;
    $plnSavingPercent = null;
    $plnSavingYoy = null;
    $plnSavingYoyPercent = null;

    if($plnIndex >= 0) {
        $plnBill = (int) $plnCurrMonth[$plnIndex];
        $plnPrevBill = (int) $plnPrevMonth[$plnIndex];
        $plnPrevYearBill = (int) $plnPrevYear[$plnPrevYearIndex];

        if($plnBill > 0 && $plnPrevBill > 0) {
            $plnSaving = $plnBill - $plnPrevBill;
            $plnSavingPercent = $plnSaving / $plnBill * 100;
        }

        if($plnBill > 0 && $plnPrevYearBill > 0) {
            $plnSavingYoy = $plnBill - $plnPrevYearBill;
            $plnSavingYoyPercent = $plnSavingYoy / $plnBill * 100;
        }
    }

    $row = [
        'location' => $location,
        'performance' => $perf,
        'performance_summary' => $perfSummary,
        'performance_summary_yearly' => $perfSummaryYear,
        'is_pue' => $location['tipe_perhitungan'] == 'pue',
        'is_ike' => $location['tipe_perhitungan'] == 'ike',
        'pue' => [
            'offline' => null,
            'online' => null,
            'isReachTarget' => true,
            'is_online' => $isPueOnline
        ],
        'ike' => null,
        'tagihan_pln' => $plnBill,
        'pln_saving' => $plnSaving,
        'pln_saving_percent' => $plnSavingPercent,
        'pln_saving_yoy' => $plnSavingYoy,
        'pln_saving_yoy_percent' => $plnSavingYoyPercent,
        'replacement' => null
    ];

    if($row['is_pue']) {

        /*
        * join PUE Offline data to Result
        */
        $pueOffline = [ 'sum' => 0, 'count' => 0 ];
        for($i=0; $i<count($pueOfflineData); $i++) {
            if($pueOfflineData[$i]['id_location'] == $location['id']) {
                $pueOffline['sum'] += (double) $pueOfflineData[$i]['pue_value'];
                $pueOffline['count']++;
            }
        }
        
        if($pueOffline['count'] > 0) {
            $row['pue']['offline'] = $pueOffline['sum'] / $pueOffline['count'];
        }

        /*
        * join PUE Online data to Result
        */
        if($row['pue']['is_online']) {
            $pueOnline = [ 'sum' => 0, 'count' => 0 ];
            for($i=0; $i<count($pueOnlineData); $i++) {

                if($pueOnlineData[$i]['id_lokasi_gepee'] == $location['id']) {
                    $pueOnline['sum'] += (double) $pueOnlineData[$i]['pue_value'];
                    $pueOnline['count']++;
                }

            }
            
            if($pueOnline['count'] > 0) {
                $row['pue']['online'] = $pueOnline['sum'] / $pueOnline['count'];
            }
        }

        if(!is_null($row['pue']['online']) && !is_null($row['pue']['offline'])) {

            $row['pue']['isReachTarget'] = min($row['pue']['online'], $row['pue']['offline']) > $pueLowLimit;
            
        }elseif(!is_null($row['pue']['online'])) {

            $row['pue']['isReachTarget'] = $row['pue']['online'] > $pueLowLimit;
            
        } elseif(!is_null($row['pue']['offline'])) {

            $row['pue']['isReachTarget'] = $row['pue']['offline'] > $pueLowLimit;

        }

    } elseif($row['is_ike']) {

        $ike = [ 'sum' => 0, 'count' => 0 ];
        for($i=0; $i<count($ikeData); $i++) {

            if($ikeData[$i]['id_location'] == $location['id']) {
                $ike['sum'] += (double) $ikeData[$i]['ike_value'];
                $ike['count']++;
            }

        }
        
        if($ike['count'] > 0) {
            $row['ike'] = $ike['sum'] / $ike['count'];
        }

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
        'pue_offline' => $row['pue']['offline'],
        'ike' => $row['ike'],
        'tagihan_pln' => $row['tagihan_pln'],
        'pln_saving' => $row['pln_saving'],
        'pln_saving_percent' => $row['pln_saving_percent'],
        'pln_saving_yoy' => $row['pln_saving_yoy'],
        'pln_saving_yoy_percent' => $row['pln_saving_yoy_percent'],
    ]);
}

if(!$sumNasional) {

    $this->result = [
        'gepee' => $data,
        'gepee_summary_nasional' => null
    ];

    return $this->result;

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

            if($item['tagihan_pln'] !== null) {
                if($groupedData[$divreKode][$witelKode]['tagihan_pln'] === null) {
                    $groupedData[$divreKode][$witelKode]['tagihan_pln'] = 0;
                }
                $groupedData[$divreKode][$witelKode]['tagihan_pln'] += $item['tagihan_pln'];
            }

            if($item['pln_saving'] !== null) {
                if($groupedData[$divreKode][$witelKode]['pln_saving'] === null) {
                    $groupedData[$divreKode][$witelKode]['pln_saving'] = 0;
                }
                $groupedData[$divreKode][$witelKode]['pln_saving'] += $item['pln_saving'];
                $groupedData[$divreKode][$witelKode]['count_pln_saving']++;
            }

            if($item['pln_saving_percent'] !== null) {
                if($groupedData[$divreKode][$witelKode]['pln_saving_percent'] === null) {
                    $groupedData[$divreKode][$witelKode]['pln_saving_percent'] = 0;
                }
                $groupedData[$divreKode][$witelKode]['pln_saving_percent'] += $item['pln_saving_percent'];
                $groupedData[$divreKode][$witelKode]['count_pln_saving_percent']++;
            }

            if($item['pln_saving_yoy'] !== null) {
                if($groupedData[$divreKode][$witelKode]['pln_saving_yoy'] === null) {
                    $groupedData[$divreKode][$witelKode]['pln_saving_yoy'] = 0;
                }
                $groupedData[$divreKode][$witelKode]['pln_saving_yoy'] += $item['pln_saving_yoy'];
                $groupedData[$divreKode][$witelKode]['count_pln_saving_yoy']++;
            }

            if($item['pln_saving_yoy_percent'] !== null) {
                if($groupedData[$divreKode][$witelKode]['pln_saving_yoy_percent'] === null) {
                    $groupedData[$divreKode][$witelKode]['pln_saving_yoy_percent'] = 0;
                }
                $groupedData[$divreKode][$witelKode]['pln_saving_yoy_percent'] += $item['pln_saving_yoy_percent'];
                $groupedData[$divreKode][$witelKode]['count_pln_saving_yoy_percent']++;
            }

            if ($item['pue_online'] !== null) {
                if ($groupedData[$divreKode][$witelKode]['pue_online'] === null) {
                    $groupedData[$divreKode][$witelKode]['pue_online'] = 0;
                }
                $groupedData[$divreKode][$witelKode]['pue_online'] += $item['pue_online'];
                $groupedData[$divreKode][$witelKode]['count_pue_online']++;
            }

            if ($item['pue_offline'] !== null) {
                if ($groupedData[$divreKode][$witelKode]['pue_offline'] === null) {
                    $groupedData[$divreKode][$witelKode]['pue_offline'] = 0;
                }
                $groupedData[$divreKode][$witelKode]['pue_offline'] += $item['pue_offline'];
                $groupedData[$divreKode][$witelKode]['count_pue_offline']++;
            }

            if ($item['ike'] !== null) {
                if ($groupedData[$divreKode][$witelKode]['ike'] === null) {
                    $groupedData[$divreKode][$witelKode]['ike'] = 0;
                }
                $groupedData[$divreKode][$witelKode]['ike'] += $item['ike'];
                $groupedData[$divreKode][$witelKode]['count_ike']++;
            }

            $groupedData[$divreKode][$witelKode]['count']++;

        } else {
            // If the witel_kode key doesn't exist, create a new entry with the current values
            $groupedData[$divreKode][$witelKode] = [
                'performance' => [ $item['performance'] ],
                'performance_summary' => $item['performance_summary'],
                'performance_summary_yearly' => $item['performance_summary_yearly'],
                'tagihan_pln' => $item['tagihan_pln'],
                'pln_saving' => $item['pln_saving'],
                'pln_saving_percent' => $item['pln_saving_percent'],
                'pln_saving_yoy' => $item['pln_saving_yoy'],
                'pln_saving_yoy_percent' => $item['pln_saving_yoy_percent'],
                'pue_online' => $item['pue_online'],
                'pue_offline' => $item['pue_offline'],
                'ike' => $item['ike'],
                'count' => 1,
                'count_pue_online' => ($item['pue_online'] !== null) ? 1 : 0,
                'count_pue_offline' => ($item['pue_offline'] !== null) ? 1 : 0,
                'count_ike' => ($item['ike'] !== null) ? 1 : 0,
                'count_pln_saving' => ($item['pln_saving'] !== null) ? 1 : 0,
                'count_pln_saving_percent' => ($item['pln_saving_percent'] !== null) ? 1 : 0,
                'count_pln_saving_yoy' => ($item['pln_saving'] !== null) ? 1 : 0,
                'count_pln_saving_yoy_percent' => ($item['pln_saving_yoy_percent'] !== null) ? 1 : 0,
            ];
        }
    } else {
        // If the divre_kode key doesn't exist, create a new entry with the witel_kode and current values
        $groupedData[$divreKode] = [
            $witelKode => [
                'performance' => [ $item['performance'] ],
                'performance_summary' => $item['performance_summary'],
                'performance_summary_yearly' => $item['performance_summary_yearly'],
                'tagihan_pln' => $item['tagihan_pln'],
                'pln_saving' => $item['pln_saving'],
                'pln_saving_percent' => $item['pln_saving_percent'],
                'pln_saving_yoy' => $item['pln_saving_yoy'],
                'pln_saving_yoy_percent' => $item['pln_saving_yoy_percent'],
                'pue_online' => $item['pue_online'],
                'pue_offline' => $item['pue_offline'],
                'ike' => $item['ike'],
                'count' => 1,
                'count_pue_online' => ($item['pue_online'] !== null) ? 1 : 0,
                'count_pue_offline' => ($item['pue_offline'] !== null) ? 1 : 0,
                'count_ike' => ($item['ike'] !== null) ? 1 : 0,
                'count_pln_saving' => ($item['pln_saving'] !== null) ? 1 : 0,
                'count_pln_saving_percent' => ($item['pln_saving_percent'] !== null) ? 1 : 0,
                'count_pln_saving_yoy' => ($item['pln_saving_yoy'] !== null) ? 1 : 0,
                'count_pln_saving_yoy_percent' => ($item['pln_saving_yoy_percent'] !== null) ? 1 : 0,
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

        if ($group['pln_saving'] !== null) {
            $group['pln_saving'] /= $group['count_pln_saving'];
        }

        if ($group['pln_saving_percent'] !== null) {
            $group['pln_saving_percent'] /= $group['count_pln_saving_percent'];
        }

        if ($group['pln_saving_yoy'] !== null) {
            $group['pln_saving_yoy'] /= $group['count_pln_saving_yoy'];
        }

        if ($group['pln_saving_yoy_percent'] !== null) {
            $group['pln_saving_yoy_percent'] /= $group['count_pln_saving_yoy_percent'];
        }

        if ($group['pue_online'] !== null) {
            $group['pue_online'] /= $group['count_pue_online'];
        }

        if ($group['pue_offline'] !== null) {
            $group['pue_offline'] /= $group['count_pue_offline'];
        }

        if ($group['ike'] !== null) {
            $group['ike'] /= $group['count_ike'];
        }
    }
}

foreach ($groupedData as $divreKode => &$divreGroup) {

    $divreItem = [
        'performance' => [],
        'performance_summary' => 0,
        'performance_summary_yearly' => 0,
        'tagihan_pln' => null,
        'pln_saving' => null,
        'pln_saving_percent' => null,
        'pln_saving_yoy' => null,
        'pln_saving_yoy_percent' => null,
        'pue_online' => null,
        'pue_offline' => null,
        'ike' => null,
        'count' => 0,
        'count_pue_online' => 0,
        'count_pue_offline' => 0,
        'count_ike' => 0,
        'count_pln_saving' => 0,
        'count_pln_saving_percent' => 0,
        'count_pln_saving_yoy' => 0,
        'count_pln_saving_yoy_percent' => 0,
    ];

    foreach ($divreGroup as $witelKode => $group) {

        array_push($divreItem['performance'], $group['performance']);
        $divreItem['performance_summary'] += $group['performance_summary'];
        $divreItem['performance_summary_yearly'] += $group['performance_summary_yearly'];
        $divreItem['count']++;

        if($group['tagihan_pln'] !== null) {
            if($divreItem['tagihan_pln'] === null) {
                $divreItem['tagihan_pln'] = 0;
            }
            $divreItem['tagihan_pln'] += $group['tagihan_pln'];
        }

        if($group['pln_saving'] !== null) {
            $divreItem['pln_saving'] += $group['pln_saving'];
            $divreItem['count_pln_saving']++;
        }

        if($group['pln_saving_percent'] !== null) {
            $divreItem['pln_saving_percent'] += $group['pln_saving_percent'];
            $divreItem['count_pln_saving_percent']++;
        }

        if($group['pln_saving_yoy'] !== null) {
            $divreItem['pln_saving_yoy'] += $group['pln_saving_yoy'];
            $divreItem['count_pln_saving_yoy']++;
        }

        if($group['pln_saving_yoy_percent'] !== null) {
            $divreItem['pln_saving_yoy_percent'] += $group['pln_saving_yoy_percent'];
            $divreItem['count_pln_saving_yoy_percent']++;
        }

        if($group['pue_online'] !== null) {
            $divreItem['pue_online'] += $group['pue_online'];
            $divreItem['count_pue_online']++;
        }

        if($group['pue_offline'] !== null) {
            $divreItem['pue_offline'] += $group['pue_offline'];
            $divreItem['count_pue_offline']++;
        }

        if($group['ike'] !== null) {
            $divreItem['ike'] += $group['ike'];
            $divreItem['count_ike']++;
        }

    }

    $divreGroup = [
        'performance' => getAvgPerformance($divreItem['performance']),
        'performance_summary' => $divreItem['performance_summary'] / $divreItem['count'],
        'performance_summary_yearly' => $divreItem['performance_summary_yearly'] / $divreItem['count'],
        'tagihan_pln' => $divreItem['tagihan_pln'],
        'pln_saving' => $divreItem['count_pln_saving'] < 1 ? null : $divreItem['pln_saving'] / $divreItem['count_pln_saving'],
        'pln_saving_percent' => $divreItem['count_pln_saving_percent'] < 1 ? null : $divreItem['pln_saving_percent'] / $divreItem['count_pln_saving_percent'],
        'pln_saving_yoy' => $divreItem['count_pln_saving_yoy'] < 1 ? null : $divreItem['pln_saving_yoy'] / $divreItem['count_pln_saving_yoy'],
        'pln_saving_yoy_percent' => $divreItem['count_pln_saving_yoy_percent'] < 1 ? null : $divreItem['pln_saving_yoy_percent'] / $divreItem['count_pln_saving_yoy_percent'],
        'pue_online' => $divreItem['count_pue_online'] < 1 ? null : $divreItem['pue_online'] / $divreItem['count_pue_online'],
        'pue_offline' => $divreItem['count_pue_offline'] < 1 ? null : $divreItem['pue_offline'] / $divreItem['count_pue_offline'],
        'ike' => $divreItem['count_ike'] < 1 ? null : $divreItem['ike'] / $divreItem['count_ike']
    ];
}

$nasionalItem = [
    'performance' => [],
    'performance_summary' => 0,
    'performance_summary_yearly' => 0,
    'tagihan_pln' => null,
    'pln_saving' => null,
    'pln_saving_percent' => null,
    'pln_saving_yoy' => null,
    'pln_saving_yoy_percent' => null,
    'pue_online' => null,
    'pue_offline' => null,
    'ike' => null,
    'count' => 0,
    'count_pue_online' => 0,
    'count_pue_offline' => 0,
    'count_ike' => 0,
    'count_pln_saving' => 0,
    'count_pln_saving_percent' => 0,
    'count_pln_saving_yoy' => 0,
    'count_pln_saving_yoy_percent' => 0,
];

foreach($groupedData as $group) {

    array_push($nasionalItem['performance'], $group['performance']);
    $nasionalItem['performance_summary'] += $group['performance_summary'];
    $nasionalItem['performance_summary_yearly'] += $group['performance_summary_yearly'];
    $nasionalItem['count']++;

    if($group['tagihan_pln'] !== null) {
        if($nasionalItem['tagihan_pln'] === null) {
            $nasionalItem['tagihan_pln'] = 0;
        }
        $nasionalItem['tagihan_pln'] += $group['tagihan_pln'];
    }

    if($group['pln_saving'] !== null) {
        $nasionalItem['pln_saving'] += $group['pln_saving'];
        $nasionalItem['count_pln_saving']++;
    }

    if($group['pln_saving_percent'] !== null) {
        $nasionalItem['pln_saving_percent'] += $group['pln_saving_percent'];
        $nasionalItem['count_pln_saving_percent']++;
    }

    if($group['pln_saving_yoy'] !== null) {
        $nasionalItem['pln_saving_yoy'] += $group['pln_saving_yoy'];
        $nasionalItem['count_pln_saving_yoy']++;
    }

    if($group['pln_saving_yoy_percent'] !== null) {
        $nasionalItem['pln_saving_yoy_percent'] += $group['pln_saving_yoy_percent'];
        $nasionalItem['count_pln_saving_yoy_percent']++;
    }

    if($group['pue_online'] !== null) {
        $nasionalItem['pue_online'] += $group['pue_online'];
        $nasionalItem['count_pue_online']++;
    }

    if($group['pue_offline'] !== null) {
        $nasionalItem['pue_offline'] += $group['pue_offline'];
        $nasionalItem['count_pue_offline']++;
    }

    if($group['ike'] !== null) {
        $nasionalItem['ike'] += $group['ike'];
        $nasionalItem['count_ike']++;
    }

}

$nasional = [
    'performance' => getAvgPerformance($nasionalItem['performance']),
    'performance_summary' => $nasionalItem['performance_summary'] / $nasionalItem['count'],
    'performance_summary_yearly' => $nasionalItem['performance_summary_yearly'] / $nasionalItem['count'],
    'tagihan_pln' => $nasionalItem['tagihan_pln'],
    'pln_saving' => $nasionalItem['count_pln_saving'] < 1 ? null : $nasionalItem['pln_saving'] / $nasionalItem['count_pln_saving'],
    'pln_saving_percent' => $nasionalItem['count_pln_saving_percent'] < 1 ? null : $nasionalItem['pln_saving_percent'] / $nasionalItem['count_pln_saving'],
    'pln_saving_yoy' => $nasionalItem['count_pln_saving_yoy'] < 1 ? null : $nasionalItem['pln_saving_yoy'] / $nasionalItem['count_pln_saving_yoy'],
    'pln_saving_yoy_percent' => $nasionalItem['count_pln_saving_yoy_percent'] < 1 ? null : $nasionalItem['pln_saving_yoy_percent'] / $nasionalItem['count_pln_saving_yoy'],
    'pue_online' => $nasionalItem['count_pue_online'] < 1 ? null : $nasionalItem['pue_online'] / $nasionalItem['count_pue_online'],
    'pue_offline' => $nasionalItem['count_pue_offline'] < 1 ? null : $nasionalItem['pue_offline'] / $nasionalItem['count_pue_offline'],
    'ike' => $nasionalItem['count_ike'] < 1 ? null : $nasionalItem['ike'] / $nasionalItem['count_ike'],
];

$nasionalPueOnline = $nasional['pue_online'] !== null ? $nasional['pue_online'] : 0;
$nasionalPueOffline = $nasional['pue_offline'] !== null ? $nasional['pue_offline'] : 0;
$nasional['isPueReachTarget'] = min($nasionalPueOnline, $nasionalPueOffline) > $pueLowLimit;

$this->result = [
    'gepee' => $data,
    'gepee_summary_nasional' => $nasional
];