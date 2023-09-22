<?php

$selectFields = [
    '*',
    "IF(evidence>'', CONCAT('".base_url(UPLOAD_OXISP_CHECK_EVIDENCE_PATH)."', evidence), '#') AS evidence_url"
];

$this->db
    ->select(implode(', ', $selectFields))
    ->from("$this->tableName AS check")
    ->where($filter);
$this->result = $this->db->get()->row_array();