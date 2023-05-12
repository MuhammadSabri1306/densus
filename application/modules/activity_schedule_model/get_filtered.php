<?php

$this->filter_for_curr_user($currUser);

if(isset($filter['divre'])) $this->db->where('l.divre_kode', $filter['divre']);
if(isset($filter['witel'])) $this->db->where('l.witel_kode', $filter['witel']);
if(isset($filter['sto'])) $this->db->where('l.sto_kode', $filter['sto']);
if(isset($filter['rtu'])) $this->db->where('l.rtu_kode', $filter['rtu']);
if(isset($filter['idLokasi'])) $this->db->where('id_lokasi', $filter['idLokasi']);
if(isset($filter['month'])) $this->db->where('MONTH(created_at)', $filter['month']);
if(isset($filter['isChecked'])) $this->db->where('value', $filter['isChecked']);

$currMonth = date('n');
$query = "s.*,
    IF(MONTH(s.created_at)='$currMonth', 1, 0) AS is_enabled,
    (SELECT COUNT(*) FROM $this->tableExecutionName WHERE id_schedule=s.id) AS execution_count,
    (SELECT COUNT(*) FROM $this->tableExecutionName WHERE id_schedule=s.id AND status='approved') AS approved_count";

$this->db
    ->select($query)
    ->from($this->tableName.' AS s')
    ->join('master_lokasi_gepee AS l', 'l.id=s.id_lokasi');
$query = $this->db->get();

$this->result = $query->result_array();