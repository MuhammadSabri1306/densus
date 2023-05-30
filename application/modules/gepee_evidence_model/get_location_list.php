<?php

$locFilter = $this->get_location_filter($filter, 'loc');
$dateFilter = $this->get_datetime_filter($filter, 'evd');

if(isset($locFilter['loc.divre_kode'])) {
    $locfields = ['loc.*'];
} else {
    $locfields = ['loc.divre_kode', 'loc.divre_name'];
    $this->db->group_by('divre_kode');
}

$locationList = $this->db
    ->select(implode(', ', $locfields))
    ->from("$this->tableLocationName AS loc")
    ->where($locFilter)
    ->get()
    ->result_array();

$categoryList = $this->db
    ->select('id_category, code, category, sub_category')
    ->from($this->tableCategoryName)
    ->get()
    ->result_array();

$result = [];
if(is_array($locationList)) {
    foreach($locationList as $locItem) {

        $temp = $locItem;

        if(is_array($categoryList)) {
            foreach($categoryList AS $catItem) {

                if(!isset($temp[$catItem['code']])) {
                    $temp[$catItem['code']]['category_name'] = $catItem['category'];
                    $temp[$catItem['code']]['id_category'] = $catItem['id_category'];
                    $temp[$catItem['code']]['count'] = 0;
                    $temp[$catItem['code']]['total'] = 0;
                }

                if(isset($locFilter['loc.divre_kode'])) {
                    $this->db
                        ->select('*')
                        ->from("$this->tableName AS evd")
                        ->where('evd.id_location', $locItem['id_location']);
                } else {
                    $this->db
                        ->select('evd.*')
                        ->from("$this->tableName AS evd")
                        ->join("$this->tableLocationName AS loc", 'loc.id_location=evd.id_location')
                        ->where('loc.divre_kode', $locItem['divre_kode']);
                }

                $itemCount = $this->db
                    ->where('id_category', $catItem['id_category'])
                    ->where($dateFilter)
                    ->count_all_results();
                
                if($itemCount > 0) {
                    $temp[$catItem['code']]['count']++;
                }
                $temp[$catItem['code']]['total']++;

            }
        }

        $temp['scores'] = 0;
        $countAll = 0;
        $totalAll = 0;
        foreach($this->categories as $code => $point) {    
            $countAll += $temp[$code]['count'];
            // $countAll += $temp[$code]['count'] * 100;
            $totalAll += $temp[$code]['total'];
            // $totalAll = $temp[$code]['total'] * $point;
        }

        $temp['scores'] = $totalAll > 0 ? $countAll / $totalAll * 100 : 0;
        array_push($result, $temp);

    }
}

$this->result = $result;