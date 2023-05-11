<?php

if(!is_null($currUser)) $this->filter_for_curr_user($currUser);
if(isset($filter['divre'])) $this->db->where('l.divre_kode', $filter['divre']);
if(isset($filter['witel'])) $this->db->where('l.witel_kode', $filter['witel']);
if(isset($filter['month'])) $this->db->where('MONTH(e.created_at)', $filter['month']);

$this->db
    ->select('COUNT(*) AS all_activity, SUM(IF(e.status="approved",1,0)) AS approved_activity')
    ->from("$this->tableName AS e")
    ->join("$this->tableScheduleName AS s", 's.id=e.id_schedule')
    ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
    ->where('s.value', '1');

$data = $this->db->get()->row();
$result = $data ? ($data->approved_activity / $data->all_activity) * 100 : 0;
$result = round($result, 2);
$this->result = $result;