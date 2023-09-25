<?php

$filterLocGepee = $this->get_loc_filter($filter, 'loc');
$filterDateCheck = $this->get_datetime_filter('created_at', $filter, 'check');

$enableMonths = $this->get_enable_months();
$categoryList = $this->get_categories();
$roomList = $this->get_rooms();

$this->db
    ->select('*')
    ->from("$this->tableLocationName AS loc")
    ->where($filterLocGepee)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_name')
    ->order_by('loc.sto_name');
$locationData = $this->db->get()->result_array();

$selectFields = [
    'check.*',
    "IF(check.evidence>'', CONCAT('".base_url(UPLOAD_OXISP_CHECK_EVIDENCE_PATH)."', check.evidence), '#') AS evidence_url"
];
$this->db
    ->select(implode(', ', $selectFields))
    ->from("$this->tableName AS check")
    ->join("$this->tableLocationName AS loc", 'loc.id_sto=check.id_location')
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

    foreach($roomList as $room) {
        foreach($categoryList as $category) {

            $checkIndex = findArrayIndex($checkList, function($checkItem) use ($loc, $category, $room) {
                $isLocMatch = $loc['id_sto'] == $checkItem['id_location'];
                $isCategoryMatch = $category['code'] == $checkItem['check_category_code'];
                $isRoomMatch = $room['id'] == $checkItem['id_room'];
                return $isLocMatch && $isCategoryMatch && $isRoomMatch;
            });

            if($checkIndex >= 0) {

                $itemDate = $checkList[$checkIndex]['created_at'];
                $item = [
                    'is_exists' => true,
                    'is_enable' => $this->is_datetime_enabled($itemDate),
                    'room' => $room,
                    'category' => $category,
                    'check_value' => $checkList[$checkIndex],
                    'year' => (int) $filter['year'],
                    'month' => (int) $filter['month']
                ];
                array_splice($checkList, $checkIndex, 1);

            } else {

                $item = [
                    'is_exists' => false,
                    'is_enable' => $this->is_month_enabled($filter['month'], $filter['year']),
                    'room' => $room,
                    'category' => $category,
                    'check_value' => null,
                    'year' => (int) $filter['year'],
                    'month' => (int) $filter['month']
                ];

            }

            array_push($row['check_list'], $item);

        }
    }

    array_push($checkData, $row);
}

$this->result = [
    'enable_months' => $enableMonths,
    'categories' => $categoryList,
    'rooms' => $roomList,
    'check_data' => $checkData
];