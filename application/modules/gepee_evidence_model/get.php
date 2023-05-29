<?php

// $locFields = ['loc.id_pel_pln', 'loc.nama_pel_pln', 'loc.tarif_pel_pln', 'loc.daya_pel_pln',
//     'loc.lokasi_pel_pln', 'loc.alamat_pel_pln', 'loc.gedung', 'loc.divre_kode', 'loc.divre_name',
//     'loc.witel_kode', 'loc.witel_name', 'loc.sto_kode', 'loc.sto_name', 'loc.tipe', 'loc.rtu_kode'];

$this->apply_filter($filter);
$this->db
    ->select('*')
    ->from("$this->tableName AS ev")
    ->join("$this->tableLocationName AS loc", 'loc.id_location=ev.id_location')
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode')
    ->order_by('ev.created_at', 'desc');
$query = $this->db->get();

$data = isset($filter['id']) ? $query->result_row() : $query->result();
$this->result = $data;