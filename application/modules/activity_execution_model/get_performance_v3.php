<?php

$filterLocMain = $this->get_loc_filter($filter, 'loc');
$filterDateSch = $this->get_datetime_filter_query($filter, 'sch');
$dateFilterQuery = $this->get_datetime_filter_query($filter, 's');

$locationList = $this->db
    ->select()
    ->from("$this->tableLocationName AS loc")
    ->where($filterLocMain)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode')
    ->get()
    ->result_array();

$categoryList = $this->db
    ->select()
    ->from($this->tableCategoryName)
    ->get()
    ->result_array();

$this->db
    ->select('sch.id_lokasi, sch.id_category, exc.*, MONTH(sch.created_at) AS month')
    ->from("$this->tableName AS exc")
    ->join("$this->tableScheduleName AS sch", 'sch.id=exc.id_schedule')
    ->join("$this->tableLocationName AS loc", 'loc.id=sch.id_lokasi')
    ->where('sch.value', 1)
    ->where($filterDateSch)
    ->where($filterLocMain)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode')
    ->order_by('month')
    ->order_by('sch.id_category');
// dd($this->db->get_compiled_select());
$execList = $this->db->get()->result_array();

$startMonth = 1;
$endMonth = (int) date('n');
if(isset($filter['datetime'])) {
    $startMonth = (int) date('n', strtotime($filter['datetime'][0]));
    if(strtotime($filter['datetime'][1]) < strtotime(date('Y-m-d H:i:s'))) {
        $endMonth = (int) date('n', strtotime($filter['datetime'][1]));
    }
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

            $colItem = [
                'id_schedule' => null,
                'exec_count' => 0,
                'approved_count' => 0
            ];
            for($i=0; $i<count($execList); $i++) {

                $isLocMatch = $execList[$i]['id_lokasi'] == $locData['id'];
                $isCatgMatch = $execList[$i]['id_category'] == $catgData['id'];
                $isMonthMatch = (int) $execList[$i]['month'] == $month;

                if($isLocMatch && $isCatgMatch && $isMonthMatch) {

                    if(!$colItem['id_schedule']) {
                        $colItem['id_schedule'] = $execList[$i]['id_schedule'];
                    }

                    $colItem['exec_count']++;
                    if($execList[$i]['status'] == 'approved') {
                        $colItem['approved_count']++;
                    }

                    $execList[$i] = null;

                }

            }

            $execList = array_values(array_filter($execList));
            $colItem['percent'] = $colItem['exec_count'] < 1 ? 0 : $colItem['approved_count'] / $colItem['exec_count'] * 100;
            $colItem['isExists'] = $colItem['id_schedule'] ? true : false;
            array_push($item['item'], $colItem);
                            
        }
    }

    array_push($result['performance'], $item);

}

$this->result = $result;