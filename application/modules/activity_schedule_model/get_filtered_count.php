<?php

$this->filter_for_curr_user($currUser);

if(isset($filter['divre'])) $this->db->where('lokasi.divre_kode', $filter['divre']);
if(isset($filter['witel'])) $this->db->where('lokasi.witel_kode', $filter['witel']);
if(isset($filter['sto'])) $this->db->where('lokasi.sto_kode', $filter['sto']);
if(isset($filter['rtu'])) $this->db->where('lokasi.rtu_kode', $filter['rtu']);
if(isset($filter['idLokasi'])) $this->db->where('id_lokasi', $filter['idLokasi']);
if(isset($filter['month'])) $this->db->where('MONTH(created_at)', $filter['month']);
if(isset($filter['isChecked'])) $this->db->where('value', $filter['isChecked']);

$query = $this->db
    ->select('COUNT(*) AS count')
    ->from($this->tableName)
    ->join('master_lokasi_gepee AS lokasi', 'lokasi.id='.$this->tableName.'.id_lokasi')
    ->get();
$this->result = $query->row_array();