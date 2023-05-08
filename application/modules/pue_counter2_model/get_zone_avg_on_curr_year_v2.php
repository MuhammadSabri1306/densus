<?php
/*
 * Optimized Code
 * 
 */

$startOfLastWeek = new DateTime('-7 days');
$startOfLastWeek->setTime(0, 0, 0);

$endOfLastWeek = new DateTime();
$endOfLastWeek->setTime(23, 59, 59);

$startDate = $startOfLastWeek->format('Y-m-d');
$endDate = $endOfLastWeek->format('Y-m-d');

if(isset($zone['witel'])) {
    $this->db->where('r.witel_kode', $zone['witel']);
} elseif(isset($zone['divre'])) {
    $this->db->where('r.divre_kode', $zone['divre']);
}

$this->db
    ->select('p.timestamp, p.pue_value')
    ->from("$this->tableName AS p")
    ->join("$this->tableRtuMapName AS r", 'r.rtu_kode=p.rtu_kode')
    ->where('DATE(p.timestamp)>=', $startDate)
    ->where('DATE(p.timestamp)<=', $endDate);

$pue = $this->db->get()->result_array();
$groupedPue = [];

foreach($pue as $row) {

    $currTimestamp = substr($row['timestamp'], 0, 13). ':00:00';
    if(!isset($groupedPue[$currTimestamp])) {
        $groupedPue[$currTimestamp] = [];
    }

    $pueValue = (double) $row['pue_value'];
    if($pueValue) {
        array_push($groupedPue[$currTimestamp], $pueValue);
    }

}

$data = array_map(function($item, $timestamp) {

    $totalCount = count($item);
    if($totalCount < 1) {
        return [ 'timestamp' => $timestamp, 'pue_value' => 0 ];
    }

    $sum = array_sum($item);
    $avgPue = $sum / $totalCount;
    // if($timestamp == '2023-04-29 11:00:00') {
        
    //     $test = [
    //         'sum' => $sum,
    //         'count' => $totalCount,
    //         'avg' => $avgPue
    //     ];
    //     dd(json_encode($item));
    //     dd(json_encode($test));
    
    // }
    return [ 'timestamp' => $timestamp, 'pue_value' => $avgPue ];

}, $groupedPue, array_keys($groupedPue));

$this->result = $data;