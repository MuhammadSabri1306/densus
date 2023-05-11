<?php

$this->filter_for_curr_user($currUser);

$selectFields = [
    'e.*',
    "IF(e.evidence>'', CONCAT('".base_url(UPLOAD_ACTIVITY_EVIDENCE_PATH)."', e.evidence), '#') AS evidence_url"
];
$selectQuery = implode(', ', $selectFields);

$this->db
    ->select($selectQuery)
    ->from("$this->tableName AS e")
    ->join("$this->tableScheduleName AS s", 's.id=e.id_schedule')
    ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
    ->where('e.id_schedule', $scheduleId)
    ->order_by('updated_at', 'DESC');
// dd($this->db->get_compiled_select());
$query = $this->db->get();
$this->result = $query->result_array();