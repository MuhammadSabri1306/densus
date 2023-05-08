<?php

$year = date('Y');
$this->db
    ->select('pue_value, timestamp')
    ->from($this->tableName)
    ->where('rtu_kode', $rtuCode)
    ->where('YEAR(timestamp)', $year)
    ->order_by('timestamp', 'desc');

$this->result = $this->db->get()->result();