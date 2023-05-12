<?php

$month = $filter['month'];
$divreCode = $filter['divre'];

$timeStr = implode('-', [date('d'), $month, date('Y')]) . ' ' . date('H:i:s');
$timestamp = date('Y-m-d H:i:s', strtotime($timeStr));
$body = [];

// ALLOWED LOCATION
$this->db
    ->select('id')
    ->from($this->tableLocationName)
    ->where('divre_kode', $divreCode);

if(isset($filter['witel'])) {
    $this->db->where('witel_kode', $filter['witel']);
}

$this->filter_for_curr_user($currUser);

$locations = $this->db->get()->result_array();
$locationsId = count($locations) > 0 ? array_column($locations, 'id') : [];

// IS CHECKED
foreach($params as $item) {
    if($item['month'] == $month && in_array($item['id_lokasi'], $locationsId)) {
        $temp = [
            'id_category' => $item['id_category'],
            'id_lokasi' => $item['id_lokasi'],
            'value' => TRUE,
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ];
        array_push($body, $temp);
    }
}

// IS UNCHECKED
$this->db->reset_query();
$categories = $this->db->get($this->tableCategoryName)->result_array();
foreach($categories as $category) {
    foreach($locationsId as $locId) {

        $isUnchecked = ($item['month'] == $month) && in_array($category['id'], array_column($params, 'id_category'));
        if($isUnchecked) {
            $temp = [
                'id_category' => $category['id'],
                'id_lokasi' => $locId,
                'value' => FALSE,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ];
            array_push($body, $temp);
        }

    }
}

// INSERT BODY
$this->db->reset_query();
$this->db->insert_batch($this->tableName, $body);

$this->result = $this->db->affected_rows() > 0;