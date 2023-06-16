<?php

$filterLocGepee = $this->get_loc_filter($filter, 'loc');
$filterLocTarget = $this->get_loc_filter($filter, 'tgt');
$filterDateTarget = $this->get_datetime_filter('created_at', $filter, 'tgt');
$filterDatePueOff = $this->get_datetime_filter('created_at', $filter, 'pue');
$filterLocRtu = $this->get_loc_filter($filter, 'rtu');
$filterDatePueOn = $this->get_datetime_filter('timestamp', $filter, 'pue');

$this->db
    ->select('target')
    ->from("$this->tablePueTargetName AS tgt")
    ->where('tgt.witel_kode=loc.witel_kode')
    ->where($filterLocTarget)
    ->where($filterDateTarget)
    ->order_by('id', 'DESC')
    ->limit(1);
$targetQuery = $this->db->get_compiled_select();

$this->db
    ->select("loc.divre_kode, loc.divre_name, loc.witel_kode, loc.witel_name, ($targetQuery) AS target_value")
    ->from("$this->tableLocationName AS loc")
    ->where($filterLocGepee)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode');
$locationList = $this->db->get()->result_array();

$this->db
    ->select('loc.witel_kode, pue.id_location, pue.pue_value, pue.created_at')
    ->from("$this->tablePueOfflineName AS pue")
    ->join("$this->tableLocationName AS loc", 'loc.id=pue.id_location')
    ->where($filterLocGepee)
    ->where($filterDatePueOff)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode');
$pueOfflineData = $this->db->get()->result_array();

$this->db
    ->select('rtu.witel_kode, rtu.id_lokasi_gepee, pue.pue_value, pue.timestamp')
    ->from("$this->tablePueOnlineName AS pue")
    ->join("$this->tableRtuName AS rtu", 'rtu.rtu_kode=pue.rtu_kode')
    ->where('rtu.id_lokasi_gepee!=""')
    ->where('pue.pue_value>0')
    ->where($filterLocRtu)
    ->where($filterDatePueOn)
    ->order_by('rtu.divre_kode')
    ->order_by('rtu.witel_kode');
$pueOnlineData = $this->db->get()->result_array();
$dataSto = [];
foreach($locationList as $loc) {
    $row = [
        'location' => $loc,
        'pue_value' => 0
    ];

    $pueOffline = [];
    for($i=0; $i<count($pueOfflineData); $i++) {
        if($pueOfflineData[$i]['witel_kode'] == $loc['witel_kode']) {
            array_push($pueOffline, (double) $pueOfflineData[$i]['pue_value']);
            $pueOfflineData[$i] = null;
        }

    }
    $pueOfflineData = array_values(array_filter($pueOfflineData));

    $pueOnline = [];
    for($i=0; $i<count($pueOnlineData); $i++) {

        if($pueOnlineData[$i]['witel_kode'] == $loc['witel_kode']) {
            array_push($pueOnline, (double) $pueOnlineData[$i]['pue_value']);
            $pueOnlineData[$i] = null;
        }

    }
    $pueOnlineData = array_filter($pueOnlineData);

    if(count($pueOnline) > 0) {
        $row['pue_value'] =array_sum($pueOnline) / count($pueOnline);
    } elseif(count($pueOffline) > 0) {
        $row['pue_value'] =array_sum($pueOffline) / count($pueOffline);
    }

    array_push($dataSto, $row);
}

$result = [];
foreach($dataSto as $item) {

    $resultIndex = -1;
    for($i=0; $i<count($result); $i++) {
        if($result[$i]['witel']['witel_kode'] == $item['location']['witel_kode']) {
            $resultIndex = $i;
            $i = count($result);
        }
    }

    $categoryCode = $this->get_pue_category($item['pue_value']);
    if($resultIndex < 0) {
        array_push($result, [
            'witel' => $item['location'],
            'categoryCount' => [
                'A' => $categoryCode == 'A' ? 1 : 0,
                'B' => $categoryCode == 'B' ? 1 : 0,
                'C' => $categoryCode == 'C' ? 1 : 0,
                'D' => $categoryCode == 'D' ? 1 : 0
            ],
            'stoCount' => 1,
            'target' => (int) $item['location']['target_value']
        ]);
        unset($result[count($result) - 1]['witel']['target_value']);
    } else {
        $result[$resultIndex]['stoCount']++;
        if($categoryCode == 'A') {
            $result[$resultIndex]['categoryCount']['A']++;
        } elseif($categoryCode == 'B') {
            $result[$resultIndex]['categoryCount']['B']++;
        } elseif($categoryCode == 'C') {
            $result[$resultIndex]['categoryCount']['C']++;
        } elseif($categoryCode == 'D') {
            $result[$resultIndex]['categoryCount']['D']++;
        }
    }
}

$this->result = $result;