<?php

/* 
 * Group MYSQL data items by condition
 * @param Array[Int rowNumber][String collumnKey] => Any $data
 * @param Array[Int rowNumber] => Number $avgFields
 * @param Array[Int rowNumber] => Function $groupBy
 * @return @param Array[Int rowNumber][String collumnKey]
 */
// function array_avg ($data, $avgFields, $groupBy) {
//     $result = [];
//     foreach ($data as $row) {
//         if (empty($result)) {

//             array_push($result, $row);

//         } else {

//             $matchedArray = null;

//             foreach ($row as $key => $value) {
                
//                 $matchedIndex = 

//             }
//         }
//     }
// }

function array_avg($data, $avgFields, $groupBy) {
    if(count($data) < 1) return [];

    $avgItem = [];
    for($i=0; $i<count($data); $i++) {
        foreach($avgFields as $field => $callback) {
            
            if(!isset($avgItem[$field])) {
                $avgItem[$field] = [ 'count' => 0, 'sum' => 0 ];
            }
            if($callback($data[$i])) {
                $avgItem[$field]['count']++;
                $avgItem[$field]['sum'] += doubleval($data[$i][$groupBy]);
            }

        }
    }
    
    $result = array_map(function ($item) {

        if($item['count'] < 1) return 0;
        return $item['sum'] / $item['count'];

    }, $avgItem);

    return $result;
}

// $avgFields = [
//     'currMonth' => function($item) {
//         $currentMonth = date('m');
//         $currentYear = date('Y');
//         $itemMonth = date('m', strtotime($item['timestamp']));
//         $itemYear = date('Y', strtotime($item['timestamp']));
        
//         $isMatch = ($itemMonth == $currentMonth && $itemYear == $currentYear);
//         return $isMatch;
//     }
// ];

// var_dump(array_avg($arrayVar, $avgFields, 'pue_value'));