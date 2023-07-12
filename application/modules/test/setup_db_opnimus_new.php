<?php

$this->load->database('opnimus_new');
$setupDivre = true;
$setupWitel = false;
$setupSto = false;

$json = file_get_contents(base_url('assets/sample_newosase_api_location.json'));
$data = json_decode($json);
$dataLocation = $data->result->locations;
$currTimestamp = date('Y-m-d H:i:s');

$divre = [];
$witel = [];
$sto = [];

foreach($dataLocation as $loc) {
    
    $divreKey = $loc->id_regional;
    if($setupDivre && !isset($divre[$divreKey])) {

        $divreNumber = preg_replace('/[^0-9]+/', '', $loc->sname_regional);
        $divreCode = 'TLK-r'.$divreNumber.'000000';

        $divre[$divreKey] = [
            'id' => $loc->id_regional,
            'name' => $loc->regional,
            'sname' => $loc->sname_regional,
            'divre_code' => $divreCode,
            'timestamp' => $currTimestamp
        ];
    }

    $witelKey = $loc->id_witel;
    if($setupWitel && !isset($witel[$witelKey])) {

        $witel[$witelKey] = [
            'id' => $loc->id_witel,
            'name' => $loc->witel,
            'regional_id' => $loc->id_regional,
            'timestamp' => $currTimestamp
        ];
    }

}

if($setupDivre) {
    foreach($divre as $divreItem) {
        $this->db->insert('regional', $divreItem);
    }
}