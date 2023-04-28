<?php

function datetime_range_semester($semester, $year) {
    $semester = (int) $semester;
    if($semester < 1 || $semester > 2) {
        return null;
    }
    
    $startMonth = $semester * 6 - 5;
    $endMonth = $startMonth + 5;
    
    $firstDay = new DateTime("$year-$startMonth-01");
    $firstDay->modify('first day of this month');
    $firstDay->setTime(0, 0, 0);

    $lastDay = new DateTime("$year-$endMonth-01");
    $lastDay->modify('last day of this month');
    $lastDay->setTime(23, 59, 59);

    $firstDate = $firstDay->format('Y-m-d H:i:s');
    $lastDate = $lastDay->format('Y-m-d H:i:s');
    return [$firstDate, $lastDate];
}