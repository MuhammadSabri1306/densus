<?php

$queries = [];
$currTimes = get_time_range('currMonth', 'currWeek', 'currDay', 'currHour');
$filter = [];
if(isset($zone['rtu'])) {
    $code = $zone['rtu'];
    array_push($filter, "r.rtu_kode='$code'");
} elseif(isset($zone['witel'])) {
    $code = $zone['witel'];
    array_push($filter, "r.witel_kode='$code'");
} elseif(isset($zone['divre'])) {
    $code = $zone['divre'];
    array_push($filter, "r.divre_kode='$code'");
}
array_push($filter, "p.pue_value>0");

foreach($currTimes as $item) {
    
    list($key, $startDate, $endDate) = $item;
    $basicFilter = ["(p.timestamp BETWEEN '$startDate' AND '$endDate')"];
    $appliedFilter = array_merge($filter, $basicFilter);
    $appliedFilter = implode(' AND ', $appliedFilter);

    $keySum = $key.'Sum';
    $keyCount = $key.'Count';
    $temp = "(SELECT SUM(p.pue_value) FROM $this->tableName AS p JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode WHERE $appliedFilter) AS $keySum,
        (SELECT COUNT(*) FROM $this->tableName AS p JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode WHERE $appliedFilter) AS $keyCount";
    array_push($queries, $temp);
    
}

$q = 'SELECT ' . implode(', ', $queries);
$query = $this->db->query($q);
$data = $query->row_array();

$result = [
    'currMonth' => null,
    'currWeek' => null,
    'currDay' => null,
    'currHour' => null,
    'timestamp' => date('Y-m-d h:i:s')
];

if(is_array($data)) {

    if(isset($data['currMonthSum'], $data['currMonthCount'])) {
        $result['currMonth'] = $data['currMonthCount'] > 0 ? $data['currMonthSum'] / $data['currMonthCount'] : 0;
    }

    if(isset($data['currWeekSum'], $data['currWeekCount'])) {
        $result['currWeek'] = $data['currWeekCount'] > 0 ? $data['currWeekSum'] / $data['currWeekCount'] : 0;
    }

    if(isset($data['currDaySum'], $data['currDayCount'])) {
        $result['currDay'] = $data['currDayCount'] > 0 ? $data['currDaySum'] / $data['currDayCount'] : 0;
    }

    if(isset($data['currHourSum'], $data['currHourCount'])) {
        $result['currHour'] = $data['currHourCount'] > 0 ? $data['currHourSum'] / $data['currHourCount'] : 0;
    }

}
    
return $this->result = $result;