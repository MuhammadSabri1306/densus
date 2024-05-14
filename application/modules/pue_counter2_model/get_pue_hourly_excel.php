<?php

$this->load->helper('number');

$currDateTime = new \DateTime();
$currDateTime->modify('first day of this month');
$currDateTime->setTime(0, 0, 0);
$currMonthDate = $currDateTime->format('Y-m-d H:i:s');

$this->db
    ->select('*')
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
    ->select('pue.rtu_kode, AVG(pue.pue_value) AS pue_value, pue.timestamp, date_format(pue.timestamp, "%Y-%m-%d %H") AS hour, no_port, satuan')
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
    ->select('pue.rtu_kode, AVG(pue.pue_value) AS pue_value, pue.timestamp, date_format(pue.timestamp, "%Y-%m-%d %H") AS hour, no_port, satuan')
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
            $isPueValueExists = $pue2['pue_value'] !== null;
            if($isRtuMatch && $isPueValueExists) {
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

                $isRtuMatch = $pue1['rtu_kode'] == $rtu['rtu_kode'];
                $isPueValueExists = $pue1['pue_value'] !== null;
                if($isRtuMatch && $isPueValueExists) {
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

    }

} catch(\Throwable $err) {
    dd(strval($err));
}

$this->result = $data;