<?php

function customRound($numb, $precision = 0) {
    $numbPow = pow(10, $precision);
    $result = floor($numb * $numbPow) / $numbPow;
    $lastDecimal = ($numb - $result) * $numbPow;
    
    if($lastDecimal < 0.5) return $result;

    $result = (ceil($result * $numbPow) + 1) / $numbPow;
    return $result;
}

$currYearDate = strval( date('Y') ).'-01-01 00:00:00';

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

$this->db
    ->select('pue.rtu_kode, AVG(pue.pue_value) AS pue_value, pue.timestamp, date_format(pue.timestamp, "%Y-%m-%d %H") AS hour')
    ->from("$this->tableName AS pue")
    ->join("$this->tableRtuMapName AS rtu", 'rtu.rtu_kode=pue.rtu_kode')
    ->where('pue.timestamp >=', $currYearDate)
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
    ->where('pue.timestamp >=', $currYearDate)
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

        // if($rtu['rtu_kode'] == 'RTU-PTRN-01') {
        //     dd($useNewosasePue);
        // }

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

    $tableCounterKeys = [ 'currMonth', 'currWeek', 'currDay' ];
    $tableCounters = get_time_range(...$tableCounterKeys);
    $tablesData = [];

    $chartDateTime = new \DateTime();
    $chartDateTime->modify('-7 day');
    $chartStartTime = $chartDateTime->getTimestamp();
    $chartData = [];

    $latestPueTime = 0;
    $latestPue = null;

    $highestPueValue = 0;
    $highestPue = null;

    $avgCounterKeys = [ 'currMonth', 'currWeek', 'currDay', 'currHour', 'prevMonth', 'prevWeek', 'prevDay' ];
    $avgCounters = get_time_range(...$avgCounterKeys);
    $avgData = [];

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

            foreach($avgCounters as $avgCounter) {
                list($avgCounterKey, $avgStartDate, $avgEndDate) = $avgCounter;
                if($pueTime >= strtotime($avgStartDate) && $pueTime <= strtotime($avgEndDate)) {
                    if(!array_key_exists($avgCounterKey, $avgData)) {
                        $avgData[$avgCounterKey] = [];
                    }
                    array_push($avgData[$avgCounterKey], $pue['value']);
                }
            }

            if($pueTime >= $chartStartTime) {
                if(!array_key_exists($pue['timestamp'], $chartData)) {
                    $chartData[ $pue['timestamp'] ] = [];
                }
                array_push($chartData[ $pue['timestamp'] ], $pue['value']);
            }

            if(!$latestPue) {
                $latestPueTime = strtotime($pue['timestamp']);
                $latestPue = [
                    'timestamp' => $pue['timestamp'],
                    'pue_value' => $pue['value']
                ];
            } else {
                if($pueTime > $latestPueTime) {
                    $latestPueTime = strtotime($pue['timestamp']);
                    $latestPue['timestamp'] = $pue['timestamp'];
                    $latestPue['pue_value'] = $pue['value'];
                }
            }

            if(!$highestPue) {
                $highestPueValue = $pue['value'];
                $highestPue = [
                    'timestamp' => $pue['timestamp'],
                    'pue_value' => $highestPueValue
                ];
            } else {
                if($pue['value'] > $highestPueValue) {
                    $highestPueValue = $pue['value'];
                    $highestPue['timestamp'] = $pue['timestamp'];
                    $highestPue['pue_value'] = $highestPueValue;
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

    $chartData = array_map(function($pueValues, $timestamp) {
        if(is_array($pueValues)) {
            $pueValuesCount = count($pueValues);
            if($pueValuesCount > 0) {
                $pueValue = array_sum($pueValues) / $pueValuesCount;
                $pueValue = customRound($pueValue, 2);
            } else {
                $pueValue = 0;
            }
        } else {
            $pueValue = 0;
        }
        return [ 'timestamp' => $timestamp, 'pue_value' => $pueValue ];
    }, $chartData, array_keys($chartData));

    foreach($avgCounterKeys as $key) {
        if(array_key_exists($key, $avgData) && is_array($avgData[$key])) {
            $avgItemCount = count($avgData[$key]);
            if($avgItemCount > 0) {
                $avgData[$key] = array_sum($avgData[$key]) / $avgItemCount;
                $avgData[$key] = customRound($avgData[$key], 2);
            } else {
                $avgData[$key] = null;
            }
        } else {
            $avgData[$key] = null;
        }
    }

    $perfNodeNames = [ 'Month', 'Week', 'Day' ];
    $perfData = [];
    foreach($perfNodeNames as $nodeName) {
        
        $avgCurrKey = "curr$nodeName";
        $avgPrevKey = "prev$nodeName";

        $currValue = array_key_exists($avgCurrKey, $avgData) ? $avgData[$avgCurrKey] : null;
        $prevValue = array_key_exists($avgPrevKey, $avgData) ? $avgData[$avgPrevKey] : null;

        if($currValue !== null && $prevValue !== null) {
            if($prevValue) {
                $perfData[$avgCurrKey] = ($prevValue - $currValue) / $prevValue * 100;
                $perfData[$avgCurrKey] = customRound($perfData[$avgCurrKey], 2);
            } else {
                $perfData[$avgCurrKey] = -100;
            }
        } else {
            $perfData[$avgCurrKey] = null;
        }

    }

} catch(\Throwable $err) {
    dd(strval($err));
}

$this->result = [
    'tables_data' => $tablesData,
    'chart_data' => $chartData,
    'latest_pue' => $latestPue,
    'highest_pue' => $highestPue,
    'averages' => $avgData,
    'performances' => $perfData,
];