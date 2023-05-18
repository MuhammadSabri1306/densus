<?php

// $this->db
//     ->select()
//     ->from('master_sto')
//     ->order_by('divre');
// $countFromCI = $this->db->count_all_results();
// $this->db
//     ->select('COUNT(id_sto) AS count')
//     ->from('master_sto')
//     ->order_by('divre');
// $countFromDB = (int) $this->db->get()->row_array()['count'];

$this->db
    ->select()
    ->from('master_sto')
    ->order_by('divre');
$opnSto = $this->db->get()->result_array();
$this->db->close();

$this->load->database('densus');
$this->db
    ->select('divre_kode, divre_name, witel_kode, witel_name')
    ->from('master_lokasi_gepee')
    ->group_by('witel_kode')
    ->order_by('divre_kode')
    ->order_by('witel_kode');
$dnsWitel = $this->db->get()->result_array();
$dnsDivre = array_reduce($dnsWitel, function($divre, $item) {

    $divreKey = $item['divre_kode'];
    if(!isset($divre[$divreKey])) {
        $divre[$divreKey] = [];
    }

    $witelKey = $item['witel_kode'];
    $divre[$divreKey][$witelKey] = $item;

    return $divre;

}, []);

function getOpnimusDivreCode($divreName) {
    preg_match('/\d+/', $divreName, $matches);
    if(!isset($matches[0])) {
        return null;
    }

    return 'TLK-r'.$matches[0].'000000';
}

$witelList = $this->get_witel_code_list();

function getOpnimusWitelData($sto, $dnsDivre, $witelList) {
    $divreCode = getOpnimusDivreCode($sto['divre']);
    if(!$divreCode) return null;

    $currDivre = $dnsDivre[$divreCode];
    if(!$currDivre) return null;

    for($i=0; $i<count($witelList); $i++) {
        
        if($witelList[$i]['name'] == $sto['witel']) {
            $witelCode = $witelList[$i]['code'];
            $i = count($witelList);
        }

    }

    if(!$witelCode) return null;
    return $currDivre[$witelCode];
}

$result = [];
$opnStoIndex = 0;
foreach($opnSto AS $sto) {

    $item = [
        'sto_kode' => $sto['sto'],
        'sto_name' => $sto['sto_desc'],
        'datel' => $sto['datel']
    ];

    $opnSto[$opnStoIndex]['witel'] = str_replace('&nbsp;', ' ', $sto['witel']);
    $sto['witel'] = $opnSto[$opnStoIndex]['witel'];
    
    $currWitel = getOpnimusWitelData($sto, $dnsDivre, $witelList);
    if($currWitel) {
        $item = array_merge($item, $currWitel);
        array_push($result, $item);
        $opnSto[$opnStoIndex] = null;
    }

    $opnStoIndex++;

}

// $unfinishedSto = array_filter($opnSto);
// $unfinishedWitel = array_unique(array_column($opnSto, 'witel'));
// $unfinishedDivre = array_unique(array_column($opnSto, 'divre'));
// $test = compact('result', 'unfinishedSto', 'unfinishedWitel', 'unfinishedDivre');
// dd_json($test);

// dd($countFromCI, $countFromDB, count($opnSto), count($result));

// $insert = $this->db->insert_batch('master_sto_densus', $result);
// dd_json($insert);
$this->result = $result;