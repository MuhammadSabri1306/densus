<?php

$filterLoc = $this->get_location_filter($filter, 'loc');
$filterDate = $this->get_datetime_filter($filter, 'evd');

$this->db
    ->select()
    ->from("$this->tableLocationName AS loc")
    ->where($filterLoc)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode');
$locationList = $this->db->get()->result_array();

$this->db
    ->select()
    ->from($this->tableCategoryName);
$categoryList = $this->db->get()->result_array();

$this->db
    ->select('evd.*')
    ->from("$this->tableName AS evd")
    ->join("$this->tableLocationName AS loc", 'loc.id_location=evd.id_location')
    ->where($filterLoc)
    ->where($filterDate)
    ->group_by('evd.id_location')
    ->group_by('evd.id_category')
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode')
    ->order_by('evd.id_category');
$evd = $this->db->get()->result_array();

$result = [];
foreach($locationList as $loc) {

    $row = [
        'location' => $loc,
        'category_data' => [],
        'summary' => [
            'target' => 0,
            'count' => 0,
            'percentage' => 0
        ]
    ];

    $catItem = [];
    foreach($categoryList as $cat) {

        $codeKey = $cat['code'];
        if(!isset($catItem[$codeKey])) {
            $isUseTarget = boolval($cat['use_target']);
            $catItem[$codeKey]['use_target'] = $isUseTarget;

            if($isUseTarget) {
                $catItem[$codeKey]['checklist'] = [];
            } else {
                $catItem[$codeKey]['count'] = 0;
            }
        }

        if($isUseTarget) {
            $row['summary']['target']++;
            array_push($catItem[$codeKey]['checklist'], false);
        }

        for($i=0; $i<count($evd); $i++) {
            
            $isLocationMatch = $evd[$i]['id_location'] == $loc['id_location'];
            $isCategoryMatch = $evd[$i]['id_category'] == $cat['id_category'];

            if($isLocationMatch && $isCategoryMatch) {
                if($isUseTarget) {
                    $checklistIndex = count($catItem[$codeKey]['checklist']) - 1;
                    $catItem[$codeKey]['checklist'][$checklistIndex] = true;
                    $row['summary']['count']++;
                } else {
                    $catItem[$codeKey]['count']++;
                }
            }
        }
    }

    $row['category_data'] = $catItem;
    if($row['summary']['target'] > 0) {
        $row['summary']['percentage'] = $row['summary']['count'] / $row['summary']['target'] * 100;
    }

    array_push($result, $row);
}

$this->result = [
    'location_list' => $result,
    'category' => array_reduce($categoryList, function($list, $item) {
        $catKey = $item['code'];
        if(!isset($list[$catKey])) {
            $list[$catKey] = [
                'code' => $item['code'],
                'category' => $item['category'],
                'use_target' => $item['use_target'],
                'sub' => []
            ];
        }

        if($catKey == 'D') {
            $list[$catKey] = $item;
        } else {
            array_push($list[$catKey]['sub'], $item);
        }
        return $list;
    }, [])
];