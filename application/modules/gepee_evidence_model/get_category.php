<?php

if($code) {
    $this->db->where('code', $code);
}

$this->db
    ->select()
    ->from($this->tableCategoryName);

$this->result = $this->db->get()->result_array();