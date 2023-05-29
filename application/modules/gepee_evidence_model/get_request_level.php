<?php

$locFilter = $this->get_location_filter($filter);
$locFields = []; $level = 'nasional';

if(isset($locFilter['witel_kode']) || isset($locFilter['id_location'])) {
    $locFields = ['id_location', 'divre_kode', 'divre_name', 'witel_kode', 'witel_name'];
    $level = 'witel';
} elseif(isset($locFilter['divre_kode'])) {
    $locFields = ['divre_kode', 'divre_name'];
    $level = 'divre';
}
if(count($locFields) > 0) {
    
    $this->db
        ->select(implode(',', $locFields))
        ->from($this->tableLocationName)
        ->where($locFilter);

    $result = $this->db->get()->row_array();
    $result['level'] = $level;

} else {
    $result = [ 'level' => $level ];
}

$this->result = $result;