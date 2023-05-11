<?php

$result = [
    'total_activity' => null,
    'total_approved' => null,
    'unactive_witel' => null
];

if(!is_null($currUser)) $this->filter_for_curr_user($currUser);
if(isset($filter['divre'])) $this->db->where('l.divre_kode', $filter['divre']);
if(isset($filter['witel'])) $this->db->where('l.witel_kode', $filter['witel']);
if(isset($filter['month'])) $this->db->where('MONTH(e.created_at)', $filter['month']);

$this->db
    ->select('COUNT(*) AS total, SUM(IF(e.status="approved",1,0)) AS total_approved')
    ->from("$this->tableName AS e")
    ->join("$this->tableScheduleName AS s", 's.id=e.id_schedule')
    ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
    ->where('s.value', '1');

$data = $this->db->get()->row();
if($data) {
    $result['total_activity'] = (int) $data->total;
    $result['total_approved'] = (int) $data->total_approved;
}

if(!is_null($currUser)) $this->filter_for_curr_user($currUser);
if(isset($filter['divre'])) $this->db->where('l.divre_kode', $filter['divre']);
if(isset($filter['witel'])) $this->db->where('l.witel_kode', $filter['witel']);
if(isset($filter['month'])) {
    $this->db->where("s.id IN (SELECT id_schedule FROM $this->tableName) WHERE MONTH(created_at)=".$filter['month']);
} else {
    $this->db->where("s.id IN (SELECT id_schedule FROM $this->tableName)");
}
$hasActivityCount = $this->db
    ->distinct()
    ->select('l.witel_name')
    ->from("$this->tableScheduleName AS s")
    ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
    ->get()
    ->num_rows();

if(!is_null($currUser)) $this->filter_for_curr_user($currUser);
if(isset($filter['divre'])) $this->db->where('l.divre_kode', $filter['divre']);
if(isset($filter['witel'])) $this->db->where('l.witel_kode', $filter['witel']);
$witelCount = $this->db
    ->select('witel_kode')
    ->distinct()
    ->from("$this->tableLocationName AS l")
    ->get()
    ->num_rows();
    
if($hasActivityCount && $witelCount) {
    $result['unactive_witel'] = $witelCount - $hasActivityCount;
}

$this->result = $result;