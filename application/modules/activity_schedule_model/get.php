<?php

$currMonth = date('n');
$query = "sch.*, loc.divre_kode, loc.witel_kode,
    IF(MONTH(sch.created_at)='$currMonth', 1, 0) AS is_enabled,
    (SELECT COUNT(*) FROM $this->tableExecutionName WHERE id_schedule=sch.id) AS execution_count,
    (SELECT COUNT(*) FROM $this->tableExecutionName WHERE id_schedule=sch.id AND status='approved') AS approved_count";

// $this->apply_filter($filter);
// dd($this->tableName);

$filterLoc = $this->get_loc_filter($filter, 'loc');
$filterDate = $this->get_datetime_filter($filter, 'sch');

$this->db
    ->select($query)
    ->from($this->tableName.' AS sch')
    ->where($filterLoc)
    ->where($filterDate)
    ->join("$this->tableLocationName AS loc", 'loc.id=sch.id_lokasi');

if(isset($filter['id'])) {
    $data = $this->db
        ->where('sch.id', $filter['id'])
        ->get()
        ->row_array();
    $data['is_enabled'] = $this->is_time_updatable($data['created_at']);
    $data['updatable'] = [
        'schedule' => $this->is_schedule_updatable($data['created_at']),
        'execution' => $this->is_execution_updatable($data['created_at'])
    ];
    
    $this->result = $data;
    return null;
}
// dd($this->db->get_compiled_select());
$result = $this->db->get()->result_array();
$data = [];
foreach($result as $row) {
    $temp = $row;
    $temp['is_enabled'] = $this->is_time_updatable($row['created_at']);
    $temp['updatable'] = [
        'schedule' => $this->is_schedule_updatable($row['created_at']),
        'execution' => $this->is_execution_updatable($row['created_at'])
    ];
    array_push($data, $temp);
}

$this->result = $data;