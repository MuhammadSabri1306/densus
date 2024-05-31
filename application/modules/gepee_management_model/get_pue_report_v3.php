<?php
$this->load->helper('number');
$this->load->helper('array');

$filterLocMain = $this->get_loc_filter($filter, 'loc');
$filterLocRtu = $this->get_loc_filter($filter, 'rtu');
$filterLocTgt = $this->get_loc_filter($filter);
$filterDatePueOff = $this->get_datetime_filter('created_at', $filter, 'pue');
$filterDatePueOn = $this->get_datetime_filter('timestamp', $filter, 'pue');

/*
 * get Witel List and target
 */
$this->db
    ->select('witel_kode, target')
    ->from("$this->tablePueTargetName AS tgt")
    ->where($filterLocTgt)
    ->where('quartal', $filter['quartal']);
$witelPueTargets = $this->db->get()->result_array();

$this->db
    ->select('*')
    ->from("$this->tableLocationName AS loc")
    ->where($filterLocMain)
    ->where('loc.tipe_perhitungan', 'pue')
    ->order_by('divre_kode')
    ->order_by('witel_kode');
$locations = $this->db->get()->result_array();

/*
 * get PUE Online data
 */
$this->db
    ->select('rtu.id_lokasi_gepee, pue.pue_value')
    ->from("$this->tablePueOnlineName AS pue")
    ->join("$this->tableRtuName AS rtu", 'rtu.rtu_kode=pue.rtu_kode')
    ->join("$this->tableLocationName AS loc", 'loc.id=rtu.id_lokasi_gepee')
    ->where('rtu.id_lokasi_gepee!=""')
    ->where($filterLocRtu)
    ->where($filterDatePueOn)
    ->group_start()
        ->where('HOUR(pue.timestamp)>=', 23)
        ->or_where('HOUR(pue.timestamp)<=', 4)
    ->group_end();
$pueOnlineData1 = $this->db->get()->result_array();

/*
 * get PUE Online data 2
 */
$this->db
    ->select('rtu.id_lokasi_gepee, pue.pue_value')
    ->from("$this->tablePueOnlineNewName AS pue")
    ->join("$this->tableRtuName AS rtu", 'rtu.rtu_kode=pue.rtu_kode')
    ->join("$this->tableLocationName AS loc", 'loc.id=rtu.id_lokasi_gepee')
    ->where('rtu.id_lokasi_gepee!=""')
    ->where($filterLocRtu)
    ->where($filterDatePueOn)
    ->group_start()
        ->where('HOUR(pue.timestamp)>=', 23)
        ->or_where('HOUR(pue.timestamp)<=', 4)
    ->group_end();
$pueOnlineData2 = $this->db->get()->result_array();

$witelPues = [];
foreach($locations as $loc) {

    $pueOnline = [ 'sum' => 0, 'count' => 0 ];
    $usePueOnlineNew = false;

    for($i=0; $i<count($pueOnlineData2); $i++) {
        if($pueOnlineData2[$i]['id_lokasi_gepee'] == $loc['id']) {
            $usePueOnlineNew = true;
            $pueOnline['sum'] += (double) $pueOnlineData2[$i]['pue_value'];
            $pueOnline['count']++;
        }
    }

    if(!$usePueOnlineNew) {
        for($j=0; $j<count($pueOnlineData1); $j++) {
            if($pueOnlineData1[$j]['id_lokasi_gepee'] == $loc['id']) {
                $pueOnline['sum'] += (double) $pueOnlineData1[$j]['pue_value'];
                $pueOnline['count']++;
            }
        }
    }

    $loc['pue_offline'] = null;
    $loc['pue_online'] = $pueOnline['count'] < 1 ? null : customRound($pueOnline['sum'] / $pueOnline['count'], 2);

    $wpIndex = findArrayIndex($witelPues, fn($item) => $item['witel']['witel_kode'] == $loc['witel_kode']);
    if($wpIndex < 0) {

        $witelRow = [
            'witel' => [
                'divre_kode' => $loc['divre_kode'],
                'divre_name' => $loc['divre_name'],
                'witel_kode' => $loc['witel_kode'],
                'witel_name' => $loc['witel_name']
            ],
            'categoryCount' => [ 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0 ],
            'locations' => [ $loc ],
            'target' => null
        ];

        $witelPueTargetItem = findArray($witelPueTargets, fn($item) => $item['witel_kode'] == $loc['witel_kode']);
        if($witelPueTargetItem) {
            $witelRow['target'] = (int) $witelPueTargetItem['target'];
        }

        if(!is_null($loc['pue_online'])) {
            $categoryCode = $this->get_pue_category($loc['pue_online']);
            $witelRow['categoryCount'][$categoryCode]++;
        }

        array_push($witelPues, $witelRow);

    } else {

        array_push($witelPues[$wpIndex]['locations'], $loc);

        if(!is_null($loc['pue_online'])) {
            $categoryCode = $this->get_pue_category($loc['pue_online']);
            $witelPues[$wpIndex]['categoryCount'][$categoryCode]++;
        }

    }

}

$this->result = $witelPues;