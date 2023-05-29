<?php

$filterLocMain = $this->get_loc_filter($filter, 'loc');
$filterLocOxisp = $this->get_loc_filter($filter, 'loc');
$filterDateOxisp = $this->get_datetime_filter($filter, 'oxisp');

$isUpdatableQuery = $this->mysql_updatable_time('oxisp.created_at');
$this->db
    ->select("oxisp.*, MONTH(oxisp.created_at) AS month, YEAR(oxisp.created_at) AS year, ($isUpdatableQuery) AS is_updatable")
    ->from("$this->tableName AS oxisp")
    ->join("$this->tableLocationName AS loc", 'loc.id_sto=oxisp.id_sto')
    ->where($filterLocOxisp)
    ->where($filterDateOxisp);
$oxisp = $this->db->get()->result_array();

$isOxispExistsQuery = $this->db
    ->select()
    ->from("$this->tableName AS oxisp")
    ->where('oxisp.id_sto=loc.id_sto')
    ->limit(1)
    ->get_compiled_select();
$this->db
    ->select()
    ->from("$this->tableLocationName AS loc")
    ->where($filterLocMain)
    ->where("EXISTS($isOxispExistsQuery)")
    ->order_by('divre_kode')
    ->order_by('witel_kode')
    ->order_by('sto_name');
$locationList = $this->db->get()->result_array();

$startMonth = 1;
$endMonth = (int) date('n');
$year = (int) date('Y');

if(isset($filter['datetime'])) {
    $startTime = strtotime($filter['datetime'][0]);
    $startMonth = (int) date('n', $startTime);
    $year = (int) date('Y', $startTime);

    $endTime = strtotime($filter['datetime'][1]);
    $currTime = strtotime(date('Y-m-d H:i:s'));
    if($endTime < $currTime) {
        $endMonth = (int) date('n', $endTime);
    }
}

$result = [];
$monthList = [];
foreach($locationList as $loc) {
    
    $row = [
        'location' => $loc,
        'location_data' => []
    ];

    for($month=$startMonth; $month<=$endMonth; $month++) {

        if(!in_array($month, $monthList)) {
            array_push($monthList, $month);
        }

        $monthItem = [
            'month' => $month,
            'year' => $year,
            'all_count' => 0,
            'approved_count' => 0,
            'is_updatable' => $this->is_date_updatable($year, $month, 1),
            'is_exists' => false
        ];

        for($i=0; $i<count($oxisp); $i++) {
            $isStoMatch = $oxisp[$i]['id_sto'] == $loc['id_sto'];
            $isMonthMatch = $oxisp[$i]['month'] == $month;

            if($isStoMatch && $isMonthMatch) {

                $monthItem['is_exists'] = true;
                $monthItem['all_count']++;
                if($oxisp[$i]['status'] == 'approved') {
                    $monthItem['approved_count']++;
                }
                if(!$monthItem['is_updatable']) {
                    $monthItem['is_updatable'] = boolval($oxisp[$i]['is_updatable']);
                }
                $oxisp[$i] = null;

            }
        }

        $oxisp = array_values(array_filter($oxisp));
        array_push($row['location_data'], $monthItem);
    }

    array_push($result, $row);
}

$this->result = [
    'performance' => $result,
    'month_list' => $monthList
];
// dd_json($monthList, $result);