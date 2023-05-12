<?php

$filterLoc = $this->get_loc_filter($filter, 'loc');
$filterDate = $this->get_datetime_filter($filter, 'sch');

$locationList = $this->db
    ->select()
    ->from("$this->tableLocationName AS loc")
    ->where($filterLoc)
    ->order_by('divre_kode')
    ->order_by('witel_kode')
    ->get()
    ->result_array();

$categoryList = $this->db
    ->select()
    ->from($this->tableCategoryName)
    ->get()
    ->result_array();

$this->db
    ->select('COUNT(id)')
    ->from("$this->tableExecutionName AS exc")
    ->where('exc.id_schedule=sch.id');
$queryCountAllExc = $this->db->get_compiled_select();

$this->db
    ->select('COUNT(id)')
    ->from("$this->tableExecutionName AS exc")
    ->where('exc.id_schedule=sch.id')
    ->where('status', 'approved');
$queryCountApprovedExc = $this->db->get_compiled_select();

$schSelectQuery = "sch.*, MONTH(sch.created_at) AS month,
    ($queryCountAllExc) AS execution_count, ($queryCountApprovedExc) AS approved_count";
$this->db
    ->select($schSelectQuery)
    ->from("$this->tableName AS sch")
    ->join("$this->tableLocationName AS loc", 'loc.id=sch.id_lokasi')
    ->where($filterDate)
    ->where($filterLoc)
    ->order_by('divre_kode')
    ->order_by('witel_kode');
$scheduleList = $this->db->get()->result_array();

$startMonth = 1;
$endMonth = 12;
if(isset($filter['datetime'])) {
    $startMonth = (int) date('m', strtotime($filter['datetime'][0]));
    $endMonth = (int) date('m', strtotime($filter['datetime'][1]));
}

$result = [
    'category_list' => $categoryList,
    'month_list' => [],
    'schedule' => []
];

foreach($locationList as $location) {

    $row = [
        'location' => $location,
        'month_item' => []
    ];

    for($month=$startMonth; $month<=$endMonth; $month++) {

        if(!in_array($month, $result['month_list'])) {
            array_push($result['month_list'], $month);
        }

        $monthData = [
            'month' => $month,
            'category_item' => []
        ];

        foreach($categoryList as $category) {
            
            $locId = $location['id'];
            $catId = $category['id'];
            $categoryData = [
                'category' => $category,
                'schedule_data' => null
            ];

            for($i=0; $i<count($scheduleList); $i++) {
                
                $isMonthMatch = $scheduleList[$i]['month'] == $month;
                $isLocMatch = $scheduleList[$i]['id_lokasi'] == $locId;
                $isCatMatch = $scheduleList[$i]['id_category'] == $catId;
                $isScheduleMatch = $isMonthMatch && $isLocMatch && $isCatMatch;

                if($isScheduleMatch) {

                    $scheduleList[$i]['is_enabled'] = $this->is_time_updatable($scheduleList[$i]['created_at']);
                    $categoryData['schedule_data'] = $scheduleList[$i];
                    array_splice($scheduleList, $i, 1);
                    $i = count($scheduleList) + 1;
                }

            }
            
            array_push($monthData['category_item'], $categoryData);
        }

        array_push($row['month_item'], $monthData);
    }

    array_push($result['schedule'], $row);
}

$this->result = $result;