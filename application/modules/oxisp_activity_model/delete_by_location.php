<?php

$filter = [ 'idLocation' => $idLocation ];
$filterLoc = $this->get_loc_filter($filter);
$this->db
    ->select('evidence')
    ->from($this->tableName)
    ->where($filterLoc);
$oxisp = $this->db->get()->result_array();

$this->db->where($filterLoc);
$isSuccess = $this->db->delete($this->tableName) ? true : false;

if($isSuccess) {
    foreach(array_column($oxisp, 'evidence') as $evidence) {
        $filePath = FCPATH . UPLOAD_OXISP_EVIDENCE_PATH . '/' . $evidence;
        if(file_exists($filePath)) {
            unlink($filePath);
        }
    }
}

$this->result = $isSuccess;