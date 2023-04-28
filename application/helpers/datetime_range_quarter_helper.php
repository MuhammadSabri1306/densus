<?php

function datetime_range_quarter($quarter, $year) {
    $month = (($quarter - 1) * 3) + 1;
    $firstDay = new DateTime("$year-$month-01");
    $firstDay->modify('first day of this month');
    $firstDay->setTime(0, 0, 0);

    $lastMonth = $month + 2;
    $lastDay = new DateTime("$year-$lastMonth-01");
    $lastDay->modify('last day of this month');
    $lastDay->setTime(23, 59, 59);

    $firstDate = $firstDay->format('Y-m-d H:i:s');
    $lastDate = $lastDay->format('Y-m-d H:i:s');
    return [$firstDate, $lastDate];
}