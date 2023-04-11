<?php

function get_time_range(...$keys) {
    $list = [
        'currYear' => [ date('Y-01-01 00:00:00'), date('Y-m-d H:i:s') ],
        'prevYear' => [ date('Y-01-01 00:00:00', strtotime('-1 year')), date('Y-m-d H:i:s', strtotime('-1 year')) ],
        'currMonth' => [ date('Y-m-01 00:00:00'), date('Y-m-d H:i:s') ],
        'prevMonth' => [ date('Y-m-01 00:00:00', strtotime('-1 month')), date('Y-m-d H:i:s', strtotime('-1 month')) ],
        'currWeek' => [ date('Y-m-d 00:00:00', strtotime('this week')), date('Y-m-d H:i:s') ],
        'prevWeek' => [ date('Y-m-d 00:00:00', strtotime('last week monday')), date('Y-m-d H:i:s', strtotime('-1 week')) ],
        'currDay' => [ date('Y-m-d 00:00:00'), date('Y-m-d H:i:s') ],
        'prevDay' => [ date('Y-m-d 00:00:00', strtotime('-1 day')), date('Y-m-d H:i:s', strtotime('-1 day')) ],
        'currHour' => [ date('Y-m-d H:00:00'), date('Y-m-d H:i:s') ],
        'prevHour' => [ date('Y-m-d H:00:00', strtotime('-1 hours')), date('Y-m-d H:i:s', strtotime('-1 hours')) ]
    ];

    if(count($keys) < 1) return $list;

    $node = [];
    foreach($keys as $key) {
        $row = $list[$key];
        array_unshift($row, $key);
        array_push($node, $row);
    }
    return $node;
}