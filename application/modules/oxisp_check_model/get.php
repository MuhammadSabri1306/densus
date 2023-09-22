<?php

$filterLocGepee = $this->get_loc_filter($filter, 'loc');
$filterDateCheck = $this->get_datetime_filter('created_at', $filter, 'check');

$enableMonths = $this->get_enable_months();
$categoryList = $this->get_categories();
$monthColumns = $this->get_list_months($filter['datetime'][0], $filter['datetime'][1]);

$this->db
    ->select('*')
    ->from("$this->tableLocationName AS loc")
    ->where($filterLocGepee)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_name')
    ->order_by('loc.sto_name');
$locationData = $this->db->get()->result_array();

$this->db
    ->select('check.*')
    ->from("$this->tableName AS check")
    ->join("$this->tableLocationName AS loc", 'loc.id=check.id_location')
    ->where($filterLocGepee)
    ->where($filterDateCheck)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_name')
    ->order_by('loc.sto_name')
    ->order_by('check.created_at');
$checkList = $this->db->get()->result_array();

$checkData = [];
foreach($locationData as $loc) {

    $row = $loc;
    $row['check_list'] = [];

    foreach($monthColumns as $month) {
        foreach($categoryList as $category) {

            $checkIndex = findArrayIndex($checkList, function($checkItem) use ($loc, $category, $month) {
                $isLocMatch = $loc['id'] == $checkItem['id_location'];
                $isCategoryMatch = $category['code'] == $checkItem['check_category_code'];
                $checkMonth = date('n', strtotime($checkItem['created_at']));
                $isMonthMatch = $month == $checkItem;
                return $isLocMatch && $isCategoryMatch && $isMonthMatch;
            });

            if($checkIndex >= 0) {

                $item = [
                    'is_exists' => true,
                    'is_enable' => in_array($month, $enableMonths),
                    'month' => $month,
                    'category' => $category,
                    'check_value' => $checkList[$checkIndex],
                ];
                array_splice($checkList, $checkIndex, 1);

            } else {

                $item = [
                    'is_exists' => false,
                    'is_enable' => in_array($month, $enableMonths),
                    'month' => $month,
                    'category' => $category,
                    'check_value' => null
                ];

            }

            array_push($row['check_list'], $item);

        }
    }

    array_push($checkData, $row);
}

$this->result = [
    'enable_months' => $enableMonths,
    'target_months' => $monthColumns,
    'categories' => $categoryList,
    'check_data' => $checkData
];