<?php

$filter = [ 'id' => $id ];
$filterId = $this->get_filter($filter, 'oxisp');
$filterLoc = $this->get_loc_filter([], 'loc');

$this->db
    ->select('oxisp.id, oxisp.evidence')
    ->from("$this->tableName AS oxisp")
    ->join("$this->tableLocationName AS loc", 'loc.id_sto=oxisp.id_sto')
    ->where($filterId)
    ->where($filterLoc);
$oxisp = $this->db->get()->row_array();

$isSuccess = false;
if($oxisp) {
    $isDeleted = $this->db
        ->where('id', $id)
        ->delete($this->tableName);
    $isSuccess = $isDeleted ? true : false;
}

if($isSuccess) {
    $filePath = FCPATH . UPLOAD_OXISP_EVIDENCE_PATH . '/' . $oxisp['evidence'];
    if(file_exists($filePath)) {
        unlink($filePath);
    }
}

$this->result = $isSuccess;