<?php

$filterLoc = $this->get_loc_filter($filter, 'loc');
$filterDate = $this->get_datetime_filter($filter, 'sch');

$locationList = $this->db
    ->select()
    ->from("$this->tableLocationName AS loc")
    ->where($filterLoc)
    ->order_by('divre_kode')
    ->order_by('witel_kode')
    ->get()
    ->result_array();

$categoryList = $this->db
    ->select()
    ->from($this->tableCategoryName)
    ->get()
    ->result_array();

$this->db
    ->select('COUNT(id)')
    ->from("$this->tableExecutionName AS exc")
    ->where('exc.id_schedule=sch.id');
$queryCountAllExc = $this->db->get_compiled_select();

$this->db
    ->select('COUNT(id)')
    ->from("$this->tableExecutionName AS exc")
    ->where('exc.id_schedule=sch.id')
    ->where('status', 'approved');
$queryCountApprovedExc = $this->db->get_compiled_select();

$schSelectQuery = "sch.*, MONTH(sch.created_at) AS month,
    ($queryCountAllExc) AS execution_count, ($queryCountApprovedExc) AS approved_count";
$this->db
    ->select($schSelectQuery)
    ->from("$this->tableName AS sch")
    ->join("$this->tableLocationName AS loc", 'loc.id=sch.id_lokasi')
    ->where($filterDate)
    ->where($filterLoc)
    ->order_by('divre_kode')
    ->order_by('witel_kode');
$scheduleList = $this->db->get()->result_array();

$startMonth = 1;
$endMonth = 12;
if(isset($filter['datetime'])) {
    $startMonth = (int) date('m', strtotime($filter['datetime'][0]));
    $endMonth = (int) date('m', strtotime($filter['datetime'][1]));
}

$monthList = range($startMonth, $endMonth);
$schedule = [];
foreach($locationList as $loc) {

    $divreCode = $loc['divre_kode'];
    if(!isset($schedule[$divreCode])) {
        $schedule[$divreCode] = [];
    }

    $witelCode = $loc['witel_kode'];
    if(!isset($schedule[$divreCode][$witelCode])) {
        $schedule[$divreCode][$witelCode] = [];
    }

    $stoCode = $loc['sto_kode'];
    if(!isset($schedule[$divreCode][$witelCode][$stoCode])) {
        $schedule[$divreCode][$witelCode][$stoCode] = [];
    }
}

$this->result = [
    'category_list' => $categoryList,
    'month_list' => $monthList,
    'schedule' => $schedule
];