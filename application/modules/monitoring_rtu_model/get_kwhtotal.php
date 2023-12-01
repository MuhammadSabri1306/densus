<?php

// $sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value
// FROM kwh_counter 
// WHERE rtu_kode='$rtu' AND YEAR(timestamp)=YEAR(CURRENT_DATE) AND MONTH(timestamp)=MONTH(CURRENT_DATE)
// GROUP BY rtu_kode,MONTH(timestamp) ORDER BY kwh_counter.timestamp ASC";    
// $query = $this->db->query($sql);
// return $query->row_array();

$this->load->library('datetime_range');
$currMonthDateRange = $this->datetime_range->get_by_month(date('m'), date('Y'));

$filterRtu = [ 'rtu_kode' => $rtu ];
$filterDate = [
    'timestamp>=' => $currMonthDateRange[0],
    'timestamp<=' => $currMonthDateRange[1]
];

$this->db
    ->select('rtu_kode, kwh_value')
    ->from($this->tableKwhName)
    ->where($filterRtu)
    ->where($filterDate)
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