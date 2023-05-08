<?php

$filter = ["(p.rtu_kode, p.timestamp) IN (SELECT rtu_kode, MAX(timestamp) FROM $this->tableName GROUP BY rtu_kode)"];
if(isset($zone['witel'])) {
    $code = $zone['witel'];
    array_push($filter, "r.witel_kode='$code'");
} elseif(isset($zone['divre'])) {
    $code = $zone['divre'];
    array_push($filter, "r.divre_kode='$code'");
}
array_push($filter, "p.pue_value>0");

$appliedFilter = implode(' AND ', $filter);
$q = "SELECT COALESCE(AVG(p.pue_value), 0) AS avg, p.timestamp FROM $this->tableName AS p
    JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode WHERE $appliedFilter";
$query = $this->db->query($q);
$this->result = $query->row();