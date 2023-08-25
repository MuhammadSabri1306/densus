<?php

/*
 * setup DB Query Filter
 */
$filterLocGepee = $this->get_loc_filter($filter);
$filterLocIke = $this->get_loc_filter($filter, 'loc');
$filterDateIke = $this->get_datetime_filter('created_at', $filter, 'ike');

/*
 * get Location
 */
$this->db
    ->select('*')
    ->from($this->tableLocationName)
    ->where($filterLocGepee)
    ->order_by('divre_kode')
    ->order_by('witel_name')
    ->order_by('sto_name');
$location = $this->db->get()->row_array();

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

/*
 * get Inputable Weeks
 */
$currYear = (int) date('Y');
$currMonth = (int) date('n');
$this->db
    ->select('ike.*, MONTH(ike.created_at) AS month')
    ->from("$this->tableName AS ike")
    ->join("$this->tableLocationName AS loc", 'loc.id=ike.id_location')
    ->where('loc.tipe_perhitungan', 'ike')
    ->where($filterLocIke)
    ->where('YEAR(ike.created_at)', $currYear)
    ->where('MONTH(ike.created_at)', $currMonth)
    ->order_by('ike.week');
$ikeThisMonth = $this->db->get()->result_array();

$inputableWeek = [];
for($week=1; $week<=4; $week++) {
    $ikeIndex = findArrayIndex($ikeThisMonth, fn($item) => $item['week'] == $week);
    if($ikeIndex < 0) {
        array_push($inputableWeek, $week);
    }
}

$this->result = [
    'ike' => $ikeList,
    'location' => $location,
    'inputable_weeks' => $inputableWeek
];