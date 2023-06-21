<?php

$filterLocMain = $this->get_loc_filter($filter, 'loc');
$filterLocRtu = $this->get_loc_filter($filter, 'rtu');
$filterDatePueOff = $this->get_datetime_filter('created_at', $filter, 'pue');
$filterDatePueOn = $this->get_datetime_filter('timestamp', $filter, 'pue');

/*
 * get Witel List and target
 */
$this->db
    ->select('target AS value')
    ->from("$this->tablePueTargetName AS tgt")
    ->where('tgt.witel_kode=loc.witel_kode')
    ->where('quartal', $filter['quartal'])
    ->order_by('id', 'DESC')
    ->limit(1);
$queryTarget = $this->db->get_compiled_select();

$this->db
    ->select("id, divre_kode, divre_name, witel_kode, witel_name, ($queryTarget) AS target")
    ->from("$this->tableLocationName AS loc")
    ->where($filterLocMain)
    ->group_by('witel_kode')
    ->order_by('divre_kode')
    ->order_by('witel_kode');
$witelList = $this->db->get()->result_array();

/*
 * get PUE Offline data
 */
$this->db
    ->select('pue.id_location, loc.witel_kode, pue.pue_value, pue.created_at')
    ->from("$this->tablePueOfflineName AS pue")
    ->join("$this->tableLocationName AS loc", 'loc.id=pue.id_location')
    ->where($filterLocMain)
    ->where($filterDatePueOff)
    ->order_by('loc.divre_kode')
    ->order_by('loc.witel_kode');
$pueOfflineData = $this->db->get()->result_array();

/*
 * get PUE Online data
 */
$this->db
    ->select('rtu.id_lokasi_gepee, loc.witel_kode, pue.pue_value, pue.timestamp')
    ->from("$this->tablePueOnlineName AS pue")
    ->join("$this->tableRtuName AS rtu", 'rtu.rtu_kode=pue.rtu_kode')
    ->join("$this->tableLocationName AS loc", 'loc.id=rtu.id_lokasi_gepee')
    ->where('rtu.id_lokasi_gepee!=""')
    ->where($filterLocRtu)
    ->where($filterDatePueOn);
    // ->order_by('rtu.divre_kode')
    // ->order_by('rtu.witel_kode');
$pueOnlineData = $this->db->get()->result_array();

$witelPue = [];
/*
 * group PUE Offline data
 */
foreach($pueOfflineData as $pueOffline) {
    $witelCode = $pueOffline['witel_kode'];
    if(!isset($witelPue[$witelCode])) {
        $witelPue[$witelCode] = [];
    }

    $idLocation = $pueOffline['id_location'];
    if(!isset($witelPue[$witelCode][$idLocation])) {
        $witelPue[$witelCode][$idLocation] = [
            'offlineSum' => 0,
            'offlineCount' => 0,
            'onlineSum' => 0,
            'onlineCount' => 0
        ];
    }

    $witelPue[$witelCode][$idLocation]['offlineSum'] += $pueOffline['pue_value'];
    $witelPue[$witelCode][$idLocation]['offlineCount']++;
}

/*
 * group PUE Online data
 */
foreach($pueOnlineData as $pueOnline) {
    $witelCode = $pueOnline['witel_kode'];
    if(!isset($witelPue[$witelCode])) {
        $witelPue[$witelCode] = [];
    }
    
    $idLocation = $pueOnline['id_lokasi_gepee'];
    if(!isset($witelPue[$witelCode][$idLocation])) {
        $witelPue[$witelCode][$idLocation] = [
            'offlineSum' => 0,
            'offlineCount' => 0,
            'onlineSum' => 0,
            'onlineCount' => 0
        ];
    }

    $witelPue[$witelCode][$idLocation]['onlineSum'] += $pueOnline['pue_value'];
    $witelPue[$witelCode][$idLocation]['onlineCount']++;
}

/*
 * calculate PUE data
 */
foreach($witelPue as $witelCode => $locItem) {
    foreach($locItem as $locId => $pueItem) {

        $witelPue[$witelCode][$locId]['offline'] = null;
        if($pueItem['offlineCount'] > 0) {
            $witelPue[$witelCode][$locId]['offline'] = $pueItem['offlineSum'] / $pueItem['offlineCount'];
        }

        unset($witelPue[$witelCode][$locId]['offlineSum']);
        unset($witelPue[$witelCode][$locId]['offlineCount']);

        $witelPue[$witelCode][$locId]['online'] = null;
        if($pueItem['onlineCount'] > 0) {
            $witelPue[$witelCode][$locId]['online'] = $pueItem['onlineSum'] / $pueItem['onlineCount'];
        }
        
        unset($witelPue[$witelCode][$locId]['onlineSum']);
        unset($witelPue[$witelCode][$locId]['onlineCount']);
    
        $witelPue[$witelCode][$locId]['value'] = null;
        if(!is_null($witelPue[$witelCode][$locId]['offline'])) {
            $witelPue[$witelCode][$locId]['value'] = $witelPue[$witelCode][$locId]['offline'];
        } elseif($witelPue[$witelCode][$locId]['online']) {
            $witelPue[$witelCode][$locId]['value'] = $witelPue[$witelCode][$locId]['online'];
        }

    }
}

$result = [];
foreach($witelList as $loc) {

    $item = [
        'witel' => [
            'divre_kode' => $loc['divre_kode'],
            'divre_name' => $loc['divre_name'],
            'witel_kode' => $loc['witel_kode'],
            'witel_name' => $loc['witel_name']
        ],
        'categoryCount' => [ 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0 ],
        'stoCount' => 0,
        'pueEntries' => [],
        'target' => (int) $loc['target']
    ];

    $witelCode = $loc['witel_kode'];
    if(isset($witelPue[$witelCode])) {
        foreach($witelPue[$witelCode] as $locId => $locPue) {
            array_push($item['pueEntries'], [
                'id_location' => $locId,
                'pue_offline' => $locPue['offline'],
                'pue_online' => $locPue['online'],
                'pue_value' => $locPue['value']
            ]);

            $categoryCode = is_null($locPue['value']) ? null : $this->get_pue_category($locPue['value']);
            $item['stoCount']++;

            if(array_key_exists($categoryCode, $item['categoryCount'])) {
                $item['categoryCount'][$categoryCode]++;
            }
        }
    }
    
    array_push($result, $item);
}

$this->result = $result;