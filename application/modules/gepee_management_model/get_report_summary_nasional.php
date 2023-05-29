<?php

$filterDateSch = $this->get_datetime_filter('created_at', $filter, 'sch');
$filterDatePueOff = $this->get_datetime_filter('created_at', $filter);
$filterDatePueOn = $this->get_datetime_filter('timestamp', $filter);

$filterDateSchYear = $this->get_datetime_filter('created_at', [ 'datetime' => $filter['datetimeYear'] ], 'sch');

/*
 * get Category data
 */
$this->db
    ->select('COUNT(exc.id)')
    ->from("$this->tableScheduleName AS sch")
    ->join("$this->tableExecutionName AS exc", 'exc.id_schedule=sch.id')
    ->where('sch.id_category=cat.id')
    ->where($filterDateSch);
$countAllQuery = $this->db->get_compiled_select();

$this->db
    ->select('COUNT(exc.id)')
    ->from("$this->tableScheduleName AS sch")
    ->join("$this->tableExecutionName AS exc", 'exc.id_schedule=sch.id')
    ->where('sch.id_category=cat.id')
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
    ->where($filterDateSchYear);
$countAllQueryYear = $this->db->get_compiled_select();

$this->db
    ->select('COUNT(exc.id)')
    ->from("$this->tableScheduleName AS sch")
    ->join("$this->tableExecutionName AS exc", 'exc.id_schedule=sch.id')
    ->where('sch.id_category=cat.id')
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
    
$performance = [];
$performanceSummary = [
    'count_all' => 0,
    'count_approved' => 0,
    'percentage' => 0
];
$performanceSummaryYear = [
    'count_all' => 0,
    'count_approved' => 0,
    'percentage' => 0
];

$percent = 0;
$summary = [];
$summaryYear = [];
foreach($categoryData as $categoryItem) {

    $perfm = $categoryItem;
    $perfm['count_all'] = (int) $perfm['count_all'];
    $perfm['count_approved'] = (int) $perfm['count_approved'];
    $perfm['has_schedule'] = boolval($perfm['has_schedule']);

    if($perfm['count_all'] < 1) {

        if(!$perfm['has_schedule']) {
            $percent = 100;
        } else {
            $performanceSummary['count_all'] += 1;
        }

    } else {

        $percent = $perfm['count_approved'] / $perfm['count_all'] * 100;
        $performanceSummary['count_all'] += $perfm['count_all'];
        $performanceSummary['count_approved'] += $perfm['count_approved'];

    }

    $perfm['percentage'] = $percent;
    array_push($performance, $perfm);
    array_push($summary, $percent);


    $countAllYearly = (int) $perfm['count_all_yearly'];
    $countApprovedYearly = (int) $perfm['count_approved_yearly'];

    $percentageYear = 0;
    $hasScheduleOnYear = boolval($perfm['has_schedule_on_year']);
    
    if($countAllYearly < 1) {

        if(!$hasScheduleOnYear) {
            $percentageYear = 100;
        } else {
            $performanceSummaryYear['count_all'] += 1;
        }

    } else {

        $percentageYear = $countApprovedYearly / $countAllYearly * 100;
        $performanceSummaryYear['count_all'] += $countAllYearly;
        $performanceSummaryYear['count_approved'] += $countApprovedYearly;
    
    }
    
    array_push($summaryYear, $percentageYear);

}

if(count($summary) > 0) {
    $performanceSummary['percentage'] = array_sum($summary) / count($summary);
}
if(count($summaryYear) > 0) {
    $performanceSummaryYear['percentage'] = array_sum($summaryYear) / count($summaryYear);
}

/*
 * get PUE Offline data
 */
$this->db
    ->select('pue_value')
    ->from($this->tablePueOfflineName)
    ->where($filterDatePueOff);
$pueOfflineData = $this->db->get()->result_array();

$pueOffline = array_reduce($pueOfflineData, function($pue, $item) {
    $pue['sum'] += (double) $item['pue_value'];
    $pue['count']++;
    return $pue;
}, [ 'sum' => 0, 'count' => 0 ]);

/*
 * get PUE Online data
 */
$this->db
    ->select('pue_value')
    ->from($this->tablePueOnlineName)
    ->where($filterDatePueOn);
$pueOnlineData = $this->db->get()->result_array();

$pueOnline = array_reduce($pueOnlineData, function($pue, $item) {
    $pue['sum'] += (double) $item['pue_value'];
    $pue['count']++;
    return $pue;
}, [ 'sum' => 0, 'count' => 0 ]);

$pue = [
    'offline' => ($pueOffline['count'] > 0) ? $pueOffline['sum'] / $pueOffline['count'] : 0,
    'online' => ($pueOnline['count'] > 0) ? $pueOnline['sum'] / $pueOnline['count'] : 0,
    'isReachTarget' => $pueOnline > 0 ? ($pueOnline > $pueLowLimit) : ($pueOffline > $pueLowLimit)
];

$data = [
    'performance' => $performance,
    'performance_summary' => $performanceSummary,
    'pue' => $pue,
    'tagihan_pln' => null,
    'replacement' => null
];
$this->result = $data;