<?php

$this->db
    ->select('pue_value, timestamp')
    ->from($this->tableName)
    ->where('rtu_kode', $rtuCode)
    ->order_by('timestamp', 'desc');

$this->result = $this->db->get()->row();