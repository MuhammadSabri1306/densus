<?php

$locFilter = $this->get_location_filter($filter, 'loc');
$dateFilter = $this->get_datetime_filter($filter, 'evd');

$filter = [];
foreach($locFilter as $key => $val) {
    array_push($filter, "$key='$val'");
}
foreach($dateFilter as $key => $val) {
    array_push($filter, "$key'$val'");
}

$whereQuery = '';
if(count($filter) > 0) {
    $whereQuery = ' WHERE evd.id_category=cat.id_category AND '.implode(' AND ', $filter);
}

$query = "SELECT cat.code, cat.category, cat.use_target, (SELECT COUNT(*) FROM $this->tableName AS evd
    LEFT JOIN $this->tableLocationName AS loc ON loc.id_location=evd.id_location
    $whereQuery) AS count FROM $this->tableCategoryName AS cat";

$data = $this->db->query($query)->result_array();
$result = [];

foreach($data as $item) {
    
    if(!isset($result[$item['code']])) {
        $result[$item['code']] = [ 'category' => $item['category'] ];
        $result[$item['code']]['checkedCount'] = 0;
        $result[$item['code']]['targetCount'] = 0;
    }
    
    if((int) $item['count'] > 0) $result[$item['code']]['checkedCount']++;
    $result[$item['code']]['targetCount']++;
    // if(boolval($item['use_target'])) {
    // }

}

$this->result = $result;