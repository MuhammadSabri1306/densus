<?php

$filterLoc = $this->get_loc_filter($filter, 'loc');

$isPueOflineQuery = $this->db
    ->select()
    ->from("$this->tablePueOfflineName AS pue")
    ->where('pue.id_location=loc.id')
    ->limit(1)
    ->get_compiled_select();

$isPueOnlineQuery = $this->db
    ->select()
    ->from("$this->tableRtuName AS rtu")
    ->where('rtu.id_lokasi_gepee=loc.id')
    ->limit(1)
    ->get_compiled_select();

$this->db
    ->select("loc.*, EXISTS($isPueOflineQuery) AS is_offline, EXISTS($isPueOnlineQuery) AS is_online")
    ->from("$this->tableLocationName AS loc")
    ->where($filterLoc)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode');
$locationData = $this->db->get()->result_array();

$result = array_map(function($item) {
    $item['is_offline'] = boolval($item['is_offline']);
    $item['is_online'] = boolval($item['is_online']);
    return $item;
}, $locationData);

$this->result = $result;