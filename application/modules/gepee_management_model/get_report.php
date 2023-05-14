<?php

/*
 * setup DB Query Filter
 */
$filterLocGepee = $this->get_loc_filter($filter, 'loc');
$filterLocRtu = $this->get_loc_filter($filter, 'rtu');
$filterDateSch = $this->get_datetime_filter('created_at', $filter, 'sch');
$filterDatePueOff = $this->get_datetime_filter('created_at', $filter, 'pue');
$filterDatePueOn = $this->get_datetime_filter('timestamp', $filter, 'pue');

/*
 * get Location data
 */
$this->db
    ->select("loc.*, IF(rtu.id_lokasi_gepee!='', 1, 0) AS is_online")
    ->from("$this->tableLocationName AS loc")
    ->join("$this->tableRtuName AS rtu", 'rtu.id_lokasi_gepee=loc.id', 'left')
    ->where($filterLocGepee)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode');
$locationData = $this->db->get()->result_array();

/*
 * get PUE Offline data
 */
$this->db
    ->select('pue.id_location, pue.pue_value, pue.created_at')
    ->from("$this->tablePueOfflineName AS pue")
    ->join("$this->tableLocationName AS loc", 'loc.id=pue.id_location')
    ->where($filterLocGepee)
    ->where($filterDatePueOff)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode');
$pueOfflineData = $this->db->get()->result_array();

/*
 * get PUE Online data
 */
$this->db
    ->select('rtu.id_lokasi_gepee, pue.pue_value, pue.timestamp')
    ->from("$this->tablePueOnlineName AS pue")
    ->join("$this->tableRtuName AS rtu", 'rtu.rtu_kode=pue.rtu_kode')
    ->where('rtu.id_lokasi_gepee!=""')
    ->where($filterLocRtu)
    ->where($filterDatePueOn)
    ->order_by('rtu.divre_kode')
    ->order_by('rtu.witel_kode');
$pueOnlineData = $this->db->get()->result_array();

$data = [];
foreach($locationData as $location) {

    $row = [
        'location' => $location,
        'performance' => [],
        'performance_summary' => [
            'count_all' => 0,
            'count_approved' => 0,
            'percentage' => 0
        ],
        'pue' => [
            'offline' => null,
            'online' => null,
            'isReachTarget' => false
        ],
        'tagihan_pln' => null,
        'replacement' => null
    ];
    $row['location']['is_online'] = boolval($row['location']['is_online']);

    /*
     * get Category data
     */
    $this->db
        ->select('COUNT(exc.id)')
        ->from("$this->tableScheduleName AS sch")
        ->join("$this->tableExecutionName AS exc", 'exc.id_schedule=sch.id')
        ->where('sch.id_category=cat.id')
        ->where('sch.id_lokasi', $location['id'])
        ->where($filterDateSch);
    $countAllQuery = $this->db->get_compiled_select();
    
    $this->db
        ->select('COUNT(exc.id)')
        ->from("$this->tableScheduleName AS sch")
        ->join("$this->tableExecutionName AS exc", 'exc.id_schedule=sch.id')
        ->where('sch.id_category=cat.id')
        ->where('sch.id_lokasi', $location['id'])
        ->where($filterDateSch)
        ->where('exc.status', 'approved');
    $countApprovedQuery = $this->db->get_compiled_select();
    
    $categoryData = $this->db
        ->select("cat.id, cat.alias, cat.activity, ($countAllQuery) AS count_all, ($countApprovedQuery) AS count_approved")
        ->from("$this->tableCategoryName AS cat")
        ->get()
        ->result_array();
    
    foreach($categoryData as $category) {
        $perfm = $category;
        $perfm['count_all'] = (int) $perfm['count_all'];
        $perfm['count_approved'] = (int) $perfm['count_approved'];
        $perfm['percentage'] = $perfm['count_all'] < 1 ? 0 : $perfm['count_approved'] / $perfm['count_all'] * 100;
        array_push($row['performance'], $perfm);

        $row['performance_summary']['count_all'] += $perfm['count_all'];
        $row['performance_summary']['count_approved'] += $perfm['count_approved'];
    }

    if($row['performance_summary']['count_all'] > 0) {
        $row['performance_summary']['percentage'] = $row['performance_summary']['count_approved'] / $row['performance_summary']['count_all'] * 100;
    }

    /*
     * join PUE Offline data to Result
     */
    $pueOffline = [ 'sum' => 0, 'count' => 0 ];
    for($i=0; $i<count($pueOfflineData); $i++) {

        if($pueOfflineData[$i]['id_location'] == $location['id']) {
            $pueOffline['sum'] += (double) $pueOfflineData[$i]['pue_value'];
            $pueOffline['count']++;
            $pueOfflineData[$i] = null;
        }

    }
    
    if($pueOffline['count'] > 0) {
        $row['pue']['offline'] = $pueOffline['sum'] / $pueOffline['count'];
        $pueOfflineData = array_filter($pueOfflineData);
    }

    /*
     * join PUE Online data to Result
     */
    if($location['is_online']) {
        $pueOnline = [ 'sum' => 0, 'count' => 0 ];
        for($i=0; $i<count($pueOnlineData); $i++) {

            if($pueOnlineData[$i]['id_lokasi_gepee'] == $location['id']) {
                $pueOnline['sum'] += (double) $pueOnlineData[$i]['pue_value'];
                $pueOnline['count']++;
                $pueOnlineData[$i] = null;
            }

        }
        
        if($pueOnline['count'] > 0) {
            $row['pue']['online'] = $pueOnline['sum'] / $pueOnline['count'];
            $pueOnlineData = array_filter($pueOnlineData);
        }
    }

    if(!is_null($row['pue']['online'])) {

        $row['pue']['isReachTarget'] = $row['pue']['online'] > $pueLowLimit;
        
    } elseif(!is_null($row['pue']['offline'])) {

        $row['pue']['isReachTarget'] = $row['pue']['offline'] > $pueLowLimit;

    }
    
    array_push($data, $row);
}

$this->result = $data;