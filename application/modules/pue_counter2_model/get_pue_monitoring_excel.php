<?php

function customRound($numb, $precision = 0) {
    $numbPow = pow(10, $precision);
    $result = floor($numb * $numbPow) / $numbPow;
    $lastDecimal = ($numb - $result) * $numbPow;
    
    if($lastDecimal < 0.5) return $result;

    $result = (ceil($result * $numbPow) + 1) / $numbPow;
    return $result;
}

$currDateTime = new \DateTime();
$currDateTime->modify('first day of this month');
$currDateTime->setTime(0, 0, 0);
$currMonthDate = $currDateTime->format('Y-m-d H:i:s');

$this->db
    ->select('divre_kode, divre_name, witel_kode, witel_name, rtu_kode, rtu_name')
    ->from($this->tableRtuMapName)
    ->order_by('divre_kode')
    ->order_by('witel_kode')
    ->order_by('rtu_kode');
if(isset($zone['witel'])) {
    $this->db->where('witel_kode', $zone['witel']);
} elseif(isset($zone['divre'])) {
    $this->db->where('divre_kode', $zone['divre']);
}
$rtuList = $this->db->get()->result_array();

$tableCounterKeys = [ 'currMonth', 'currWeek', 'currDay' ];
$tableCounters = get_time_range(...$tableCounterKeys);

$this->db
    ->select('pue.rtu_kode, AVG(pue.pue_value) AS pue_value, pue.timestamp, date_format(pue.timestamp, "%Y-%m-%d %H") AS hour')
    ->from("$this->tableName AS pue")
    ->join("$this->tableRtuMapName AS rtu", 'rtu.rtu_kode=pue.rtu_kode')
    ->where('pue.timestamp >=', $tableCounters[0][1])
    ->group_by('rtu_kode')
    ->group_by('hour');
if(isset($zone['witel'])) {
    $this->db->where('rtu.witel_kode', $zone['witel']);
} elseif(isset($zone['divre'])) {
    $this->db->where('rtu.divre_kode', $zone['divre']);
}
$pue1Values = $this->db->get()->result_array();

$this->db
    ->select('pue.rtu_kode, AVG(pue.pue_value) AS pue_value, pue.timestamp, date_format(pue.timestamp, "%Y-%m-%d %H") AS hour')
    ->from("pue_counter_new AS pue")
    ->join("$this->tableRtuMapName AS rtu", 'rtu.rtu_kode=pue.rtu_kode')
    ->where('pue.timestamp >=', $tableCounters[0][1])
    ->group_by('rtu_kode')
    ->group_by('hour');
if(isset($zone['witel'])) {
    $this->db->where('rtu.witel_kode', $zone['witel']);
} elseif(isset($zone['divre'])) {
    $this->db->where('rtu.divre_kode', $zone['divre']);
}
$pue2Values = $this->db->get()->result_array();

$pueValues = [];
try {
    foreach($rtuList as $rtu) {

        $useNewosasePue = false;
        $pues = [];

        foreach($pue2Values as $pue2) {

            $isRtuMatch = $pue2['rtu_kode'] == $rtu['rtu_kode'];
            $isPueValueExists = $isRtuMatch && $pue2['pue_value'] !== null;
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

                $isRtuMatch = $pue1['rtu_kode'] == $rtu['rtu_kode'];
                $isPueValueExists = $isRtuMatch && $pue1['pue_value'] !== null;
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

        $item = [ 'rtu' => $rtu, 'pue_values' => [] ];
        foreach($pues as $pue) {
            $pueCount = count($pue['values']);
            if($pueCount > 0) {
                $pueHourlyAvg = array_sum($pue['values']) / $pueCount;
                $pueHourlyAvg = customRound($pueHourlyAvg, 2);
                array_push($item['pue_values'], [
                    'timestamp' => $pue['timestamp'],
                    'value' => $pueHourlyAvg
                ]);
            }
        }
        array_push($pueValues, $item);

    }

    $tablesData = [];
    foreach($pueValues as $item) {

        $tableRow = $item['rtu'];

        foreach($item['pue_values'] as $pue) {

            $pueTime = strtotime($pue['timestamp']);

            foreach($tableCounters as $tableCounter) {
                list($tableCounterKey, $tableStartDate, $tableEndDate) = $tableCounter;
                if($pueTime >= strtotime($tableStartDate) && $pueTime <= strtotime($tableEndDate)) {
                    if(!array_key_exists($tableCounterKey, $tableRow)) {
                        $tableRow[$tableCounterKey] = [];
                    }
                    array_push($tableRow[$tableCounterKey], $pue['value']);
                }
            }

        }

        foreach($tableCounterKeys as $tableCounterKey) {
            if(array_key_exists($tableCounterKey, $tableRow) && is_array($tableRow[$tableCounterKey])) {
                $tablePueCounterCount = count($tableRow[$tableCounterKey]);
                if($tablePueCounterCount > 0) {
                    $tableRow[$tableCounterKey] = array_sum($tableRow[$tableCounterKey]) / $tablePueCounterCount;
                    $tableRow[$tableCounterKey] = customRound($tableRow[$tableCounterKey], 2);
                } else {
                    $tableRow[$tableCounterKey] = null;
                }
            } else {
                $tableRow[$tableCounterKey] = null;
            }
        }
        array_push($tablesData, $tableRow);
        
    }

} catch(\Throwable $err) {
    dd(strval($err));
}

$this->result = $tablesData;