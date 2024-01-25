<?php

function customRound($numb, $precision = 0) {
    $numbPow = pow(10, $precision);
    $result = floor($numb * $numbPow) / $numbPow;
    $lastDecimal = ($numb - $result) * $numbPow;
    
    if($lastDecimal < 0.5) return $result;

    $result = (ceil($result * $numbPow) + 1) / $numbPow;
    return $result;
}

$this->result = [ 'pue_entries' => [] ];

$currYearDate = strval( date('Y') ).'-01-01 00:00:00';

$this->db
    ->select('rtu_kode, AVG(pue_value) AS pue_value, timestamp, date_format(timestamp, "%Y-%m-%d %H") AS hour')
    ->from($this->tableName)
    ->where('timestamp >=', $currYearDate)
    ->where('rtu_kode', $rtuCode)
    ->group_by('hour');
$pue1Values = $this->db->get()->result_array();

$this->db
    ->select('rtu_kode, AVG(pue_value) AS pue_value, timestamp, date_format(timestamp, "%Y-%m-%d %H") AS hour')
    ->from("pue_counter_new")
    ->where('timestamp >=', $currYearDate)
    ->group_by('rtu_kode')
    ->group_by('hour');
$pue2Values = $this->db->get()->result_array();

$pueEntries = [];
try {
    $useNewosasePue = false;
    $pues = [];

    foreach($pue2Values as $pue2) {

        $isPueValueExists = $pue2['pue_value'] !== null;
        if($isPueValueExists) {
            $pueHour = $pue2['hour'];
            if(!array_key_exists($pueHour, $pues)) {
                $pues[$pueHour] = [
                    'timestamp' => "$pueHour:00:00",
                    'values' => []
                ];
            }
            array_push($pues[$pueHour]['values'], $pue2['pue_value']);
            $useNewosasePue = true;
        }

    }

    if(!$useNewosasePue) {
        foreach($pue1Values as $pue1) {

            $isPueValueExists = $pue1['pue_value'] !== null;
            if($isPueValueExists) {
                $pueHour = $pue1['hour'];
                if(!array_key_exists($pueHour, $pues)) {
                    $pues[$pueHour] = [
                        'timestamp' => "$pueHour:00:00",
                        'values' => []
                    ];
                }
                array_push($pues[$pueHour]['values'], $pue1['pue_value']);
            }

        }
    }

    foreach($pues as $pue) {
        $pueCount = count($pue['values']);
        if($pueCount > 0) {
            $pueHourlyAvg = array_sum($pue['values']) / $pueCount;
            $pueHourlyAvg = customRound($pueHourlyAvg, 2);
            array_push($pueEntries, [
                'timestamp' => $pue['timestamp'],
                'value' => $pueHourlyAvg
            ]);
        }
    }

} catch(\Throwable $err) {
    dd(strval($err));
}

$this->result['pue_entries'] = $pueEntries;