<?php

/*
 * setup DB Query Filter
 */
$filterLocSto = $this->get_loc_filter($filter, 'sto');
$filterDateKwh = $this->extract_datetime_filter('timestamp', $filter['datetime']);
$filterDatePrevKwh = $this->extract_datetime_filter('timestamp', $filter['prevDatetime']);

/*
 * get Location data
 */
$this->db
    ->select('rtu.rtu_kode')
    ->from($this->tableRtuName)
    ->join("$this->densusDbName.$this->tableStoName AS sto", 'sto.sto_kode=rtu.master_sto_kode')
    ->where('rtu.master_sto_kode!=', '')
    ->where($filterLocSto);
$rtuList = $this->db->get()->result_array();

/*
 * get khw data
 */
if(empty($filterLocSto)) {

    $this->db
        ->select('SUM(kwh_value) AS value')
        ->from($this->tableName)
        ->where($filterDateKwh);
    $kwh = $this->db->get()->row_array();

    $this->db
        ->select('SUM(kwh_value) AS value')
        ->from($this->tableName)
        ->where($filterDatePrevKwh);
    $prevKwh = $this->db->get()->row_array();

} else {

    $rtuTargetCodes = array_column($rtuList, 'rtu_kode');

    $this->db
        ->select('SUM(kwh_value) AS value')
        ->from($this->tableName)
        ->where($filterDateKwh)
        ->where_in('rtu_kode', $rtuTargetCodes);
    $kwh = $this->db->get()->row_array();

    $this->db
        ->select('SUM(kwh_value) AS value')
        ->from($this->tableName)
        ->where($filterDatePrevKwh)
        ->where_in('rtu_kode', $rtuTargetCodes);
    $prevKwh = $this->db->get()->row_array();

}

$kwhValue = ($kwh && isset($kwh['value'])) ? (double) $kwh['value'] : null;
$prevKwhValue = ($prevKwh && isset($prevKwh['value'])) ? (double) $prevKwh['value'] : null;

$momData = [
    'performance' => null,
    'kwh' => null,
    'cost' => null,
];

if(!is_null($kwhValue) && !is_null($prevKwhValue)) {
    $momData['kwh'] = $prevKwhValue - $kwhValue;
    $momData['performance'] = $momData['kwh'] / $kwhValue * 100;
}

/*
 * get Target Area
 */
$targetArea = null;

if($filter['witel']) {

    $this->db
        ->select('witel_kode, witel_name')
        ->from("$this->densusDbName.$this->tableStoName")
        ->where('witel_kode', $filter['witel']);
    $targetArea = $this->db->get()->row_array();

} elseif($filter['divre']) {

    $this->db
        ->select('divre_kode, divre_name')
        ->from("$this->densusDbName.$this->tableStoName")
        ->where('divre_kode', $filter['divre']);
    $targetArea = $this->db->get()->row_array();
    
}

$this->result = [
    'mom' => $momData,
    'target_area' => $targetArea
];