<?php

$locFilter = $this->get_location_filter([], 'loc');
$fields = [
    'evd.*',
    "IF(e.file>'', CONCAT('".base_url(UPLOAD_GEPEE_EVIDENCE_PATH)."', e.file), '#') AS file_url"
];

$this->db
    ->select(implode(', ', $fields))
    ->from("$this->tableName AS evd")
    ->join("$this->tableLocationName AS loc", 'loc.id_location=evd.id_location')
    ->where($locFilter)
    ->where('evd.id', $id);

$data = $this->db->get()->row_array();
$this->result = $data;