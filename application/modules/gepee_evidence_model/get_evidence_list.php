<?php

$locFilter = $this->get_location_filter($filter, 'loc');
$dateFilter = $this->get_datetime_filter($filter, 'evd');

$fileBasicPath = base_url(UPLOAD_GEPEE_EVIDENCE_PATH);
$selectedFields = [
    'evd.*', 'cat.*', 'loc.*',
    "IF(evd.file>'', CONCAT('$fileBasicPath', evd.file), '#') AS file_url"
];

$this->db
    ->select(implode(', ', $selectedFields))
    ->from("$this->tableName AS evd")
    ->join("$this->tableCategoryName AS cat", 'cat.id_category=evd.id_category')
    ->join("$this->tableLocationName AS loc", 'loc.id_location=evd.id_location')
    ->where($locFilter)
    ->where($dateFilter)
    ->where('evd.id_category', $idCategory);

$data = $this->db->get()->result_array();
$this->result = $data;