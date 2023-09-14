<?php

/*
 * setup DB Query Filter
 */
$filterLocSto = $this->get_loc_filter($filter, 'sto');
$filterDateKwh = $this->get_datetime_filter('timestamp', $filter);

/*
 * setup date range column
 */
$startDateCol = new DateTime($filter['datetime'][0]);
$endDateCol = new DateTime($filter['datetime'][1]);
$monthColsTemp = [];

while($startDateCol <= $endDateCol) {
    $monthColsTemp[$startDateCol->format('n')] = null;
    $startDateCol->modify('+1 month');
}

/*
 * get Location data
 */
$this->db
    ->select('rtu.rtu_kode, rtu.rtu_name, sto.sto_kode, sto.sto_name, sto.witel_kode, sto.witel_name, sto.divre_kode, sto.divre_name')
    ->from($this->tableRtuName)
    ->join("$this->densusDbName.$this->tableStoName AS sto", 'sto.sto_kode=rtu.master_sto_kode')
    ->where('rtu.master_sto_kode!=', '')
    ->where($filterLocSto)
    ->order_by('sto.divre_name')
    ->order_by('sto.witel_name')
    ->order_by('sto.sto_name');
$rtuList = $this->db->get()->result_array();

/*
 * get khw data
 */
if(empty($filterLocSto)) {
    $this->db
        ->select('rtu_kode, SUM(kwh_value) AS kwh_value, YEAR(timestamp) AS kwh_year, MONTH(timestamp) AS kwh_month')
        ->from($this->tableName)
        ->where($filterDateKwh)
        ->group_by('rtu_kode')
        ->group_by('kwh_year')
        ->group_by('kwh_month')
        ->order_by('rtu_kode')
        ->order_by('timestamp');
} else {
    $rtuTargetCodes = array_map(function($locItem) {
        return $locItem['rtu_kode'];
    }, $rtuList);
    
    $this->db
        ->select('rtu_kode, SUM(kwh_value) AS kwh_value, DATE(timestamp) AS kwh_date, YEAR(timestamp) AS kwh_year, MONTH(timestamp) AS kwh_month')
        ->from($this->tableName)
        ->where($filterDateKwh)
        ->where_in('rtu_kode', $rtuTargetCodes)
        ->group_by('rtu_kode')
        ->group_by('kwh_year')
        ->group_by('kwh_month')
        ->order_by('rtu_kode')
        ->order_by('timestamp');
}
// dd($this->db->get_compiled_select());
$kwhList = $this->db->get()->result_array();
// dd($kwhList);

/*
 * build location list
 */
$locationList = array_reduce($rtuList, function($list, $item) {

    $currIndex = findArrayIndex($list, fn($listItem) => $listItem['sto_kode'] == $item['sto_kode'] && $listItem['sto_name'] == $item['sto_name']);
    if($currIndex < 0) {
        array_push($list, [
            'sto_kode' => $item['sto_kode'],
            'sto_name' => $item['sto_name'],
            'witel_kode' => $item['witel_kode'],
            'witel_name' => $item['witel_name'],
            'divre_kode' => $item['divre_kode'],
            'divre_name' => $item['divre_name'],
            'rtus' => []
        ]);
        $currIndex = count($list) - 1;
    }

    array_push($list[$currIndex]['rtus'], $item['rtu_kode']);
    return $list;

}, []);

/*
 * build khw data
 */
$kwhData = [];
foreach($locationList as $location) {

    $row = $location;
    $locKwh = [];

    $isKwhHasItemToRemove = false;
    foreach($kwhList as &$kwhItem) {
        if(!isset($kwhItem['has_used'])) {
            $kwhItem['has_used'] = false;
        }

        if(in_array($kwhItem['rtu_kode'], $row['rtus'])) {
            
            $kwhDate = $kwhItem['kwh_date'];
            if(!isset($locKwh[$kwhDate])) {
                $locKwh[$kwhDate] = [];
            }

            array_push($locKwh[$kwhDate], (double) $kwhItem['kwh_value']);
            $kwhItem['has_used'] = true;
            $isKwhHasItemToRemove = true;

        }
    }

    if($isKwhHasItemToRemove) {
        $kwhListTemp = array_filter($kwhList, fn($kwhListItem) => !$kwhListItem['has_used']);
        $kwhList = $kwhListTemp;
    }

    $row['kwh_values'] = $monthColsTemp;
    if(count($locKwh) > 0) {
        foreach($locKwh as $dateKey => $kwhValues) {

            list($kwhYear, $kwhMonth) = explode('-', $dateKey);
            $kwhMonth = (int) $kwhMonth;

            if(array_key_exists($kwhMonth, $row['kwh_values'])) {
                $kwhValue = count($kwhValues) < 1 ? null : array_sum($kwhValues);
                $row['kwh_values'][$kwhMonth] = [
                    'year' => (int) $kwhYear,
                    'month' => $kwhMonth,
                    'value' => $kwhValue,
                ];
            }

        }
    }

    array_push($kwhData, $row);

}

$this->result = [
    'kwh_data' => $kwhData,
    'month_column' => array_map(function($monthItem) {
        return (string) $monthItem;
    }, array_keys($monthColsTemp))
];