<?php

$currMonth = date('n');
$query = "s.*, l.divre_kode, l.witel_kode,
    IF(MONTH(s.created_at)='$currMonth', 1, 0) AS is_enabled,
    (SELECT COUNT(*) FROM $this->tableExecutionName WHERE id_schedule=s.id) AS execution_count,
    (SELECT COUNT(*) FROM $this->tableExecutionName WHERE id_schedule=s.id AND status='approved') AS approved_count";

$this->apply_filter($filter);
// dd($this->tableName);
$this->db
    ->select($query)
    ->from($this->tableName.' AS s')
    ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi');

if(isset($filter['id'])) {
    $data = $this->db->get()->row_array();
    $data['is_enabled'] = $this->is_time_updatable($data['created_at']);
    
    $this->result = $data;
    return null;
}

$result = $this->db->get()->result_array();
$data = [];
foreach($result as $row) {
    $temp = $row;
    $temp['is_enabled'] = $this->is_time_updatable($row['created_at']);
    array_push($data, $temp);
}

$this->result = $data;