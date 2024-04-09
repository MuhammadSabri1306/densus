<?php

function customRound($numb, $precision = 0) {
    $numbPow = pow(10, $precision);
    $result = floor($numb * $numbPow) / $numbPow;
    $lastDecimal = ($numb - $result) * $numbPow;
    
    if($lastDecimal < 0.5) return $result;

    $result = (ceil($result * $numbPow) + 1) / $numbPow;
    return $result;
}