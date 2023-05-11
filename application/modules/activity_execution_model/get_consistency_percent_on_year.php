<?php

if(!is_null($currUser)) $this->filter_for_curr_user($currUser);

$this->db
    ->select('COUNT(*) AS all_activity, COALESCE(SUM(IF(e.status="approved",1,0)), 0) AS approved_activity')
    ->from("$this->tableName AS e")
    ->join("$this->tableScheduleName AS s", 's.id=e.id_schedule')
    ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
    ->where('s.value', '1')
    ->where('YEAR(e.created_at)', $currYear);
    
$data = $this->db->get()->row();
$result = is_null($data) || $data->all_activity == 0 ? 0 : ($data->approved_activity / $data->all_activity) * 100;
$this->result = $result;