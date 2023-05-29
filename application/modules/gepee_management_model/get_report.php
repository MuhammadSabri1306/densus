<?php

/*
 * setup DB Query Filter
 */
$filterLocGepee = $this->get_loc_filter($filter, 'loc');
$filterLocRtu = $this->get_loc_filter($filter, 'rtu');
$filterDateSch = $this->get_datetime_filter('created_at', $filter, 'sch');
$filterDatePueOff = $this->get_datetime_filter('created_at', $filter, 'pue');
$filterDatePueOn = $this->get_datetime_filter('timestamp', $filter, 'pue');

$filterDateSchYear = $this->get_datetime_filter('created_at', [ 'datetime' => $filter['datetimeYear'] ], 'sch');

/*
 * get Location data
 */
$this->db
    ->select("loc.*, IF(rtu.id_lokasi_gepee!='', 1, 0) AS is_online")
    ->from("$this->tableLocationName AS loc")
    ->join("$this->tableRtuName AS rtu", 'rtu.id_lokasi_gepee=loc.id', 'left')
    ->where($filterLocGepee)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode');
$locationData = $this->db->get()->result_array();

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

$data = [];
foreach($locationData as $location) {

    $row = [
        'location' => $location,
        'performance' => [],
        'performance_summary' => [
            'count_all' => 0,
            'count_approved' => 0,
            'percentage' => 0
        ],
        'performance_summary_yearly' => [
            'count_all' => 0,
            'count_approved' => 0,
            'percentage' => 0
        ],
        'pue' => [
            'offline' => null,
            'online' => null,
            'isReachTarget' => false
        ],
        'tagihan_pln' => null,
        'replacement' => null
    ];
    $row['location']['is_online'] = boolval($row['location']['is_online']);

    /*
     * get Category data
     */
    $this->db
        ->select('COUNT(exc.id)')
        ->from("$this->tableScheduleName AS sch")
        ->join("$this->tableExecutionName AS exc", 'exc.id_schedule=sch.id')
        ->where('sch.id_category=cat.id')
        ->where('sch.id_lokasi', $location['id'])
        ->where($filterDateSch);
    $countAllQuery = $this->db->get_compiled_select();
    
    $this->db
        ->select('COUNT(exc.id)')
        ->from("$this->tableScheduleName AS sch")
        ->join("$this->tableExecutionName AS exc", 'exc.id_schedule=sch.id')
        ->where('sch.id_category=cat.id')
        ->where('sch.id_lokasi', $location['id'])
        ->where($filterDateSch)
        ->where('exc.status', 'approved');
    $countApprovedQuery = $this->db->get_compiled_select();

    /*
     * get Category data on Year
     */
    $this->db
        ->select('COUNT(exc.id)')
        ->from("$this->tableScheduleName AS sch")
        ->join("$this->tableExecutionName AS exc", 'exc.id_schedule=sch.id')
        ->where('sch.id_category=cat.id')
        ->where('sch.id_lokasi', $location['id'])
        ->where($filterDateSchYear);
    $countAllQueryYear = $this->db->get_compiled_select();
    
    $this->db
        ->select('COUNT(exc.id)')
        ->from("$this->tableScheduleName AS sch")
        ->join("$this->tableExecutionName AS exc", 'exc.id_schedule=sch.id')
        ->where('sch.id_category=cat.id')
        ->where('sch.id_lokasi', $location['id'])
        ->where($filterDateSchYear)
        ->where('exc.status', 'approved');
    $countApprovedQueryYear = $this->db->get_compiled_select();

    /*
     * has Schedule
     */
    $this->db
        ->select('IF(COUNT(sch.id)>0, 1, 0)')
        ->from("$this->tableScheduleName AS sch")
        ->where('sch.value=1')
        ->where('sch.id_category=cat.id')
        ->where('sch.id_lokasi', $location['id'])
        ->where($filterDateSch);
    $hasScheduleQuery = $this->db->get_compiled_select();

    /*
     * has Schedule on year
     */
    $this->db
        ->select('IF(COUNT(sch.id)>0, 1, 0)')
        ->from("$this->tableScheduleName AS sch")
        ->where('sch.value=1')
        ->where('sch.id_category=cat.id')
        ->where('sch.id_lokasi', $location['id'])
        ->where($filterDateSchYear);
    $hasScheduleOnYearQuery = $this->db->get_compiled_select();
    
    $selectQuery = "cat.id, cat.alias, cat.activity, ($hasScheduleQuery) AS has_schedule,
        ($hasScheduleQuery) AS has_schedule_on_year, ($countAllQuery) AS count_all, ($countApprovedQuery) AS count_approved,
        ($countAllQueryYear) AS count_all_yearly, ($countApprovedQueryYear) AS count_approved_yearly";
    $categoryData = $this->db
        ->select($selectQuery)
        ->from("$this->tableCategoryName AS cat")
        ->get()
        ->result_array();

    $percentageSummary = [];
    $percentageSummaryYear = [];

    foreach($categoryData as $category) {
        $perfm = $category;
        $perfm['count_all'] = (int) $perfm['count_all'];
        $perfm['count_approved'] = (int) $perfm['count_approved'];

        $percent = 0;
        $perfm['has_schedule'] = boolval($perfm['has_schedule']);

        // if($location['id'] == 406 && $category['id'] == 4) {
        //     dd($perfm['has_schedule']);
        // }

        if($perfm['count_all'] < 1) {

            if(!$perfm['has_schedule']) {
                $percent = 100;
            } else {
                $row['performance_summary']['count_all'] += 1;
            }

        } else {

            $percent = $perfm['count_approved'] / $perfm['count_all'] * 100;
            $row['performance_summary']['count_all'] += $perfm['count_all'];
            $row['performance_summary']['count_approved'] += $perfm['count_approved'];
        
        }
        
        $perfm['percentage'] = $percent;
        array_push($row['performance'], $perfm);
        array_push($percentageSummary, $percent);


        $countAllYearly = (int) $perfm['count_all_yearly'];
        $countApprovedYearly = (int) $perfm['count_approved_yearly'];

        $percentageYear = 0;
        $hasScheduleOnYear = boolval($perfm['has_schedule_on_year']);
        
        if($countAllYearly < 1) {

            if(!$hasScheduleOnYear) {
                $percentageYear = 100;
            } else {
                $row['performance_summary_yearly']['count_all'] += 1;
            }

        } else {

            $percentageYear = $countApprovedYearly / $countAllYearly * 100;
            $row['performance_summary_yearly']['count_all'] += $countAllYearly;
            $row['performance_summary_yearly']['count_approved'] += $countApprovedYearly;
        
        }
        
        array_push($percentageSummaryYear, $percentageYear);

        // $row['performance_summary']['count_all'] += $perfm['count_all'] ? $perfm['count_all'] : 1;
        // $row['performance_summary']['count_approved'] += $perfm['count_approved'];
    }
    
    if(count($percentageSummary) > 0) {
        $row['performance_summary']['percentage'] = array_sum($percentageSummary) / count($percentageSummary);
    }
    if(count($percentageSummaryYear) > 0) {
        $row['performance_summary_yearly']['percentage'] = array_sum($percentageSummaryYear) / count($percentageSummaryYear);
    }

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