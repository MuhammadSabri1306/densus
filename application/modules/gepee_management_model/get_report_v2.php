<?php

/*
 * setup DB Query Filter
 */
$filterLocGepee = $this->get_loc_filter($filter, 'loc');
$filterLocRtu = $this->get_loc_filter($filter, 'rtu');
$filterDatePueOff = $this->get_datetime_filter('created_at', $filter, 'pue');
$filterDatePueOn = $this->get_datetime_filter('timestamp', $filter, 'pue');

/*
 * get Location data
 */
$this->db
    ->select("loc.*, IF(rtu.id_lokasi_gepee!='', 1, 0) AS is_online")
    ->from("$this->tableLocationName AS loc")
    ->join("$this->tableRtuName AS rtu", 'rtu.id_lokasi_gepee=loc.id', 'left')
    ->where($filterLocGepee)
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
$this->db
    ->select('pue.id_location, pue.pue_value, pue.created_at')
    ->from("$this->tablePueOfflineName AS pue")
    ->join("$this->tableLocationName AS loc", 'loc.id=pue.id_location')
    ->where($filterLocGepee)
    ->where($filterDatePueOff)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode');
$pueOfflineData = $this->db->get()->result_array();

/*
 * get PUE Online data
 */
$this->db
    ->select('rtu.id_lokasi_gepee, pue.pue_value, pue.timestamp')
    ->from("$this->tablePueOnlineName AS pue")
    ->join("$this->tableRtuName AS rtu", 'rtu.rtu_kode=pue.rtu_kode')
    ->where('rtu.id_lokasi_gepee!=""')
    ->where($filterLocRtu)
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

$selectQuery = [
    'sch.*',
    'MONTH(sch.created_at) AS month',
    "($countAllQuery) AS count_all",
    "($countAprQuery) AS count_approved",
    "($countRejtQuery) AS count_rejected",
    "($countSubmQuery) AS count_submitted",
    "($countConfQuery) AS count_confirmed"
];

$this->db
    ->select(implode(', ', $selectQuery))
    ->from("$this->tableScheduleName AS sch")
    ->join("$this->tableLocationName AS loc", 'loc.id=sch.id_lokasi')
    ->where('sch.value', 1)
    ->where($filterLocGepee)
    ->where('YEAR(sch.created_at)', $selectedYear)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode')
    ->order_by('loc.sto_kode')
    ->order_by('month')
    ->order_by('sch.id_category');
// dd($this->db->get_compiled_select());

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
foreach($locationData as $location) {

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
    
    array_push($data, $row);
}

$this->result = $data;