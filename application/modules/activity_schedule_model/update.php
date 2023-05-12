<?php

$month = $filter['month'];
$divreCode = $filter['divre'];

$timeStr = implode('-', [date('d'), $month, date('Y')]) . ' ' . date('H:i:s');
$timestamp = date('Y-m-d H:i:s', strtotime($timeStr));

$this->filter_for_curr_user($currUser);
$this->db
    ->select($this->tableName.'.*')
    ->from($this->tableName)
    ->join($this->tableLocationName, $this->tableLocationName.'.id='.$this->tableName.'.id_lokasi')
    ->where($this->tableLocationName.'.divre_kode', $divreCode)
    ->where('MONTH(created_at)', $month);

if(isset($filter['witel'])) {
    $this->db->where($this->tableLocationName.'.witel_kode', $filter['witel']);
}

$savedSchedule = $this->db->get()->result_array();
$checked = []; $unchecked = []; $matchedParamsIndex = [];

foreach($savedSchedule AS $saved) {
    $isExists = in_array($saved['id_category'], array_column($params, 'id_category')) && in_array($saved['id_lokasi'], array_column($params, 'id_lokasi'));
    if($isExists) {

        $paramsIndex = -1;
        for($i=0; $i<count($params); $i++) {
            if($params[$i]['id_category'] == $saved['id_category'] && $params[$i]['id_lokasi'] == $saved['id_lokasi']) {
                $paramsIndex = $i;
                $i = count($params);

                array_push($matchedParamsIndex, $paramsIndex);
            }
        }
        
        if($paramsIndex < 0) {
            array_push($unchecked, [
                'id' => $saved['id'],
                'value' => FALSE,
                'updated_at' => $timestamp
            ]);
        } else {
            array_push($checked, [
                'id' => $saved['id'],
                'value' => TRUE,
                'updated_at' => $timestamp
            ]);
        }
    }


}

$checkedSuccess = false;
$uncheckedSuccess = false;

// UPDATE CHECKED
if(count($checked) > 0) {
    $this->db->reset_query();
    $checkedSuccess = $this->db->update_batch($this->tableName, $checked, 'id');
}

// UPDATE UNCHECKED
if(count($unchecked) > 0) {
    $this->db->reset_query();
    $uncheckedSuccess = $this->db->update_batch($this->tableName, $unchecked, 'id');
}

$newChecked = [];
for($i=0; $i<count($params); $i++) {
    if(!in_array($i, $matchedParamsIndex)) {
        array_push($newChecked, [
            'id_category' => $params[$i]['id_category'],
            'id_lokasi' => $params[$i]['id_lokasi'],
            'value' => TRUE,
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ]);
    }
}

if(count($newChecked) > 0) {
    $this->db->insert_batch($this->tableName, $newChecked);
}

$this->result = $checkedSuccess && $uncheckedSuccess;