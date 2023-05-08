<?php

$filter = ['YEAR(p.timestamp)=' . date('Y')];
if(isset($zone['rtu'])) {
    $code = $zone['rtu'];
    array_push($filter, "r.rtu_kode='$code'");
} elseif(isset($zone['witel'])) {
    $code = $zone['witel'];
    array_push($filter, "r.witel_kode='$code'");
} elseif(isset($zone['divre'])) {
    $code = $zone['divre'];
    array_push($filter, "r.divre_kode='$code'");
}

$appliedFilter = implode(' AND ', $filter);
$join = "$this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode";
$q = "SELECT pue_value, timestamp FROM $this->tableName AS p JOIN $join WHERE $appliedFilter
    AND p.pue_value=(SELECT MAX(p.pue_value) FROM $this->tableName AS p JOIN $join WHERE $appliedFilter)
    ORDER BY p.timestamp DESC LIMIT 1";
    
$query = $this->db->query($q);
$this->result = $query->row();