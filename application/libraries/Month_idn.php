<?php

class Month_idn
{
    public static $nameList = [ 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'November', 'Oktober', 'Desember' ];
        
    public static function getList()
    {
        return array_map(function($name, $index) {
            return [
                'name' => $name,
                'number' => $number
            ];
        }, Month_idn::$nameList, array_keys(Month_idn::$nameList));
    }

    public static function getNameByNumber($monthNumber)
    {
        $monthIndex = $monthNumber - 1;
        if($monthIndex < 0 || $monthIndex >= count(Month_idn::$nameList)) {
            return null;
        }
        
        return Month_idn::$nameList[$monthIndex];
    }

    public static function getByNumber($monthNumber)
    {
        $monthName = Month_idn::getNameByNumber($monthNumber);
        return !$monthName ? null : [ 'name' => $monthName, 'number' => $monthNumber ];
    }
}