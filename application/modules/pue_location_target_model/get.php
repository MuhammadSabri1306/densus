<?php

$filterLoc = $this->get_loc_filter($filter);
$filterDate = $this->get_datetime_filter($filter);

$this->db
    ->select('divre_kode, divre_name, witel_kode, witel_name')
    ->from('master_lokasi_gepee')
    ->where($filterLoc)
    ->group_by('witel_kode')
    ->order_by('divre_kode')
    ->order_by('witel_kode');
$locationList = $this->db->get()->result_array();

$this->db
    ->select('id, divre_kode, divre_name, witel_kode, witel_name, target AS value, quartal')
    ->from($this->tableName)
    ->where($filterLoc)
    ->where($filterDate)
    ->order_by('divre_kode')
    ->order_by('witel_kode')
    ->order_by('divre_kode')
    ->order_by('witel_kode')
    ->order_by('quartal');
$targetList = $this->db->get()->result_array();

$data = [];
foreach($locationList as $loc) {

    $row = [
        'witel' => $loc,
        'target' => [
            'q1' => [
                'divre_kode' => $loc['divre_kode'],
                'divre_name' => $loc['divre_name'],
                'witel_kode' => $loc['witel_kode'],
                'witel_name' => $loc['witel_name'],
                'quartal' => 1,
                'value' => null
            ],
            'q2' => [
                'divre_kode' => $loc['divre_kode'],
                'divre_name' => $loc['divre_name'],
                'witel_kode' => $loc['witel_kode'],
                'witel_name' => $loc['witel_name'],
                'quartal' => 2,
                'value' => null
            ],
            'q3' => [
                'divre_kode' => $loc['divre_kode'],
                'divre_name' => $loc['divre_name'],
                'witel_kode' => $loc['witel_kode'],
                'witel_name' => $loc['witel_name'],
                'quartal' => 3,
                'value' => null
            ],
            'q4' => [
                'divre_kode' => $loc['divre_kode'],
                'divre_name' => $loc['divre_name'],
                'witel_kode' => $loc['witel_kode'],
                'witel_name' => $loc['witel_name'],
                'quartal' => 4,
                'value' => null
            ]
        ]
    ];
    for($i=0; $i<count($targetList); $i++) {

        $isLocationMatch = $targetList[$i]['witel_kode'] == $loc['witel_kode'];
        if($isLocationMatch) {
            $quarterKey = 'q'.$targetList[$i]['quartal'];
            $row['target'][$quarterKey] = $targetList[$i];
            // $targetList[$i] = null;
        }

    }
    // $targetList = array_filter($targetList);
    array_push($data, $row);

}

$this->result = $data;