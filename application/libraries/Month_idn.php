<?php

class Month_idn
{
    public static $nameList = [ 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'November', 'Oktober', 'Desember' ];
    public static $snameList = [ 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Nov', 'Okt', 'Des' ];
        
    public static function getList()
    {
        return array_map(function($name, $sname, $index) {
            return [
                'name' => $name,
                'sname' => $sname,
                'number' => $index + 1
            ];
        }, Month_idn::$nameList, Month_idn::$snameList, array_keys(Month_idn::$nameList));
    }

    public static function getNameByNumber(int $monthNumber)
    {
        $monthIndex = $monthNumber - 1;
        if($monthIndex < 0 || $monthIndex > count(Month_idn::$nameList)) {
            return null;
        }
        
        return Month_idn::$nameList[$monthIndex];
    }

    public static function getSnameByNumber(int $monthNumber)
    {
        $monthIndex = $monthNumber - 1;
        if($monthIndex < 0 || $monthIndex > count(Month_idn::$snameList)) {
            return null;
        }
        
        return Month_idn::$snameList[$monthIndex];
    }

    public static function getByNumber($monthNumber)
    {
        $monthName = Month_idn::getNameByNumber($monthNumber);
        return !$monthName ? null : [ 'name' => $monthName, 'number' => $monthNumber ];
    }
}