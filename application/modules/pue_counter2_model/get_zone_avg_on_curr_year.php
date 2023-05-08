<?php
/*
 * Current Source Code
 * 
 */

$start_of_last_week = new DateTime('-7 days');
$end_of_last_week = new DateTime();
$end_of_last_week->setTime(23, 59, 59);

$start_date = $start_of_last_week->format('Y-m-d');
$end_date = $end_of_last_week->format('Y-m-d');

$filter = ' WHERE ';
if(isset($zone['witel'])) {
    $code = $zone['witel'];
    $filter .= "r.witel_kode='$code' AND ";
} elseif(isset($zone['divre'])) {
    $code = $zone['divre'];
    $filter .= "r.divre_kode='$code' AND ";
}
$filter .= "DATE(timestamp) BETWEEN DATE('$start_date') AND DATE('$end_date')";

$group = ['DATE', 'MONTH', 'DAY', 'HOUR'];
$groupFilters = array_map(function($item) {
    return "$item(timestamp)=$item(p.timestamp)";
}, $group);
$qGroupFilters = 'WHERE ' . implode(' AND ', $groupFilters);

$groupItems = array_map(function($item) {
    return "$item(p.timestamp)";
}, $group);
$qGroupItems = 'GROUP BY ' . implode(', ', $groupItems);

$q = "SELECT p.timestamp, (SELECT COALESCE(AVG(pue_value), 0) FROM pue_counter $qGroupFilters) AS pue_value
    FROM $this->tableName AS p
    JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode
    $filter $qGroupItems";

$query = $this->db->query($q);
$this->result = $query->result();