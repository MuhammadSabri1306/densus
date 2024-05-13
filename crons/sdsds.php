<?php

date_default_timezone_set('Asia/Jakarta');

define('DB_HOST', '10.60.164.18');
define('DB_USER', 'admindb');
define('DB_PASS', '@Dm1ndb#2020');
define('DB_NAME', 'juan5684_densus');
require 'Database.php';

class CronStorePueConter {

    function debug(...$vars) {
        foreach($vars as $v) {
            var_dump($v);
        }
        exit();
    }
    
    // Build OsaseNewApi endpoint by RTU_CODE and PORT
    function getOsaseNewApiUrl($rtuId, $port) {
        return "https://opnimus.telkom.co.id/api/osasenewapi/getrtuport?token=xg7DT34vE7&flag=0&rtuid=$rtuId&port=$port";
    }

    // Get list of RTU from DB Densus
    function getRtuList() {
        $db = new Database;
        $db->query('SELECT rtu_kode, port_pue FROM rtu_map WHERE rtu_kode!="" AND port_pue!=""');
        return $db->resultSet();
    }

    function setPueCounter($pueItem) {
        $db = new Database;
        $db->query('SELECT * FROM pue_counter WHERE rtu_kode=:rtuKode');
        $db->bind('rtuKode', $pueItem['RTU_ID']);
        $hasData = $db->numRows() > 0;
        
        $query = 'INSERT INTO pue_counter (rtu_kode, pue_value, no_port, nama_port, tipe_port, satuan, timestamp)
            VALUES (:rtuKode, :pueValue, :noPort, :namaPort, :tipePort, :satuan, :timestamp)';

        $db->query($query);
        $db->bind('rtuKode', $pueItem['RTU_ID']);
        $db->bind('pueValue', $pueItem['VALUE']);
        $db->bind('noPort', $pueItem['PORT']);
        $db->bind('namaPort', $pueItem['NAMA_PORT']);
        $db->bind('tipePort', $pueItem['TIPE_PORT']);
        $db->bind('satuan', $pueItem['SATUAN']);
        $db->bind('timestamp', date('Y-m-d H:i:s'));

        $success = $db->execute();
        return $success;
    }

    function getPue($rtu) {
        if(!isset($rtu['rtu_kode'], $rtu['port_pue'])) {
            return false;
        }

        $apiUrl = $this->getOsaseNewApiUrl($rtu['rtu_kode'], $rtu['port_pue']);
        // echo $apiUrl;
        $response = file_get_contents($apiUrl);

        if(!$response) {
            return false;
        }

        $pue = json_decode($response, true);
        return count($pue) > 0 && isset($pue[0]['RTU_ID']) ? $pue[0] : false;
    }

    static function run() {
        $cron = new CronStorePueConter();
        $rtuList = $cron->getRtuList();
        
        foreach($rtuList as $rtu) {
            
            $fetchCounter = 0;
            $pueData = false;

            while(!$pueData && $fetchCounter < 3) {
                $pueData = $cron->getPue($rtu);
                var_dump($pueData);
                $fetchCounter++;
                if(!$pueData) {
                    sleep(1);
                }
            }
            
            if($pueData && isset($pueData['VALUE'])) {
                if($pueData['VALUE'] <= 10 && $pueData['VALUE'] > 0) {
                    $result = $cron->setPueCounter($pueData);
                    var_dump($result);
                }
            }
            
        }
    }

}

CronStorePueConter::run();