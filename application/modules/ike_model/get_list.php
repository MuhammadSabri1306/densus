<?php

/*
 * setup DB Query Filter
 */
$filterLocGepee = $this->get_loc_filter($filter);
$filterLocIke = $this->get_loc_filter($filter, 'loc');
$filterDateIke = $this->get_datetime_filter('created_at', $filter, 'ike');

/*
 * get Location data
 */
$this->db
    ->select('*')
    ->from($this->tableLocationName)
    ->where('tipe_perhitungan', 'ike')
    ->where($filterLocGepee)
    ->order_by('divre_kode')
    ->order_by('witel_name')
    ->order_by('sto_name');
$locationData = $this->db->get()->result_array();

/*
 * get IKE data
 */
$this->db
    ->select('ike.*, MONTH(ike.created_at) AS month')
    ->from("$this->tableName AS ike")
    ->join("$this->tableLocationName AS loc", 'loc.id=ike.id_location')
    ->where('loc.tipe_perhitungan', 'ike')
    ->where($filterLocIke)
    ->where($filterDateIke)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_name')
    ->order_by('loc.sto_name')
    ->order_by('ike.week');
$ikeList = $this->db->get()->result_array();

$data = [];
foreach($locationData as $loc) {
    
    $row = [
        'location' => $loc,
        'ike' => []
    ];

    $isSearch = true;
    while($isSearch) {

        $ikeIndex = findArrayIndex($ikeList, fn($item) => $item['id_location'] == $loc['id']);
        if($ikeIndex < 0) {
            $isSearch = false;
        } else {

            array_push($row['ike'], $ikeList[$ikeIndex]);
            array_splice($ikeList, $ikeIndex, 1);

        }
    }

    array_push($data, $row);
}

$this->result = $data;