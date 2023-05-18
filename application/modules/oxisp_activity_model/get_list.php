<?php

$filterLocMain = $this->get_loc_filter($filter, 'loc');
$filterDateOxisp = $this->get_datetime_filter($filter, 'oxisp');

$isUpdatableQuery = $this->mysql_updatable_time('oxisp.created_at');
$selectFields = [
    'oxisp.*', 'MONTH(oxisp.created_at) AS month', 'YEAR(oxisp.created_at) AS year', "($isUpdatableQuery) AS is_updatable",
    "IF(oxisp.evidence>'', CONCAT('".base_url(UPLOAD_OXISP_EVIDENCE_PATH)."', oxisp.evidence), '#') AS evidence_url"
];

$this->db
    ->select(implode(', ', $selectFields))
    ->from("$this->tableName AS oxisp")
    ->join("$this->tableLocationName AS loc", 'loc.id_sto=oxisp.id_sto')
    ->where($filterLocMain)
    ->where($filterDateOxisp);
$oxisp = $this->db->get()->result_array();

$this->result = $oxisp;