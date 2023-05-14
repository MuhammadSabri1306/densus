<?php

$filterDateSch = $this->get_datetime_filter('created_at', $filter, 'sch');
$filterDatePueOff = $this->get_datetime_filter('created_at', $filter);
$filterDatePueOn = $this->get_datetime_filter('timestamp', $filter);

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

$categoryData = $this->db
    ->select("cat.id, cat.alias, cat.activity, ($countAllQuery) AS count_all, ($countApprovedQuery) AS count_approved")
    ->from("$this->tableCategoryName AS cat")
    ->get()
    ->result_array();
    
$performance = array_map(function ($item) {

    $item['count_all'] = (int) $item['count_all'];
    $item['count_approved'] = (int) $item['count_approved'];
    $item['percentage'] = $item['count_all'] < 1 ? 0 : $item['count_approved'] / $item['count_all'] * 100;
    return $item;

}, $categoryData);
$performance = [];
$performanceSummary = [
    'count_all' => 0,
    'count_approved' => 0,
    'percentage' => 0
];

foreach($categoryData as $categoryItem) {
    $perfm = $categoryItem;
    $perfm['count_all'] = (int) $perfm['count_all'];
    $perfm['count_approved'] = (int) $perfm['count_approved'];
    $perfm['percentage'] = $perfm['count_all'] < 1 ? 0 : $perfm['count_approved'] / $perfm['count_all'] * 100;
    array_push($performance, $perfm);

    $performanceSummary['count_all'] += $perfm['count_all'];
    $performanceSummary['count_approved'] += $perfm['count_approved'];
}

if($performanceSummary['count_all'] > 0) {
    $performanceSummary['percentage'] = $performanceSummary['count_approved'] / $performanceSummary['count_all'] * 100;
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