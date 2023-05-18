<?php

$result = [
    'hasLowLimit' => false,
    'lowLimit' => $this->defaultLowLimit,
    'id' => null
];

$filterDate = $this->get_datetime_filter($filter);
$this->db
    ->select('id, pue_low_limit')
    ->from($this->tableName)
    ->where($filterDate)
    ->order_by('created_at', 'DESC');
$targetRow = $this->db->row_array();

if($targetRow) {
    $result['hasLowLimit'] = true;
    $result['lowLimit'] = (double) $targetRow['pue_low_limit'];
    $result['id'] = $targetRow['id'];
}

$this->result = $result;