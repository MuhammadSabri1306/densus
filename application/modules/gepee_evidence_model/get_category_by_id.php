<?php

$this->db
    ->select()
    ->from($this->tableCategoryName)
    ->where('id_category', $idCategory);
$this->result = $this->db->get()->row_array();