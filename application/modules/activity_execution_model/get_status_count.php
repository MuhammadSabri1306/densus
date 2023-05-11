<?php

if(!is_null($currUser)) $this->filter_for_curr_user($currUser);

$this->db
    ->select('COALESCE(SUM(IF(e.status="approved",1,0)), 0) AS approved, COALESCE(SUM(IF(e.status="rejected",1,0)), 0) AS rejected, COALESCE(SUM(IF(e.status="submitted",1,0)), 0) AS submitted')
    ->from("$this->tableName AS e")
    ->join("$this->tableScheduleName AS s", 's.id=e.id_schedule')
    ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
    ->where('s.value', '1')
    ->where('YEAR(e.created_at)', $currYear);
    
$data = $this->db->get()->row();
$this->result = $data;