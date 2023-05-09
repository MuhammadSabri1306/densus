<?php

$locationFilter = [];
if(is_array($filter)) {
    if($filter['witel']) $locationFilter['witel_kode'] = $filter['witel'];
    if($filter['divre']) $locationFilter['divre_kode'] = $filter['divre'];
}
if(is_array($this->currUser)) {
    if($this->currUser['level'] == 'witel') $locationFilter['witel_kode'] = $this->currUser['locationId'];
    if($this->currUser['level'] == 'divre') $locationFilter['divre_kode'] = $this->currUser['locationId'];
}
foreach($locationFilter as $lfKey => $lfVal) {
    $this->db->where($lfKey, $lfVal);
}
$locationList = $this->db
    ->select()
    ->from($this->tableLocationName)
    ->order_by('divre_kode')
    ->order_by('witel_kode')
    ->get()
    ->result_array();

$categoryList = $this->db
    ->select()
    ->from($this->tableCategoryName)
    ->get()
    ->result_array();

$startMonth = $filter['month'] ? (int) $filter['month'] : 1;
$endMonth = $filter['month'] ? (int) $filter['month'] : 12;
$currYear = date('Y');
$result = [ 'month_list' => [], 'category_list' => $categoryList, 'performance' => [] ];
foreach($locationList as $locData) {

    $item = [ 'location' => $locData, 'item' => [] ];

    for($month=$startMonth; $month<=$endMonth; $month++) {

        if(!in_array($month, $result['month_list'])) {
            array_push($result['month_list'], $month);
        }

        foreach($categoryList as $catgData) {
            
            $locId = $locData['id'];
            $catgId = $catgData['id'];
            $selectScheduleId = "SELECT s.id FROM activity_schedule AS s WHERE s.id_category=$catgId AND s.id_lokasi=$locId
                AND MONTH(s.created_at)='$month' AND YEAR(s.created_at)='$currYear' AND s.value=1
                ORDER BY UNIX_TIMESTAMP(s.created_at) DESC LIMIT 1";
            $selectExecCount = "SELECT COUNT(*) FROM activity_execution AS e JOIN activity_schedule AS s ON s.id=e.id_schedule
                WHERE s.id_category=$catgId AND s.id_lokasi=$locId AND MONTH(s.created_at)='$month' AND YEAR(s.created_at)='$currYear'";
            $selectApprovedCount = $selectExecCount . ' AND e.status="approved"';

            $querySelect = "SELECT ($selectScheduleId) AS id_schedule, ($selectExecCount) AS exec_count,
                ($selectApprovedCount) AS approved_count";
            $queryResult = $this->db->query($querySelect)->row_array();

            $colItem = null;
            if(is_array($queryResult)) {
                $colItem = $queryResult;
                $colItem['percent'] = $queryResult['exec_count'] < 1 ? 0 : $queryResult['approved_count'] / $queryResult['exec_count'] * 100;
                $colItem['isExists'] = $queryResult['id_schedule'] ? true : false;
            }
            array_push($item['item'], $colItem);
                            
        }
    }

    array_push($result['performance'], $item);

}

$this->result = $result;