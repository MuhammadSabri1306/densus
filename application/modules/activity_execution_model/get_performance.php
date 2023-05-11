<?php

$selectScheduleId = 'SELECT s.id FROM activity_schedule AS s WHERE s.id_category=c.id AND s.id_lokasi=loc.id
    AND MONTH(s.created_at)=m.month AND YEAR(s.created_at)="2023" AND s.value=1';
$selectExecCount = 'SELECT COUNT(*) FROM activity_execution AS e JOIN activity_schedule AS s ON s.id=e.id_schedule
    WHERE s.id_category=c.id AND s.id_lokasi=loc.id AND MONTH(s.created_at)=m.month AND YEAR(s.created_at)="'.date('Y').'"';
$selectApprovedCount = $selectExecCount . ' AND e.status="approved"';

$selectList = [ 'loc.id AS id_lokasi', 'loc.alamat_pel_pln', 'loc.divre_kode', 'loc.divre_name',
    'loc.witel_kode', 'loc.witel_name', 'loc.sto_kode', 'loc.sto_name', 'm.month', 'c.id AS category_id',
    'c.alias AS category_alias', 'c.activity AS category_name', "($selectScheduleId) AS id_schedule",
    "($selectExecCount) AS exec_count", "($selectApprovedCount) AS approved_count" ];
$querySelect = implode(', ', $selectList);

$appliedMonth = $filter['month'] ? [$filter['month']] : [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
$monthQueryList = array_map(function($item) {
    return "SELECT $item AS month";
}, $appliedMonth);
$tableMonth = '('.implode(' UNION ALL ', $monthQueryList).')';

$appliedLocation = [];
if(is_array($filter)) {
    if($filter['witel']) $appliedLocation['loc.witel_kode'] = $filter['witel'];
    if($filter['divre']) $appliedLocation['loc.divre_kode'] = $filter['divre'];
}
if(is_array($this->currUser)) {
    if($this->currUser['level'] == 'witel') $appliedLocation['loc.witel_kode'] = $this->currUser['locationId'];
    if($this->currUser['level'] == 'divre') $appliedLocation['loc.divre_kode'] = $this->currUser['locationId'];
}
$whereQueryList = [];
foreach($appliedLocation as $key => $val) {
    array_push($whereQueryList, "$key='$val'");
}
$queryWhere = count($whereQueryList) > 0 ? 'WHERE '.implode(' AND ', $whereQueryList) : '';

$query = "SELECT $querySelect FROM $this->tableLocationName AS loc
    CROSS JOIN $this->tableCategoryName AS c
    CROSS JOIN $tableMonth AS m
    $queryWhere
    ORDER BY loc.id, m.month, c.id";

$data = $this->db
    ->query($query)
    ->result_array();

$data = array_map(function($item) {
    $item['exec_count'] = (int) $item['exec_count'];
    $item['approved_count'] = (int) $item['approved_count'];
    $item['percent'] = $item['exec_count'] > 0 ? $item['approved_count'] / $item['exec_count'] * 100 : 0;
    return $item;
}, $data);

$this->result = $data;