<?php

$this->db
    ->select()
    ->from($this->tableName)
    ->where('id', $id);
$evd = $this->db->get()->row_array();

$isFileDeleted = false;
if(isset($evd['file'])) {
    $filePath = FCPATH . UPLOAD_GEPEE_EVIDENCE_PATH . '/' . $evd['file'];
    $isFileDeleted = unlink($filePath);
}

if($isFileDeleted) {
    $this->db->where('id', $id);
    $this->result = $this->db->delete($this->tableName);
}
$this->result = false;