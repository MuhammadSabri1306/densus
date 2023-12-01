<?php

// $sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value,no_port,nama_port,tipe_port,satuan, timestamp 
// FROM kwh_counter 
// WHERE rtu_kode='$rtu'
// GROUP BY rtu_kode,DAY(timestamp) ORDER BY kwh_counter.timestamp ASC";    
// $query = $this->db->query($sql);
// return $query->row_array();

$this->db
    ->select('rtu_kode, kwh_value, no_port, nama_port, tipe_port, satuan, timestamp')
    ->from($this->tableKwhName)
    ->where('rtu_kode', $rtu)
    ->orderBy('timestamp');
$kwhData = $this->db->get()->result_array();

$this->result = null;
if(count($kwhData) > 0) {
    
    $kwh = array_reduce($kwhData, function($result, $item) {
        if(count($result) < 1) {
            $result = $item;
        } else {
            $result['kwh_value'] += $item['kwh_value'];
        }
        return $result;
    }, []);

    $kwh['kwh_value'] = round($kwh['kwh_value']);
    $this->result = $kwh;
}