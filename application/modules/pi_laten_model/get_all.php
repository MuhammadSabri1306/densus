<?php

// 2023, 2019, 2022
$srcYear = $filter['year'];
$cmpYear1 = $filter['year'] - 4;
$cmpYear2 = $filter['year'] - 1;

$filterLocGepee = $this->get_loc_filter($filter);
$filterYearPln = [ $cmpYear1, $cmpYear2, $srcYear ];


/*
 * get Gepee Location Data
 */
$this->db
    ->select('*')
    ->from($this->tableLocationName)
    ->where($filterLocGepee)
    ->order_by('divre_kode')
    ->order_by('witel_name')
    ->order_by('sto_name');
$locationData = $this->db->get()->result_array();

/*
 * get PLN Bill Data
 */
$dbAmc = $this->load->database('amc', true);
$monthKey = $this->get_amc_month_key($filter['month']);
$dbAmc->select("id_pelanggan, tahun, $monthKey")
    ->from("$dbAmc->database.t_pln_transaksi")
    ->where_in('tahun', $filterYearPln)
    ->order_by('id_pelanggan');
$plnData = $dbAmc->get()->result_array();


$piResult = [];
foreach($locationData as $loc) {

    $rowBills = [];
    $rowBills[$cmpYear1] = null;
    $rowBills[$cmpYear2] = null;
    $rowBills[$srcYear] = null;

    $rowSaving = [
        [ 'src_year' => $srcYear, 'cmp_year' => $cmpYear1, 'value' => null ],
        [ 'src_year' => $srcYear, 'cmp_year' => $cmpYear2, 'value' => null ],
    ];

    $row = [
        'location' => $loc,
        'bills' => $rowBills,
        'saving' => $rowSaving,
        'cer' => $rowSaving,
        'cef' => $rowSaving
    ];

    foreach($plnData as $pln) {
        if($pln['id_pelanggan'] == $loc['id_pel_pln']) {

            $plnYear = $pln['tahun'];
            $row['bills'][$plnYear] = (int) $pln[$monthKey];

        }
    }

    if($row['bills'][$srcYear] && $row['bills'][$cmpYear1]) {

        $row['saving'][0]['value'] = $row['bills'][$srcYear] - $row['bills'][$cmpYear1];
        $row['cer'][0]['value'] = round( 100 + ( $row['saving'][0]['value'] / $row['bills'][$cmpYear1] * 100 ), 2 );
        // $row['cef'][0]['value'] = (double) bcsub( $row['cer'][0]['value'], 100, 2 );
        $row['cef'][0]['value'] = (double) round( $row['cer'][0]['value'] - 100, 2 );

    }

    if($row['bills'][$srcYear] && $row['bills'][$cmpYear2]) {

        $row['saving'][1]['value'] = $row['bills'][$srcYear] - $row['bills'][$cmpYear2];
        $row['cer'][1]['value'] = round( 100 + ( $row['saving'][1]['value'] / $row['bills'][$cmpYear2] * 100 ), 2 );
        $row['cef'][1]['value'] = (double) round( $row['cer'][1]['value'] - 100, 2 );
        
    }

    array_push($piResult, $row);

}

$this->result = [
    'list' => $piResult,
    'years' => [
        'source' => $srcYear,
        'comparison_1' => $cmpYear1,
        'comparison_2' => $cmpYear2
    ],
    'month' => $filter['month']
];