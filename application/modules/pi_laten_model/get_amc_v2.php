<?php

// 2023, 2022
$srcYear = $filter['year'];
$cmpYear = $filter['year'] - 1;
$filterYearPln = [ $cmpYear, $srcYear ];

function customRound($numb, $precision = 0) {
    return $numb;
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
    'gepee.divre_kode', 'gepee.divre_name', 'gepee.witel_kode', 'gepee.witel_name', "$locTargetQuery AS in_target",
    'amc.kode_regional AS amc_kode_regional', 'amc.kode_witel AS amc_kode_witel'
];

$this->db
    ->select(implode(', ', $selectFields))
    ->from("$this->tableLocationName AS gepee")
    ->join("$this->tableWitelName AS amc", 'amc.gepee_witel_kode=gepee.witel_kode')
    ->group_by('witel_kode')
    ->order_by('divre_kode')
    ->order_by('witel_name');
$witelData = $this->db->get()->result_array();

/*
 * get PLN Bill Data
 */
$dbAmc = $this->load->database('amc', true);
$dbAmc->select('bill.*, loc.nama_pelanggan, loc.alamat, loc.lokasi, loc.kode_witel')
    ->from("$this->tablePlnName AS bill")
    ->join("$this->tableIndoorName AS loc", 'loc.id_pelanggan=bill.id_pelanggan')
    ->where_in('tahun', $filterYearPln)
    ->order_by('tahun')
    ->order_by('nama_pelanggan')
    ->order_by('alamat')
    ->order_by('lokasi');
$plnData = $dbAmc->get()->result_array();

$targetMonth = $filter['month'];
$monthRange = $this->get_amc_month_key(1, $targetMonth);
$allMonthRange = $this->get_amc_month_key(1, 12);

$rowTemplate = [
    'is_filter_applied' => true,
    'location' => [],
    'bill' => [ 'src' => null, 'cmp' => null, 'cmp_full' => null ],
    'bill_fraction' => null,
    'saving' => [
        'target' => null,
        'value' => null,
        'achievement' => null
    ],
    'cer' => [
        'src_real' => null,
    ]
];

$stoList = [];
foreach($witelData as $witel) {
    foreach($plnData as $pln) {
        if($pln['kode_witel'] == $witel['amc_kode_witel']) {

            $stoIndex = findArrayIndex($stoList, fn($item) => $item['location']['id_pelanggan'] == $pln['id_pelanggan']);
            if($stoIndex >= 0) {
                $row = $stoList[$stoIndex];
            } else {
                $row = $rowTemplate;
                $row['location']['id_pelanggan'] = $pln['id_pelanggan'];
                $row['location']['nama_pelanggan'] = $pln['nama_pelanggan'];
                $row['location']['alamat'] = $pln['alamat'];
                $row['location']['lokasi'] = $pln['lokasi'];
                $row['location']['name'] = $pln['nama_pelanggan'].' '.$pln['alamat'].' '.$pln['lokasi'];
                $row['location']['in_target'] = (bool) $witel['in_target'];
                $row['location']['witel_kode'] = $witel['witel_kode'];
                $row['location']['witel_name'] = $witel['witel_name'];
                $row['location']['witel_amc_kode'] = $witel['amc_kode_witel'];
                $row['location']['divre_kode'] = $witel['divre_kode'];
                $row['location']['divre_name'] = $witel['divre_name'];
                $row['location']['divre_amc_kode'] = $witel['amc_kode_regional'];
            }

            if($pln['tahun'] == $srcYear) {
                foreach($monthRange as $monthField) {

                    if($pln[$monthField] > 0) {
                        if(is_null($row['bill']['src'])) {
                            $row['bill']['src'] = 0;
                        }
                        $row['bill']['src'] += $pln[$monthField];
                    }

                }
            } elseif($pln['tahun'] == $cmpYear) {
                foreach($allMonthRange as $monthField) {

                    if($pln[$monthField] > 0 && in_array($monthField, $monthRange)) {
                        if(is_null($row['bill']['cmp'])) {
                            $row['bill']['cmp'] = 0;
                        }
                        $row['bill']['cmp'] += $pln[$monthField];
                    }

                    if($pln[$monthField] > 0) {
                        if(is_null($row['bill']['cmp_full'])) {
                            $row['bill']['cmp_full'] = 0;
                        }
                        $row['bill']['cmp_full'] += $pln[$monthField];
                    }
                }
            }

            if($stoIndex >= 0) {
                $stoList[$stoIndex] = $row;
            } else {
                array_push($stoList, $row);
            }

        }
    }
}

$divreList = [];
$witelList = [];
$nasional = [
    'is_filter_applied' => false,
    'bill' => $rowTemplate['bill']
];

