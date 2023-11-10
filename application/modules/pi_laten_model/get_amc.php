<?php

// 2023, 2019, 2022
$srcYear = $filter['year'];
$cmpYear1 = $filter['year'] - 4;
$cmpYear2 = $filter['year'] - 1;

$filterWitelGepee = $this->get_loc_filter($filter);
$filterLocAmc = [];
$filterYearPln = [ $cmpYear1, $cmpYear2, $srcYear ];

/*
 * get GEPEE Witel Data
 */
/* SELECT loc.divre_kode, loc.divre_name, loc.witel_kode, loc.witel_name, amc.kode_witel
FROM master_sto_densus AS loc
JOIN master_amc_witel AS amc ON amc.gepee_witel_kode=loc.witel_kode
GROUP BY witel_kode */
$selectFields = [
    'gepee.divre_kode', 'gepee.divre_name', 'gepee.witel_kode', 'gepee.witel_name',
    'amc.kode_regional AS amc_kode_regional', 'amc.kode_witel AS amc_kode_witel'
];
$this->db
    ->select(implode(', ', $selectFields))
    ->from("$this->tableLocationName AS gepee")
    ->join("$this->tableWitelName AS amc", 'amc.gepee_witel_kode=gepee.witel_kode')
    ->where($filterWitelGepee)
    ->group_by('witel_kode')
    ->order_by('divre_kode')
    ->order_by('witel_name');
$witelData = $this->db->get()->result_array();

/*
 * get AMC Location Data
 */
/* SELECT * FROM t_pln_indoor
ORDER BY nama_pelanggan, alamat, lokasi */
// if(isset($filter['divre'])) $filterLocAmc['kode_regional'] = $witelData[0]['amc_kode_regional'];
// if(isset($filter['witel'])) $filterLocAmc['kode_witel'] = $witelData[0]['amc_kode_witel'];
// $dbAmc->select('*')
//     ->from($this->tableIndoorName)
//     ->where($filterLocAmc)
//     ->order_by('nama_pelanggan')
//     ->order_by('alamat')
//     ->order_by('lokasi');
// $locationData = $dbAmc->get()->result_array();

/*
 * get PLN Bill Data
 */
$monthKey = $this->get_amc_month_key($filter['month']);
if(isset($filter['divre'])) $filterLocAmc['kode_regional'] = $witelData[0]['amc_kode_regional'];
if(isset($filter['witel'])) $filterLocAmc['kode_witel'] = $witelData[0]['amc_kode_witel'];

$dbAmc = $this->load->database('amc', true);
$selectFields = [
    'bill.id_pelanggan', 'bill.tahun', "bill.$monthKey AS value",
    'loc.nama_pelanggan', 'loc.alamat', 'loc.lokasi', 'loc.kode_witel'
];

$dbAmc->select(implode(', ', $selectFields))
    ->from("$this->tablePlnName AS bill")
    ->join("$this->tableIndoorName AS loc", 'loc.id_pelanggan=bill.id_pelanggan')
    ->where($filterLocAmc)
    ->where_in('tahun', $filterYearPln)
    ->order_by('tahun')
    ->order_by('nama_pelanggan')
    ->order_by('alamat')
    ->order_by('lokasi');
$plnData = $dbAmc->get()->result_array();

$piResult = [];
foreach($witelData as $witel) {
    foreach($plnData as $pln) {

        if($pln['kode_witel'] == $witel['amc_kode_witel']) {

            $index = findArrayIndex($piResult, fn($item) => $item['location']['id_pelanggan'] == $pln['id_pelanggan']);
            if($index < 0) {
                $rowBills = [];
                $rowBills[$cmpYear1] = null;
                $rowBills[$cmpYear2] = null;
                $rowBills[$srcYear] = null;
            
                $rowSaving = [
                    [ 'src_year' => $srcYear, 'cmp_year' => $cmpYear1, 'value' => null ],
                    [ 'src_year' => $srcYear, 'cmp_year' => $cmpYear2, 'value' => null ],
                ];
        
                $rowLoc = [
                    'id_pelanggan' => $pln['id_pelanggan'],
                    'nama_pelanggan' => $pln['nama_pelanggan'],
                    'alamat' => $pln['alamat'],
                    'lokasi' => $pln['lokasi'],
                    'name' => $pln['nama_pelanggan'].' '.$pln['alamat'].' '.$pln['lokasi'],
                    ...$witel
                ];
        
                $row = [
                    'location' => $rowLoc,
                    'bills' => $rowBills,
                    'saving' => $rowSaving,
                    'cer' => $rowSaving,
                    'cef' => $rowSaving
                ];
            } else {
                $row = $piResult[$index];
            }
        
            $plnYear = $pln['tahun'];
            $row['bills'][$plnYear] = (int) $pln['value'];

            if($index < 0) {
                array_push($piResult, $row);
            } else {
                $piResult[$index] = $row;
            }

        }
    
    }
}

// dd($filter['month'], $monthKey);
// dd_json($piResult[0]);

foreach($piResult as $index => $row) {

    if($row['bills'][$srcYear] && $row['bills'][$cmpYear1]) {

        $row['saving'][0]['value'] = $row['bills'][$srcYear] - $row['bills'][$cmpYear1];
        $row['cer'][0]['value'] = round( 100 + ( $row['saving'][0]['value'] / $row['bills'][$cmpYear1] * 100 ), 2 );
        $row['cef'][0]['value'] = (double) round( $row['cer'][0]['value'] - 100, 2 );

    }

    if($row['bills'][$srcYear] && $row['bills'][$cmpYear2]) {

        $row['saving'][1]['value'] = $row['bills'][$srcYear] - $row['bills'][$cmpYear2];
        $row['cer'][1]['value'] = round( 100 + ( $row['saving'][1]['value'] / $row['bills'][$cmpYear2] * 100 ), 2 );
        $row['cef'][1]['value'] = (double) round( $row['cer'][1]['value'] - 100, 2 );
        
    }

    $piResult[$index] = $row;

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