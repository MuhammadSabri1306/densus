<?php

// 2023, 2022
$srcYear = $filter['year'];
$cmpYear = $filter['year'] - 1;
$filterYearPln = [ $cmpYear, $srcYear ];

function customRound($numb, $precision = 0) {
    $numbPow = pow(10, $precision);

    if($numb - floor($numb) < 0.5) {
        $numb = floor($numb * $numbPow) / $numbPow;
    }

    return ceil($numb * $numbPow) / $numbPow;
}

/*
 * get Gepee Witel Data
 */
$locTargetQuery = '1';
if(isset($filter['witel'])) {
    $locTargetQuery = 'IF(gepee.witel_kode="'.$filter['witel'].'",1,0)';
} elseif(isset($filter['divre'])) {
    $locTargetQuery = 'IF(gepee.divre_kode="'.$filter['divre'].'",1,0)';
}

$selectFields = [
    'gepee.divre_kode', 'gepee.divre_name', 'gepee.witel_kode', 'gepee.witel_name',
    'amc.kode_regional AS amc_kode_regional', 'amc.kode_witel AS amc_kode_witel',
    "$locTargetQuery AS in_target"
];
$this->db
    ->select(implode(', ', $selectFields))
    ->from("$this->tableLocationName AS gepee")
    ->join("$this->tableWitelName AS amc", 'amc.gepee_witel_kode=gepee.witel_kode')
    ->group_by('witel_kode')
    ->order_by('divre_kode')
    ->order_by('witel_kode');
$witelData = $this->db->get()->result_array();

/*
 * get PLN Bill Data
 */
$locTargetQuery = '1';
if(isset($filter['witel'])) {
    $locTargetQuery = 'IF(loc.kode_witel="'.$witelData[0]['amc_kode_witel'].'",1,0)';
} elseif(isset($filter['divre'])) {
    $locTargetQuery = 'IF(loc.kode_regional="'.$witelData[0]['amc_kode_regional'].'",1,0)';
}

$dbAmc = $this->load->database('amc', true);
$dbAmc->select("bill.*, loc.kode_witel, loc.kode_regional, $locTargetQuery AS in_target")
    ->from("$this->tablePlnName AS bill")
    ->join("$this->tableIndoorName AS loc", 'loc.id_pelanggan=bill.id_pelanggan', 'left')
    ->where_in('bill.tahun', $filterYearPln)
    ->order_by('kode_regional')
    ->order_by('kode_witel')
    ->order_by('tahun');
$plnData = $dbAmc->get()->result_array();

$targetMonth = $filter['month'];
$monthRange = $this->get_amc_month_key(1, $targetMonth);
$allMonthRange = $this->get_amc_month_key(1, 12);

$piDivre = [];
$piDivreRowTemplate = [
    'divre_kode' => null,
    'divre_name' => null,
    'bill_total' => [ 'src' => null, 'cmp' => null, 'cmp_full' => null ],
    'saving_target' => null
];
    
$piWitel = [];
$piWitelRowTemplate = [
    'divre_kode' => null,
    'divre_name' => null,
    'witel_kode' => null,
    'witel_name' => null,
    'bill_total' => [ 'src' => null, 'cmp' => null, 'cmp_full' => null ],
    'treg_bill_percent' => null,
    'saving' => [
        'target' => null,
        'value' => null,
        'achievement' => null
    ],
    'cer' => [
        'src_real' => null,
    ]
];

