<?php

// $this->filter_for_curr_user($currUser);
        
$this->db
    ->select()
    ->from($this->tableName)
    ->where('id', $id);
$exec = $this->db->get()->row_array();

$isFileDeleted = false;
if(isset($exec['evidence'])) {
    $filePath = FCPATH . UPLOAD_ACTIVITY_EVIDENCE_PATH . '/' . $exec['evidence'];
    $isFileDeleted = unlink($filePath);
}

if($isFileDeleted) {
    $this->db->where('id', $id);
    $this->result = $this->db->delete($this->tableName);
}

$this->result = false;