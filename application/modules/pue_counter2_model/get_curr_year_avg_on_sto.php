<?php

$currTimes = get_time_range('currMonth', 'currWeek', 'currDay');
$qAvgs = array_map(function($row) {

    list($key, $startDate, $endDate) = $row;
    return "(SELECT COALESCE(AVG(pue_value), 0) FROM $this->tableName
        WHERE rtu_kode=p.rtu_kode AND (timestamp BETWEEN '$startDate' AND '$endDate')) AS $key";

}, $currTimes);
$qAvgs = implode(', ', $qAvgs);

$q = "SELECT r.divre_kode, r.divre_name, r.witel_kode, r.witel_name, r.rtu_kode, r.rtu_name, $qAvgs
    FROM $this->tableName AS p JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode";

if(isset($zone['witel'])) {
    $code = $zone['witel'];
    $q .= " WHERE r.witel_kode='$code'";
} elseif(isset($zone['divre'])) {
    $code = $zone['divre'];
    $q .= " WHERE r.divre_kode='$code'";
}

$q .= ' GROUP BY r.rtu_kode ORDER BY r.divre_kode, r.witel_kode, r.rtu_kode';
$query = $this->db->query($q);
$data = $query->result();

$this->result = [
    'data' => $data,
    'timestamp' => date('Y-m-d H:i:s')
];