foreach($witelData as $witel) {

    $divreCode = $witel['divre_kode'];
    $witelCode = $witel['witel_kode'];

    if(!isset($piDivre[$divreCode])) {
        $piDivre[$divreCode] = $piDivreRowTemplate;
        $piDivre[$divreCode]['divre_kode'] = $witel['divre_kode'];
        $piDivre[$divreCode]['divre_name'] = $witel['divre_name'];
    }
    
    foreach($plnData as $pln) {

        if($witel['in_target'] && !isset($piWitel[$witelCode])) {
            $piWitel[$witelCode] = $piWitelRowTemplate;
            $piWitel[$witelCode]['divre_kode'] = $witel['divre_kode'];
            $piWitel[$witelCode]['divre_name'] = $witel['divre_name'];
            $piWitel[$witelCode]['witel_kode'] = $witel['witel_kode'];
            $piWitel[$witelCode]['witel_name'] = $witel['witel_name'];
        }

        if($pln['kode_witel'] == $witel['amc_kode_witel']) {
            if($pln['tahun'] == $srcYear) {

                foreach($monthRange as $monthField) {
                    if($witel['in_target'] && $pln[$monthField]) {
                        if(is_null( $piWitel[$witelCode]['bill_total']['src'] )) {
                            $piWitel[$witelCode]['bill_total']['src'] = 0;
                        }
                        $piWitel[$witelCode]['bill_total']['src'] += $pln[$monthField];
                    }

                    if($pln[$monthField]) {
                        if(is_null( $piDivre[$divreCode]['bill_total']['src'] )) {
                            $piDivre[$divreCode]['bill_total']['src'] = 0;
                        }
                        $piDivre[$divreCode]['bill_total']['src'] += $pln[$monthField];
                    }
                }

            } elseif($pln['tahun'] == $cmpYear) {

                foreach($allMonthRange as $monthField) {
                    $inMonthRange = in_array($monthField, $monthRange);
                    if($pln[$monthField]) {

                        if($witel['in_target'] && $inMonthRange) {
                            if(is_null( $piWitel[$witelCode]['bill_total']['cmp'] )) {
                                $piWitel[$witelCode]['bill_total']['cmp'] = 0;
                            }
                            $piWitel[$witelCode]['bill_total']['cmp'] += $pln[$monthField];
                        }

                        if($inMonthRange) {
                            if(is_null( $piDivre[$divreCode]['bill_total']['cmp'] )) {
                                $piDivre[$divreCode]['bill_total']['cmp'] = 0;
                            }
                            $piDivre[$divreCode]['bill_total']['cmp'] += $pln[$monthField];
                        }

                        if($witel['in_target']) {
                            if(is_null( $piWitel[$witelCode]['bill_total']['cmp_full'] )) {
                                $piWitel[$witelCode]['bill_total']['cmp_full'] = 0;
                            }
                            $piWitel[$witelCode]['bill_total']['cmp_full'] += $pln[$monthField];
                        }

                        if(is_null( $piDivre[$divreCode]['bill_total']['cmp_full'] )) {
                            $piDivre[$divreCode]['bill_total']['cmp_full'] = 0;
                        }
                        $piDivre[$divreCode]['bill_total']['cmp_full'] += $pln[$monthField];
                    }
                }

            }
        }

    }

}

$resultDivre = [];
foreach($piDivre as $piDivreKey => $piDivreItem) {

    $row = $piDivreItem;

    $divreSavingTarget = $savingTarget;
    $divreBillCmpFull = $piDivreItem['bill_total']['cmp_full'];
    if($divreSavingTarget && $divreBillCmpFull) {
        $row['saving_target'] = customRound($divreBillCmpFull * $divreSavingTarget, 2);
    }
    
    array_push($resultDivre, $row);

}

$resultWitel = [];
foreach($piWitel as $piWitelItem) {

    $row = $piWitelItem;
    $resultDivreItem = findArray($resultDivre, fn($item) => $item['divre_kode'] == $piWitelItem['divre_kode']);

    $divreBillCmpFull = is_array($resultDivreItem) ? $resultDivreItem['bill_total']['cmp_full'] : null;
    $witelBillCmpFull = $piWitelItem['bill_total']['cmp_full'];
    if($divreBillCmpFull && $witelBillCmpFull) {
        $row['treg_bill_percent'] = customRound($witelBillCmpFull / $divreBillCmpFull * 100, 2);
    }

    $divreSavingTarget = is_array($resultDivreItem) ? $resultDivreItem['saving_target'] : null;
    if($divreSavingTarget && $row['treg_bill_percent']) {
        // $row['saving']['target'] = customRound($row['treg_bill_percent'] * $divreSavingTarget * -1, 2);
        // $row['saving']['target'] = customRound($row['treg_bill_percent'] * $divreSavingTarget, 2);
        $row['saving']['target'] = customRound($row['treg_bill_percent'] / 100 * $divreSavingTarget, 2);
    }

    $witelBillSrc = $piWitelItem['bill_total']['src'];
    $witelBillCmp = $piWitelItem['bill_total']['cmp'];
    if($witelBillSrc && $witelBillCmp) {
        $row['saving']['value'] = customRound($witelBillSrc - $witelBillCmp, 2);
        $row['cer']['src_real'] = customRound($witelBillSrc / $witelBillCmp * 100, 2);
    }

    if($row['saving']['value'] && $row['saving']['target']) {
        // $row['saving']['achievement'] = customRound($row['saving']['target'] - $row['saving']['value'], 2);
        $rowSavingAchv = customRound($row['saving']['target'] + $row['saving']['value'], 2);
        $row['saving']['achievement'] = $rowSavingAchv > 0 ? $rowSavingAchv : null;
    }

    array_push($resultWitel, $row);

}

$this->result = [
    'witel_list' => $resultWitel,
    'treg_list' => $resultDivre,
    'years' => [
        'source' => $srcYear,
        'comparison' => $cmpYear
    ],
    'filter_month' => $filter['month']
];