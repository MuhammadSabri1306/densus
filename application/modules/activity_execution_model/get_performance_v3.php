<?php

$locFilter = $this->get_loc_filter($filter);
$dateFilterQuery = $this->get_datetime_filter_query($filter, 's');

$locationList = $this->db
    ->select()
    ->from($this->tableLocationName)
    ->where($locFilter)
    ->order_by('divre_kode')
    ->order_by('witel_kode')
    ->get()
    ->result_array();

$categoryList = $this->db
    ->select()
    ->from($this->tableCategoryName)
    ->get()
    ->result_array();

$startMonth = 1;
$endMonth = 12;
if(isset($filter['datetime'])) {
    $startMonth = (int) date('m', strtotime($filter['datetime'][0]));
    $endMonth = (int) date('m', strtotime($filter['datetime'][1]));
}

$result = [
    'month_list' => [],
    'category_list' => $categoryList,
    'performance' => []
];

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
                AND s.value=1 AND $dateFilterQuery
                ORDER BY UNIX_TIMESTAMP(s.created_at) DESC LIMIT 1";
            $selectExecCount = "SELECT COUNT(*) FROM activity_execution AS e JOIN activity_schedule AS s ON s.id=e.id_schedule
                WHERE s.id_category=$catgId AND s.id_lokasi=$locId AND $dateFilterQuery";
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