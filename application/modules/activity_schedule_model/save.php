<?php

$this->apply_filter($filter);
$savedSchedule = $this->db
    ->select('s.*, MONTH(s.created_at) AS month')
    ->from("$this->tableName AS s")
    ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
    ->where($this->mysql_updatable_time('s.created_at'))
    ->get()
    ->result_array();
    
$this->apply_filter($filter, ['id', 'month']);
$locationList = $this->db
    ->select()
    ->from("$this->tableLocationName AS l")
    ->get()
    ->result_array();

// SET ITEM TIMESTAMP
$currTimestamp = date('Y-m-d H:i:s');

// UPDATE WHEN EXISTS
$updateData = [];
for($j=0; $j<count($savedSchedule); $j++) {

    $isUpdatable = $this->is_time_updatable($savedSchedule[$j]['created_at']);
    if($isUpdatable) {
        
        $savedItem = $savedSchedule[$j];
        $matchIndex = -1;
        for($i=0; $i<count($schedule); $i++) {

            if(isset($schedule[$i]['id'])) {
                $isMatch = $savedItem['id'] == $schedule[$i]['id'];
            } else {
                $isMatch = $savedItem['id_lokasi'] == $schedule[$i]['location'];
                $isMatch = $isMatch && $savedItem['month'] == $schedule[$i]['month'];
                $isMatch = $isMatch && $savedItem['id_category'] == $schedule[$i]['category'];
            }
            if($isMatch) {
                $matchIndex = $i;
                $i = count($schedule);
            }
        }

        if($matchIndex >= 0) {
            $updateItem = [
                'id' => (int) $savedItem['id'],
                'value' => $schedule[$matchIndex]['value'],
                'updated_at' => $currTimestamp
            ];

            array_push($updateData, $updateItem);
            array_splice($schedule, $matchIndex, 1);
        }
    }
}

$isUpdateSuccess = true;
if(count($updateData) > 0) {
    $this->db->update_batch($this->tableName, $updateData, 'id');
    $isUpdateSuccess = $this->db->affected_rows() > 0;
}

// INSERT UNEXISTS
$isInsertSuccess = true;
if($isUpdateSuccess && count($schedule) > 0) {
    
    $insertData = [];
    list($currYear, $currDate, $currTime) = explode('-', date('Y-d-H:i:s'));
    $currDateTime = new DateTime('now');
    foreach($schedule as $insertItem) {

        $itemDateTimeStr = "$currYear-".$insertItem['month']."-$currDate $currTime";
        $isInsertable = $this->is_time_updatable($itemDateTimeStr);
        $isInsertable = $isInsertable && in_array($insertItem['location'], array_column($locationList, 'id'));
        if($isInsertable) {
            array_push($insertData, [
                'id_category' => (int) $insertItem['category'],
                'id_lokasi' => (int) $insertItem['location'],
                'value' => $insertItem['value'],
                'created_at' => $itemDateTimeStr,
                'updated_at' => $itemDateTimeStr
            ]);
        }
    }
    
    if(count($insertData) > 0) {
        $this->db->insert_batch($this->tableName, $insertData);
        $isInsertSuccess = $this->db->affected_rows() > 0;
    }
}

$result = $isInsertSuccess ? 'successfull'
    : ($isUpdateSuccess ? 'some_error' : 'error');
$this->result = $result;