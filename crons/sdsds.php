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

    function setPueCounter($pue) {
        $db = new Database;
        $db->query('SELECT * FROM pue_counter WHERE rtu_kode=:rtuKode');
        $db->bind('rtuKode', $pue['RTU_ID']);
        $hasData = $db->numRows() > 0;
        
        $query = 'INSERT INTO pue_counter (rtu_kode, pue_value, no_port, nama_port, tipe_port, satuan, timestamp)
            VALUES (:rtuKode, :pueValue, :noPort, :namaPort, :tipePort, :satuan, :timestamp)';

        $db->query($query);
        $db->bind('rtuKode', $pue['RTU_ID']);
        $db->bind('pueValue', $pue['VALUE']);
        $db->bind('noPort', $pue['PORT']);
        $db->bind('namaPort', $pue['NAMA_PORT']);
        $db->bind('tipePort', $pue['TIPE_PORT']);
        $db->bind('satuan', $pue['SATUAN']);
        $db->bind('timestamp', date('Y-m-d H:i:s'));

        $success = $db->execute();
        return $success;
    }

    function getPue($rtu) {
        if(!isset($rtu['rtu_kode'], $rtu['port_pue'])) return false;
        $apiUrl = $this->getOsaseNewApiUrl($rtu['rtu_kode'], $rtu['port_pue']);
        echo $apiUrl;
        $response = file_get_contents($apiUrl);

        if(!$response) return false;
        $pue = json_decode($response, true);
        return count($pue) > 0 && isset($pue[0]['RTU_ID']) ? $pue[0] : false;
    }

    static function run() {
        $cron = new CronStorePueConter();
        $rtuList = $cron->getRtuList();
        
        foreach($rtuList as $rtu) {
            
            $fetchCounter = 0;
            $pue = false;

            while(!$pue && $fetchCounter < 3) {
                $pue = $cron->getPue($rtu);
                $fetchCounter++;
                if(!$pue) {
                    sleep(1);
                }
            }
            
            if($pue) {
                $result = $cron->setPueCounter($pue);
                var_dump($result);
            }
            
        }
    }

}

CronStorePueConter::run();