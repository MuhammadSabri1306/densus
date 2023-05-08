<?php

$queries = [];
$currTimes = get_time_range('currMonth', 'prevMonth', 'currWeek', 'prevWeek', 'currDay', 'prevDay');

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
array_push($filter, 'p.pue_value>0');

foreach($currTimes as $item) {
    
    list($key, $startDate, $endDate) = $item;
    $appliedFilter = array_merge($filter, ["(p.timestamp BETWEEN '$startDate' AND '$endDate')"]);
    $appliedFilter = implode(' AND ', $appliedFilter);

    $temp = "(SELECT COALESCE(AVG(p.pue_value), 0) FROM $this->tableName AS p
        JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode
        WHERE $appliedFilter) AS $key";
    array_push($queries, $temp);
    
}

$q = 'SELECT ' . implode(', ', $queries);
$query = $this->db->query($q);
$data = $query->row_array();

if(!$data) return null;
$result = [ 'timestamp' => date('Y-m-d h:i:s') ];

if($data['currMonth']) {
    if($data['prevMonth']) {
        $data['currMonth'] = (double) $data['currMonth'];
        $data['prevMonth'] = (double) $data['prevMonth'];
        $result['currMonth'] = !$data['prevMonth'] ? 100 : ($data['currMonth'] - $data['prevMonth']) / $data['prevMonth'] * 100;
    } else {
        $result['currMonth'] = 0;
    }
}

if($data['currWeek']) {
    if($data['prevWeek']) {
        $data['currWeek'] = (double) $data['currWeek'];
        $data['prevWeek'] = (double) $data['prevWeek'];
        $result['currWeek'] = ($data['currWeek'] - $data['prevWeek']) / $data['prevWeek'] * 100;
    } else {
        $result['currWeek'] = 0;
    }
}

if($data['currDay']) {
    if($data['prevDay']) {
        $data['currDay'] = (double) $data['currDay'];
        $data['prevDay'] = (double) $data['prevDay'];
        $result['currDay'] = ($data['currDay'] - $data['prevDay']) / $data['prevDay'] * 100;
    } else {
        $result['currDay'] = 0;
    }
}

if($data['currHour']) {
    if($data['prevHour']) {
        $data['currHour'] = (double) $data['currHour'];
        $data['prevHour'] = (double) $data['prevHour'];
        $result['currHour'] = ($data['currHour'] - $data['prevHour']) / $data['prevHour'] * 100;
    } else {
        $result['currHour'] = 0;
    }
}

$this->result = $result;