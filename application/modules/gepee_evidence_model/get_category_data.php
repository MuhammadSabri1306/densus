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

$query = "SELECT cat.id_category, cat.code, cat.category, cat.sub_category, cat.use_target,
    (SELECT COUNT(*) FROM $this->tableName AS evd JOIN $this->tableLocationName AS loc ON loc.id_location=evd.id_location
    $whereQuery) AS data_count FROM $this->tableCategoryName AS cat";

$data = $this->db->query($query)->result_array();
$result = [];

foreach($data as $item) {
    
    if(!isset($result[$item['code']])) {
        $result[$item['code']] = [];
    }

    $temp = $item;
    if(isset($temp['use_target'])) {
        $temp['use_target'] = boolval($temp['use_target']);
    }
    if(isset($temp['data_count'])) {
        $temp['data_count'] = (int) $temp['data_count'];
    }
    array_push($result[$item['code']], $temp);

}

$this->result = $result;