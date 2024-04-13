<?php

try {

$this->load->library('datetime_range');
$this->load->library('month_idn');
$this->load->helper('number');

$monthList = Month_idn::getList();
// $currYear = '2023';
$currYear = date('Y');
// $currMonth = '03';
$currMonth = date('m');
$currDay = date('d');
$prev2Day = date('d', strtotime('-2 days'));

$filterKwhDateTime = $this->datetime_range->get_by_year_range($currYear - 1, $currYear);
$filterDegDateTime = $this->datetime_range->get_by_month_range('01', $currMonth, $currYear);

$timestampRangeCurrDay = array_map(
    fn($dateTime) => strtotime($dateTime),
    $this->datetime_range->get_by_day($currDay, $currMonth, $currYear)
);
$timestampRangeLast3Day = array_map(
    fn($dateTime) => strtotime($dateTime),
    $this->datetime_range->get_by_daterange_month($prev2Day, $currDay, $currMonth, $currYear)
);
$timestampRangeCurrMonth = array_map(
    fn($dateTime) => strtotime($dateTime),
    $this->datetime_range->get_by_month($currMonth, $currYear)
);
$timestampRangeCurrYear = array_map(
    fn($dateTime) => strtotime($dateTime),
    $this->datetime_range->get_by_year($currYear)
);
$timestampRangePrevYear = array_map(
    fn($dateTime) => strtotime($dateTime),
    $this->datetime_range->get_by_year($currYear - 1)
);
$timestampRangePrevYearMonth = array_map(
    fn($dateTime) => strtotime($dateTime),
    $this->datetime_range->get_by_month(
        str_pad(strval(intval($currMonth) - 1), 2, '0', STR_PAD_LEFT),
        $currYear - 1
    )
);
$timestampRangePrevYearDay = array_map(
    fn($dateTime) => strtotime($dateTime),
    $this->datetime_range->get_by_day(
        str_pad(strval(intval($currDay) - 1), 2, '0', STR_PAD_LEFT),
        $currMonth,
        $currYear - 1
    )
);

/*
 * get RTU
 */
$dbDensus = $this->load->database('densus', true);
$dbDensus->select('*')->from($this->tableRtuName)->where('rtu_kode', $rtuCode);
$rtu = $dbDensus->get()->row_array();


/*
 * get KwH data
 */
$this->db
    ->select('*')
    ->from($this->tableKwhName)
    ->where('rtu_kode', $rtuCode)
    ->where('timestamp>=', $filterKwhDateTime[0])
    ->where('timestamp<=', $filterKwhDateTime[1]);
$kwhList = $this->db->get()->result_array();
$kwhListCount = count($kwhList);


/*
 * setup KwH and PLN result data
 */
$kwhSummary = [
    'curr_day' => [
        'year' => $currYear,
        'month' => $currMonth,
        'month_name' => Month_idn::getNameByNumber($currMonth),
        'month_sname' => Month_idn::getSnameByNumber($currMonth),
        'day' => $currDay,
        'kwh_total' => null
    ],
    'curr_month' => [
        'year' => $currYear,
        'month' => $currMonth,
        'month_name' => Month_idn::getNameByNumber($currMonth),
        'month_sname' => Month_idn::getSnameByNumber($currMonth),
        'kwh_total' => null
    ],
];
$kwhDailyUsages = [];

$monthRange = range(1, intval($currMonth));
$plnCostMonthly = array_map(function($monthNumber) use ($currYear) {
    return [
        'year' => $currYear,
        'month' => $monthNumber,
        'month_name' => Month_idn::getNameByNumber($monthNumber),
        'month_sname' => Month_idn::getSnameByNumber($monthNumber),
        'kwh_usage' => null,
        'kwh_cost' => null,
        'lwbp' => null,
        'lwbp_cost' => null,
        'wbp' => null,
        'wbp_cost' => null,
    ];
}, $monthRange);

$costEstimation = [
    'kwh_cost_curr_year' => [
        'year' => $currYear,
        'kwh_cost' => null
    ],
    'bbm_cost_curr_year' => [
        'year' => $currYear,
        'bbm_cost' => null
    ],
    'total_cost_curr_year' => [
        'year' => $currYear,
        'total_cost' => null
    ],
    'kwh_cost_curr_month' => [
        'year' => $currYear,
        'month' => $currMonth,
        'month_name' => Month_idn::getNameByNumber($currMonth),
        'month_sname' => Month_idn::getSnameByNumber($currMonth),
        'kwh_cost' => null
    ],
    'bbm_cost_curr_month' => [
        'year' => $currYear,
        'month' => $currMonth,
        'month_name' => Month_idn::getNameByNumber($currMonth),
        'month_sname' => Month_idn::getSnameByNumber($currMonth),
        'bbm_cost' => null
    ],
    'total_cost_curr_month' => [
        'year' => $currYear,
        'month' => $currMonth,
        'month_name' => Month_idn::getNameByNumber($currMonth),
        'month_sname' => Month_idn::getSnameByNumber($currMonth),
        'total_cost' => null
    ],
];

$kwhTotalCurrYear = null;
$kwhTotalPrevYear = null;
$kwhTotalCurrYearMonth = null;
$kwhTotalPrevYearMonth = null;
$kwhTotalCurrYearDay = null;
$kwhTotalPrevYearDay = null;

for($i=0; $i<$kwhListCount; $i++) {

    $kwhTimestamp = strtotime($kwhList[$i]['timestamp']);

    $isKwhCurrDay = $kwhTimestamp >= $timestampRangeCurrDay[0] && $kwhTimestamp <= $timestampRangeCurrDay[1];
    if($isKwhCurrDay) {
        if(is_null($kwhSummary['curr_day']['kwh_total'])) $kwhSummary['curr_day']['kwh_total'] = 0;
        $kwhSummary['curr_day']['kwh_total'] += $kwhList[$i]['kwh_value'];
    }

    $isKwhCurrMonth = $kwhTimestamp >= $timestampRangeCurrMonth[0] && $kwhTimestamp <= $timestampRangeCurrMonth[1];
    if($isKwhCurrMonth) {
        if(is_null($kwhSummary['curr_month']['kwh_total'])) $kwhSummary['curr_month']['kwh_total'] = 0;
        $kwhSummary['curr_month']['kwh_total'] += $kwhList[$i]['kwh_value'];
    }

    $isKwhLast3Days = $kwhTimestamp >= $timestampRangeLast3Day[0] && $kwhTimestamp <= $timestampRangeLast3Day[1];
    if($isKwhLast3Days) {
        array_push($kwhDailyUsages, [
            'kwh_value' => $kwhList[$i]['kwh_value'],
            'timestamp' => $kwhTimestamp * 1000,
        ]);
    }

    $kwhMonthNumber = (int) date('n', $kwhTimestamp);
    $kwhMonthIndex = $kwhMonthNumber - 1;

    $isKwhCurrYear = $kwhTimestamp >= $timestampRangeCurrYear[0] && $kwhTimestamp <= $timestampRangeCurrYear[1];
    if($isKwhCurrYear && isset($plnCostMonthly[$kwhMonthIndex])) {
        if(is_null($plnCostMonthly[$kwhMonthIndex]['year'])) {
            $plnCostMonthly[$kwhMonthIndex]['kwh_usage'] = 0;
            $plnCostMonthly[$kwhMonthIndex]['kwh_cost'] = 0;
            $plnCostMonthly[$kwhMonthIndex]['lwbp'] = 0;
            $plnCostMonthly[$kwhMonthIndex]['lwbp_cost'] = 0;
            $plnCostMonthly[$kwhMonthIndex]['wbp'] = 0;
            $plnCostMonthly[$kwhMonthIndex]['wbp_cost'] = 0;
        }

        $plnCostMonthly[$kwhMonthIndex]['kwh_usage'] += $kwhList[$i]['kwh_value'];
        $currKwhCost = 0;

        if($this->isLwbpTime($kwhList[$i]['timestamp'])) {
            $plnCostMonthly[$kwhMonthIndex]['lwbp'] += $kwhList[$i]['kwh_value'];
            $currKwhCost = $kwhList[$i]['kwh_value'] * Monitoring_rtu_model::LWBP_COST;
            $plnCostMonthly[$kwhMonthIndex]['lwbp_cost'] += $currKwhCost;
        } else {
            $plnCostMonthly[$kwhMonthIndex]['wbp'] += $kwhList[$i]['kwh_value'];
            $currKwhCost = $kwhList[$i]['kwh_value'] * Monitoring_rtu_model::WBP_COST;
            $plnCostMonthly[$kwhMonthIndex]['wbp_cost'] += $currKwhCost;
        }
        $plnCostMonthly[$kwhMonthIndex]['kwh_cost'] += $currKwhCost;
    
        if(is_null($costEstimation['kwh_cost_curr_year']['kwh_cost'])) $costEstimation['kwh_cost_curr_year']['kwh_cost'] = 0;
        $costEstimation['kwh_cost_curr_year']['kwh_cost'] += $currKwhCost;
        
        if($isKwhCurrMonth) {
            if(is_null($costEstimation['kwh_cost_curr_month']['kwh_cost'])) $costEstimation['kwh_cost_curr_month']['kwh_cost'] = 0;
            $costEstimation['kwh_cost_curr_month']['kwh_cost'] += $currKwhCost;
        }
    }

    if($isKwhCurrYear) {
        if(is_null($kwhTotalCurrYear)) $kwhTotalCurrYear = 0;
        $kwhTotalCurrYear += $kwhList[$i]['kwh_value'];
    }

    $isKwhPrevYear = $kwhTimestamp >= $timestampRangePrevYear[0] && $kwhTimestamp <= $timestampRangePrevYear[1];
    if($isKwhPrevYear) {
        if(is_null($kwhTotalPrevYear)) $kwhTotalPrevYear = 0;
        $kwhTotalPrevYear += $kwhList[$i]['kwh_value'];
    }

    if($isKwhCurrMonth) {
        if(is_null($kwhTotalCurrYearMonth)) $kwhTotalCurrYearMonth = 0;
        $kwhTotalCurrYearMonth += $kwhList[$i]['kwh_value'];
    }

    $isKwhPrevYearMonth = $kwhTimestamp >= $timestampRangePrevYearMonth[0] && $kwhTimestamp <= $timestampRangePrevYearMonth[1];
    if($isKwhPrevYearMonth) {
        if(is_null($kwhTotalPrevYearMonth)) $kwhTotalPrevYearMonth = 0;
        $kwhTotalPrevYearMonth += $kwhList[$i]['kwh_value'];
    }

    if($isKwhCurrDay) {
        if(is_null($kwhTotalCurrYearDay)) $kwhTotalCurrYearDay = 0;
        $kwhTotalCurrYearDay += $kwhList[$i]['kwh_value'];
    }

    $isKwhPrevYearDay = $kwhTimestamp >= $timestampRangePrevYearDay[0] && $kwhTimestamp <= $timestampRangePrevYearDay[1];
    if($isKwhPrevYearDay) {
        if(is_null($kwhTotalPrevYearDay)) $kwhTotalPrevYearDay = 0;
        $kwhTotalPrevYearDay += $kwhList[$i]['kwh_value'];
    }

}

$kwhSaving = [
    'yearly' => [
        'year' => $currYear,
        'kwh' => null,
        'kwh_percent' => null,
    ],
    'monthly' => [
        'year' => $currYear,
        'month' => $currMonth,
        'month_name' => Month_idn::getNameByNumber($currMonth),
        'month_sname' => Month_idn::getSnameByNumber($currMonth),
        'kwh' => null,
        'kwh_percent' => null,
    ],
    'daily' => [
        'year' => $currYear,
        'month' => $currMonth,
        'month_name' => Month_idn::getNameByNumber($currMonth),
        'month_sname' => Month_idn::getSnameByNumber($currMonth),
        'day' => $currDay,
        'kwh' => null,
        'kwh_percent' => null,
    ],
];
if(!is_null($kwhTotalCurrYear) && !is_null($kwhTotalPrevYear)) {
    $kwhSaving['yearly']['kwh'] = $kwhTotalCurrYear - $kwhTotalPrevYear;
    $kwhSaving['yearly']['kwh_percent'] = customRound($kwhSaving['yearly']['kwh'] / $kwhTotalPrevYear * 100);
}
if(!is_null($kwhTotalCurrYearMonth) && !is_null($kwhTotalPrevYearMonth)) {
    $kwhSaving['monthly']['kwh'] = $kwhTotalCurrYearMonth - $kwhTotalPrevYearMonth;
    $kwhSaving['monthly']['kwh_percent'] = customRound($kwhSaving['monthly']['kwh'] / $kwhTotalPrevYearMonth * 100);
}
if(!is_null($kwhTotalCurrYearDay) && !is_null($kwhTotalPrevYearDay)) {
    $kwhSaving['daily']['kwh'] = $kwhTotalCurrYearDay - $kwhTotalPrevYearDay;
    $kwhSaving['daily']['kwh_percent'] = customRound($kwhSaving['daily']['kwh'] / $kwhTotalPrevYearDay * 100);
}


/*
 * clear kwh db result to optimize memory usage
 */
unset($kwhList);


/*
 * get Deg data
 */
// $dbOpnimusNew = $this->load->database('opnimus_new', true);
$this->db
    ->select('*')
    ->from($this->tablePortStatusName)
    ->where('rtu_kode', $rtuCode)
    ->where('port', $rtu['port_genset'])
    ->group_start()
        ->group_start()
            ->where('timestamp>=', $filterDegDateTime[0])
            ->where('timestamp<=', $filterDegDateTime[1])
        ->group_end()
        ->or_group_start()
            ->where('timeended>=', $filterDegDateTime[0])
            ->where('timeended<=', $filterDegDateTime[1])
        ->group_end()
    ->group_end();
$degList = $this->db->get()->result_array();
$degListCount = count($degList);


/*
 * setup Deg and BBM result data
 */
$bbmCostMonthly = array_map(function($monthNumber) use ($currYear) {
    return [
        'year' => $currYear,
        'month' => $monthNumber,
        'month_name' => Month_idn::getNameByNumber($monthNumber),
        'month_sname' => Month_idn::getSnameByNumber($monthNumber),
        'duration' => null,
        'duration_ms' => null,
        'deg_on_count' => null,
        'bbm_usage' => null,
        'bbm_cost' => null,
    ];
}, $monthRange);

for($j=0; $j<$degListCount; $j++) {

    $degTimeStart = strtotime($degList[$j]['timestamp']);
    $degTimeEnd = strtotime($degList[$j]['timeended']);
    
    $degDurationTime = abs($degTimeEnd - $degTimeStart);
    $degTimeMid = $degTimeStart + ($degDurationTime / 2);
    $degMonthNumber = (int) date('n', $degTimeMid);
    $degMonthIndex = $degMonthNumber - 1;

    if(isset($bbmCostMonthly[$degMonthIndex])) {
        if(is_null($bbmCostMonthly[$degMonthIndex]['year'])) {
            $bbmCostMonthly[$degMonthIndex]['duration_ms'] = 0;
            $bbmCostMonthly[$degMonthIndex]['deg_on_count'] = 0;
            $bbmCostMonthly[$degMonthIndex]['bbm_usage'] = 0;
            $bbmCostMonthly[$degMonthIndex]['bbm_cost'] = 0;
        }
        $bbmCostMonthly[$degMonthIndex]['duration_ms'] += ($degDurationTime * 1000);
        $bbmCostMonthly[$degMonthIndex]['deg_on_count']++;

        $currBbmUsage = Monitoring_rtu_model::BBM_CSM_RATE * $rtu['kva_genset'] * ($degDurationTime / 3600);
        $bbmCostMonthly[$degMonthIndex]['bbm_usage'] += $currBbmUsage;
        $currBbmCost = ($currBbmUsage * Monitoring_rtu_model::BBM_PRICE);
        $bbmCostMonthly[$degMonthIndex]['bbm_cost'] += $currBbmCost;

        if(is_null($costEstimation['bbm_cost_curr_year']['bbm_cost'])) $costEstimation['bbm_cost_curr_year']['bbm_cost'] = 0;
        $costEstimation['bbm_cost_curr_year']['bbm_cost'] += $currBbmCost;

        $isDegCurrMonth = $degTimeMid >= $timestampRangeCurrMonth[0] && $degTimeMid <= $timestampRangeCurrMonth[1];
        if($isDegCurrMonth) {
            if(is_null($costEstimation['bbm_cost_curr_month']['bbm_cost'])) $costEstimation['bbm_cost_curr_month']['bbm_cost'] = 0;
            $costEstimation['bbm_cost_curr_month']['bbm_cost'] += $currBbmCost;
        }
    }

}

$costEstimation['total_cost_curr_year']['total_cost'] = ($costEstimation['kwh_cost_curr_year']['kwh_cost'] ?? 0) + ($costEstimation['bbm_cost_curr_year']['bbm_cost'] ?? 0);
$costEstimation['total_cost_curr_month']['total_cost'] = ($costEstimation['kwh_cost_curr_month']['kwh_cost'] ?? 0) + ($costEstimation['bbm_cost_curr_month']['bbm_cost'] ?? 0);

$this->result = [
    'rtu' => $rtu,
    'kwh_summary' => $kwhSummary,
    'kwh_saving' => $kwhSaving,
    'kwh_daily_usages' => $kwhDailyUsages,
    'pln_cost_monthly' => $plnCostMonthly,
    'bbm_cost_monthly' => $bbmCostMonthly,
    'cost_estimation' => $costEstimation
];

} catch(\Throwable $err) {
    dd(strval($err));
}