<?php

// $pues = $this->db
//     ->select()
//     ->from($this->tableName)
// 	   ->where('week_number IS NULL')
//     ->get()
//     ->result_array();

// foreach($pues as $pue) {

//     $pueId = $pue['id'];
//     $pueDate = $pue['created_at'];

//     $dateTime = new DateTime($pueDate);
//     $pueWeekNumber = $dateTime->format('W');

//     echo "<br>id:$pueId, created_at:$pueDate, week_number:$pueWeekNumber";

//     $this->db->where([ 'id' => $pueId ]);
//     $this->db->update($this->tableName, [ 'week_number' => $pueWeekNumber ]);

// }
// exit();
// dd('DONE');

/* ============================================ */

$mainLocFilter = $this->get_loc_filter($filter);
$pueLocFilter = $this->get_loc_filter($filter, 'loc');
$pueDateFilter = $this->get_datetime_filter($filter, 'pue');

$this->db
	->select('*')
	->from($this->tableLocationName)
	->where('tipe_perhitungan', 'pue')
	->where($mainLocFilter)
	->order_by('divre_kode')
	->order_by('witel_kode')
	->order_by('sto_name');
$locationList = $this->db->get()->result_array();

$pueOfflineList = $this->db
    ->select('pue.*')
    ->from("$this->tableName AS pue")
    ->join("$this->tableLocationName AS loc", 'loc.id=pue.id_location')
    ->where('loc.tipe_perhitungan', 'pue')
    ->where($pueLocFilter)
    ->where($pueDateFilter)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode')
    ->order_by('loc.sto_name')
    ->order_by('pue.week_number');
$pueOfflineList = $this->db->get()->result_array();

$weekList = $this->get_week_list_of_month($filter['month'], $filter['year']); // item keys: key, number, number_of_month
$data = [];

foreach($locationList as $loc) {
  
	$pueWeekly = [];
	foreach($weekList as $week) {
		
		$weekKey = $week['key'];

		$pueEntries = [];
		foreach($pueOfflineList as $pueOffIndex => $pueOff) {
			$isLocMatch = $pueOff['id_location'] == $loc['id'];
			$isWeekMatch = $pueOff['week_number'] == $week['number'];
			if($isLocMatch && $isWeekMatch) {
				array_push($pueEntries, $pueOff['pue_value']);
				unset($pueOfflineList[$pueOffIndex]);
			}
		}
		$pueOfflineList = array_values($pueOfflineList);

		$pueAvgValue = null;
		$pueEntriesCount = count($pueEntries);
		if($pueEntriesCount > 0) {
			$pueAvgValue = array_sum($pueEntries) / $pueEntriesCount;
		}

		$pueWeekly[$weekKey] = [
			'year' => $filter['year'],
			'month' => $filter['month'],
			'week_number' => $week['number'],
			'entries' => $pueEntries,
			'pue_value' => $pueAvgValue
		];

	}
  
	array_push($data, [
		'location' => $loc,
		'pue' => $pueWeekly
	]);
  
}

$this->result = [
    'location_data' => $data,
	'weeks' => $weekList,
	'month' => $filter['month'],
	'year' => $filter['year']
];