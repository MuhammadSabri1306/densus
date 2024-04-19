<?php

$this->load->helper('number');

$this->db
    ->select('*')
    ->from($this->tableRtuMapName)
    ->where('rtu_kode', $rtuCode);
$rtu = $this->db->get()->row_array();
if(!$rtu) {
    $this->result = [];
    return $this->result;
}

$currYearDate = strval( date('Y') ).'-01-01 00:00:00';

$this->db
    ->select('rtu_kode, AVG(pue_value) AS pue_value, timestamp, date_format(timestamp, "%Y-%m-%d %H") AS hour, no_port, satuan')
    ->from($this->tableName)
    ->where('timestamp >=', $currYearDate)
    ->where('rtu_kode', $rtuCode)
    ->group_by('hour');
$pue1Values = $this->db->get()->result_array();

$this->db
    ->select('rtu_kode, AVG(pue_value) AS pue_value, timestamp, date_format(timestamp, "%Y-%m-%d %H") AS hour, no_port, satuan')
    ->from("pue_counter_new")
    ->where('timestamp >=', $currYearDate)
    ->where('rtu_kode', $rtuCode)
    ->group_by('rtu_kode')
    ->group_by('hour');
$pue2Values = $this->db->get()->result_array();

$data = [];
try {

    $useNewosasePue = false;
    $pues = [];

    foreach($pue2Values as $pue2) {

        $isPueValueExists = $pue2['pue_value'] !== null;
        if($isPueValueExists) {
            $pueHour = $pue2['hour'];
            if(!array_key_exists($pueHour, $pues)) {
                $pues[$pueHour] = [
                    'no_port' => $pue2['no_port'],
                    'satuan' => $pue2['satuan'],
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
                        'no_port' => $pue1['no_port'],
                        'satuan' => $pue1['satuan'],
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
            array_push($data, [
                'divre_kode' => $rtu['divre_kode'],
                'divre_name' => $rtu['divre_name'],
                'witel_kode' => $rtu['witel_kode'],
                'witel_name' => $rtu['witel_name'],
                'rtu_kode' => $rtu['rtu_kode'],
                'rtu_name' => $rtu['rtu_name'],
                'sto_kode' => $rtu['sto_kode'],
                'lokasi' => $rtu['lokasi'],
                'no_port' => $pue['no_port'],
                'pue_value' => $pueHourlyAvg,
                'satuan' => $pue['satuan'],
                'timestamp' => $pue['timestamp'],
            ]);
        }
    }

} catch(\Throwable $err) {
    dd(strval($err));
}

$this->result = $data;