foreach($stoList as $sto) {

    if($sto['location']['in_target']) {

        $witelIndex = findArrayIndex($witelList, fn($witel) => $witel['location']['witel_kode'] == $sto['location']['witel_kode']);
        if($witelIndex >= 0) {
            $rowWitel = $witelList[$witelIndex];
        } else {
            $rowWitel = $rowTemplate;
            $rowWitel['location']['witel_kode'] = $sto['location']['witel_kode'];
            $rowWitel['location']['witel_name'] = $sto['location']['witel_name'];
            $rowWitel['location']['divre_kode'] = $sto['location']['divre_kode'];
            $rowWitel['location']['divre_name'] = $sto['location']['divre_name'];
        }

        if($sto['bill']['src']) {
            if(is_null($rowWitel['bill']['src'])) {
                $rowWitel['bill']['src'] = 0;
            }
            $rowWitel['bill']['src'] += $sto['bill']['src'];
        }
    
        if($sto['bill']['cmp']) {
            if(is_null($rowWitel['bill']['cmp'])) {
                $rowWitel['bill']['cmp'] = 0;
            }
            $rowWitel['bill']['cmp'] += $sto['bill']['cmp'];
        }
    
        if($sto['bill']['cmp_full']) {
            if(is_null($rowWitel['bill']['cmp_full'])) {
                $rowWitel['bill']['cmp_full'] = 0;
            }
            $rowWitel['bill']['cmp_full'] += $sto['bill']['cmp_full'];
        }

        if($witelIndex >= 0) {
            $witelList[$witelIndex] = $rowWitel;
        } else {
            array_push($witelList, $rowWitel);
        }

    }

    $divreIndex = findArrayIndex($divreList, fn($divre) => $divre['location']['divre_kode'] == $sto['location']['divre_kode']);
    if($divreIndex >= 0) {
        $rowDivre = $divreList[$divreIndex];
    } else {
        $rowDivre = $rowTemplate;
        unset($rowDivre['saving']['value'], $rowDivre['saving']['achievement'], $rowDivre['cer']);
        $rowDivre['is_filter_applied'] = false;
        $rowDivre['location']['divre_kode'] = $sto['location']['divre_kode'];
        $rowDivre['location']['divre_name'] = $sto['location']['divre_name'];
        $rowDivre['saving']['target_percent'] = isset($savingTarget) ? $savingTarget * 100 : null;
    }

    if($sto['bill']['src']) {
        if(is_null($rowDivre['bill']['src'])) {
            $rowDivre['bill']['src'] = 0;
        }
        $rowDivre['bill']['src'] += $sto['bill']['src'];

        if(is_null($nasional['bill']['src'])) {
            $nasional['bill']['src'] = 0;
        }
        $nasional['bill']['src'] += $sto['bill']['src'];
    }

    if($sto['bill']['cmp']) {
        if(is_null($rowDivre['bill']['cmp'])) {
            $rowDivre['bill']['cmp'] = 0;
        }
        $rowDivre['bill']['cmp'] += $sto['bill']['cmp'];

        if(is_null($nasional['bill']['cmp'])) {
            $nasional['bill']['cmp'] = 0;
        }
        $nasional['bill']['cmp'] += $sto['bill']['cmp'];
    }

    if($sto['bill']['cmp_full']) {
        if(is_null($rowDivre['bill']['cmp_full'])) {
            $rowDivre['bill']['cmp_full'] = 0;
        }
        $rowDivre['bill']['cmp_full'] += $sto['bill']['cmp_full'];

        if(is_null($nasional['bill']['cmp_full'])) {
            $nasional['bill']['cmp_full'] = 0;
        }
        $nasional['bill']['cmp_full'] += $sto['bill']['cmp_full'];
    }

    if($divreIndex >= 0) {
        $divreList[$divreIndex] = $rowDivre;
    } else {
        array_push($divreList, $rowDivre);
    }

}

$stoList = array_filter($stoList, fn($item) => $item['location']['in_target']);
$stoList = array_map(function($item) {
    unset($item['location']['in_target']);
    return $item;
}, $stoList);
$stoList = array_values($stoList);

foreach($divreList as $index => $divre) {

    $nasionalBillCmpFull = $nasional['bill']['cmp_full'];
    $divreBillCmpFull = $divre['bill']['cmp_full'];
    $divreBillFraction = $divreList[$index]['bill_fraction'];
    
    if($divreBillCmpFull && $nasionalBillCmpFull) {
        $divreBillFraction = customRound($divreBillCmpFull / $nasionalBillCmpFull * 100, 2);
    }

    $divreSavingTargetPercent = $divreList[$index]['saving']['target_percent'];
    $divreSavingTarget = $divreList[$index]['saving']['target'];
    if($divreBillCmpFull && $divreSavingTargetPercent) {
        $divreSavingTarget = customRound($divreBillCmpFull * $divreSavingTargetPercent / 100, 2);
    }

    $divreList[$index]['bill_fraction'] = $divreBillFraction;
    $divreList[$index]['saving']['target'] = $divreSavingTarget;

}

