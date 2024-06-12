<?php

$this->result = false;
$this->db
    ->select()
    ->from($this->tableName)
    ->where('id', $id);
$evd = $this->db->get()->row_array();

$isFileExists = isset($evd['file']);
$isFileDeleted = false;
if($isFileExists) {
    $filePath = FCPATH . UPLOAD_GEPEE_EVIDENCE_PATH . '/' . $evd['file'];
    $isFileExists = file_exists($filePath);
    if($isFileExists) {
        $isFileDeleted = unlink($filePath);
    }
}

if(!$isFileExists || $isFileDeleted) {
    $this->db->where('id', $id);
    $this->result = $this->db->delete($this->tableName);
}