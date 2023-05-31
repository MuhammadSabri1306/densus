<?php

$locFilter = $this->get_location_filter($filter, 'loc');
$dateFilter = $this->get_datetime_filter($filter, 'evd');

$this->db
    ->select('loc.*')
    ->from("$this->tableLocationName AS loc")
    ->where($locFilter);
$locationList = $this->db->get()->result_array();

$categoryList = $this->db
    ->select('id_category, code, category, sub_category')
    ->from($this->tableCategoryName)
    ->where('use_target', 1);
$categoryList = $this->db->get()->result_array();
$targetCount = count($categoryList);

$this->db
    ->select('evd.id_location, evd.id_category, COUNT(evd.id) AS count')
    ->from("$this->tableName AS evd")
    ->join("$this->tableLocationName AS loc", 'loc.id_location=evd.id_location')
    ->where($locFilter)
    ->where($dateFilter)
    ->group_by('evd.id_location')
    ->group_by('evd.id_category')
    ->order_by('evd.id_location')
    ->order_by('evd.id_category');
$evdData = $this->db->get()->result_array();

$resultWitel = [];
foreach($locationList as $loc) {

    $count = 0;
    foreach($categoryList as $cat) {
        for($i=0; $i<count($evdData); $i++) {

            $isMatch = $loc['id_location'] == $evdData[$i]['id_location'];
            $isMatch = $isMatch && $cat['id_category'] == $evdData[$i]['id_category'];
            if($isMatch) {
                $count++;
                $evdData[$i] = null;
            }

        }

        $evdData = array_values(array_filter($evdData));
    }

    $row = $loc;
    $row['scores'] = $targetCount < 1 ? 0 : $count / $targetCount * 100;
    array_push($resultWitel, $row);

}

if(isset($filter['divre'])) {
    $this->result = $resultWitel;
    return $this->result;
}

$dataDivre = [];
foreach($resultWitel as $witel) {
    
    $divreCode = $witel['divre_kode'];
    if(!isset($dataDivre[$divreCode])) {
        $dataDivre[$divreCode] = [
            'divre_kode' => $divreCode,
            'divre_name' => $witel['divre_name'],
            'scores' => []
        ];
    }
    
    array_push($dataDivre[$divreCode]['scores'], $witel['scores']);
}

$resultDivre = [];
foreach($dataDivre as $divre) {
    $scores = count($divre['scores']) < 1 ? 0 : array_sum($divre['scores']) / count($divre['scores']);
    $divreItem = $divre;
    $divreItem['scores'] = $scores;
    array_push($resultDivre, $divreItem);
}

$this->result = $resultDivre;