<?php

$select = null;
if(isset($filter['rtu'])) {
    $this->db->where('rtu_kode', $filter['rtu']);
    $select = 'divre_kode, divre_name, witel_kode, witel_name, rtu_kode, rtu_name, "rtu" AS level';
} elseif(isset($filter['witel'])) {
    $this->db->where('witel_kode', $filter['witel']);
    $select = 'divre_kode, divre_name, witel_kode, witel_name, "witel" AS level';
} elseif(isset($filter['divre'])) {
    $this->db->where('divre_kode', $filter['divre']);
    $select = 'divre_kode, divre_name, "regional" AS level';
} else {
    $this->result = [ 'level' => 'nasional' ];
    return $this->result;
}

$result = $this->db
    ->select($select)
    ->from($this->tableRtuMapName)
    ->get()
    ->row_array();
$this->result = $result;