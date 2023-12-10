<?php

$mainLocFilter = $this->get_loc_filter($filter);
$pueLocFilter = [ 'pue.id_location' => $filter['idLocation'] ];
$pueDateFilter = $this->get_datetime_filter($filter, 'pue');

$this->db
    ->select('*')
    ->from($this->tableLocationName)
    ->where($mainLocFilter);
$location = $this->db->get()->row_array();

$pueOfflineList = $this->db
    ->select('pue.*')
    ->from("$this->tableName AS pue")
    ->join("$this->tableLocationName AS loc", 'loc.id=pue.id_location')
    ->where('loc.tipe_perhitungan', 'pue')
    ->where($pueLocFilter)
    ->where($pueDateFilter);
$pueOfflineList = $this->db->get()->result_array();

$weekList = $this->get_week_list_of_month($filter['month'], $filter['year']); // item keys: key, number, number_of_month
$pueValues = [];
foreach($pueOfflineList as $pue) {

    $week = $this->date_to_week($pue['created_at'], $weekList);
    $pue['week_number'] = $week['number_of_month'];

    $pue['evidence_url'] = '#';
    if($pue['evidence']) {
        $pue['evidence_url'] = base_url(UPLOAD_PUE_EVIDENCE_PATH) . $pue['evidence'];
    }

    array_push($pueValues, $pue);

}

$this->result = [
    'location' => $location,
    'pue' => $pueValues,
    'weeks' => $weekList,
    'month' => $filter['month'],
	'year' => $filter['year']
];