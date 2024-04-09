<?php

if($this->currUser['level'] == 'regional') {
    $divreCode = $this->currUser['locationId'];
} elseif($this->currUser['level'] == 'witel') {
    $witelCode = $this->currUser['locationId'];
}

$filters = [];
$this->load->library('rest_client', [ 'baseUri' => 'https://newosase.telkom.co.id/api/v1' ] );
$this->rest_client->request['query'] = [
    'isArea' => 'hide',
    'isChildren' => 'view'
];

if($divreCode) {
    $filters['divre_kode'] = $divreCode;
    $regional = $this->db
        ->select('*')
        ->from($this->tableNonRegionalName)
        ->where('divre_kode', $divreCode)
        ->get()
        ->row_array();
    if(!$regional) throw new ModelEmptyDataException('Gagal terhubung dengan Newosase.');
    $this->rest_client->request['query']['regional'] = $regional['regional_id'];
}

if($witelCode) {
    $filters['witel_kode'] = $witelCode;
    $witel = $this->db
        ->select('*')
        ->from($this->tableNonWitelName)
        ->where('witel_kode', $witelCode)
        ->get()
        ->row_array();
    if(!$witel) throw new ModelEmptyDataException('Gagal terhubung dengan Newosase.');
    $this->rest_client->request['query']['witel'] = $witel['witel_id'];
}

$this->db
    ->select('*')
    ->from($this->tableRtuName)
    ->where($filters);
$rtuMapData = $this->db->get()->result_array();
$availableRtus = array_map(fn($item) => $item['rtu_kode'], $rtuMapData);

$rtus = [];
try {
    $osaseData = $this->rest_client->sendRequest('GET', 'https://newosase.telkom.co.id/api/v1/parameter-service/mapview');
    foreach($osaseData->result as $regionalItem) {
        foreach($regionalItem->witel as $witelItem) {
            foreach($witelItem->rtu as $rtuItem) {
                array_push($rtus, [
                    'rtu_kode' => $rtuItem->rtu_sname,
                    'rtu_name' => $rtuItem->rtu_name,
                    'is_available' => in_array($rtuItem->rtu_sname, $availableRtus)
                ]);
            }
        }
    }
} catch(\GuzzleHttp\Exception\RequestException $err) {
    // dd( (string) $err );
    // dd( $err->getRequest() );
    // dd( $err->getRequest()->getUri() );
    throw new ModelEmptyDataException('Gagal terhubung dengan Newosase.');
}

$this->result = $rtus;