foreach($witelList as $index => $witel) {

    $divre = findArray($divreList, fn($item) => $item['location']['divre_kode'] == $witel['location']['divre_kode']);

    $divreBillCmpFull = $divre && isset($divre['bill']['cmp_full']) ? $divre['bill']['cmp_full'] : null;
    $witelBillCmpFull = $witel['bill']['cmp_full'];
    $witelBillFraction = $witelList[$index]['bill_fraction'];
    if($divreBillCmpFull && $witelBillCmpFull) {
        $witelBillFraction = customRound($witelBillCmpFull / $divreBillCmpFull * 100, 2);
    }

    $divreSavingTarget = $divre && isset($divre['saving']['target']) ? $divre['saving']['target'] : null;
    $witelSavingTarget = $witelList[$index]['saving']['target'];
    if($divreSavingTarget && $witelBillFraction) {
        $witelSavingTarget = customRound($witelBillFraction / 100 * $divreSavingTarget, 2);
    }

    $witelBillSrc = $witelList[$index]['bill']['src'];
    $witelBillCmp = $witel['bill']['cmp'];
    $witelSavingValue = $witelList[$index]['saving']['value'];
    $witelCerSrc = $witelList[$index]['cer']['src_real'];
    if($witelBillSrc && $witelBillCmp) {
        $witelSavingValue = customRound($witelBillSrc - $witelBillCmp, 2);
        $witelCerSrc = customRound($witelBillSrc / $witelBillCmp * 100, 2);
    }
    
    $witelSavingAchv = $witelList[$index]['saving']['achievement'];
    if($witelSavingTarget && $witelSavingValue) {
        $witelSavingAchv = customRound($witelSavingTarget + $witelSavingValue, 2);
        if($witelSavingAchv <= 0) {
            $witelSavingAchv = null;
        }
    }

    $witelList[$index]['bill_fraction'] = $witelBillFraction;
    $witelList[$index]['saving']['target'] = $witelSavingTarget;
    $witelList[$index]['saving']['value'] = $witelSavingValue;
    $witelList[$index]['cer']['src_real'] = $witelCerSrc;
    $witelList[$index]['saving']['achievement'] = $witelSavingAchv;

}

foreach($stoList as $index => $sto) {

    $witel = findArray($witelList, fn($item) => $item['location']['witel_kode'] == $sto['location']['witel_kode']);

    $witelBillCmpFull = $witel && isset($witel['bill']['cmp_full']) ? $witel['bill']['cmp_full'] : null;
    $stoBillCmpFull = $sto['bill']['cmp_full'];
    $stoBillFraction = $stoList[$index]['bill_fraction'];
    if($witelBillCmpFull && $stoBillCmpFull) {
        $stoBillFraction = customRound($stoBillCmpFull / $witelBillCmpFull * 100, 2);
    }

    $witelSavingTarget = $witel && isset($witel['saving']['target']) ? $witel['saving']['target'] : null;
    $stoSavingTarget = $stoList[$index]['saving']['target'];
    if($witelSavingTarget && $stoBillFraction) {
        $stoSavingTarget = customRound($stoBillFraction / 100 * $witelSavingTarget, 2);
    }

    $stoBillSrc = $stoList[$index]['bill']['src'];
    $stoBillCmp = $sto['bill']['cmp'];
    $stoSavingValue = $stoList[$index]['saving']['value'];
    $stoCerSrc = $stoList[$index]['cer']['src_real'];
    if($stoBillSrc && $stoBillCmp) {
        $stoSavingValue = customRound($stoBillSrc - $stoBillCmp, 2);
        $stoCerSrc = customRound($stoBillSrc / $stoBillCmp * 100, 2);
    }
    
    $stoSavingAchv = $stoList[$index]['saving']['achievement'];
    if($stoSavingTarget && $stoSavingValue) {
        $stoSavingAchv = customRound($stoSavingTarget + $stoSavingValue, 2);
        if($stoSavingAchv <= 0) {
            $stoSavingAchv = null;
        }
    }

    $stoList[$index]['bill_fraction'] = $stoBillFraction;
    $stoList[$index]['saving']['target'] = $stoSavingTarget;
    $stoList[$index]['saving']['value'] = $stoSavingValue;
    $stoList[$index]['cer']['src_real'] = $stoCerSrc;
    $stoList[$index]['saving']['achievement'] = $stoSavingAchv;

}

// dd_json(array_map(function($item) {
//     return $item['bill']['cmp_full'];
// }, $divreList));

// dd_json(array_map(function($item) {
//     return $item['bill']['cmp_full'];
// }, $witelList), $divreList[0]);

// dd_json([
    // 'nasional' => $nasional,
    // 'divre_list' => $divreList,
    // 'witel_list' => $witelList,
    // 'sto_list' => $stoList,
// ]);

$this->result = [
    'sto_list' => $stoList,
    'witel_list' => $witelList,
    'treg_list' => $divreList,
    'nasional_data' => $nasional,
    'years' => [
        'source' => $srcYear,
        'comparison' => $cmpYear
    ],
    'filter_month' => $filter['month']
];