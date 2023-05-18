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
    ->select('id, divre_kode, divre_name, witel_kode, witel_name, target AS value, MONTH(created_at) AS month')
    ->from($this->tableName)
    ->where($filterLoc)
    ->where($filterDate)
    ->order_by('divre_kode')
    ->order_by('witel_kode')
    ->order_by('created_at', 'DESC');
$targetList = $this->db->get()->result_array();

$data = [];
foreach($locationList as $loc) {

    $row = [
        'witel' => $loc,
        'target' => [ 'q1' => null, 'q2' => null, 'q3' => null, 'q4' => null ]
    ];
    for($i=0; $i<count($targetList); $i++) {

        $isLocationMatch = $targetList[$i]['witel_kode'] == $loc['witel_kode'];
        $quarter = ceil((int) $targetList[$i]['month'] / 3);
        $quarterKey = "q$quarter";
        if($isLocationMatch && is_null($row['target'][$quarterKey])) {
            $row['target'][$quarterKey] = $targetList[$i];
            $targetList[$i] = null;
        }

    }
    $targetList = array_filter($targetList);
    array_push($data, $row);

}

$this->result = $data;