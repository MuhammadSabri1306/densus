<?php
class Pue_Counter_model extends CI_Model {

    private $tableName = 'pue_counter';

    public function __construct()
    {
        $this->load->database('densus');
    }

    public function get_rtu_detail($rtuCode)
    {
        $this->db
            ->select('rtu_kode, no_port, nama_port, tipe_port, satuan')
            ->from($this->tableName)
            ->where('rtu_kode', $rtuCode);
        return $this->db->get()->row();
    }
    
    public function get_chart($rtu)
    {
        //LAST Year YTD Perf
        $sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value
        FROM kwh_counter 
        WHERE rtu_kode='RTU-BALA' AND YEAR(timestamp) = YEAR(CURRENT_DATE)-1
        GROUP BY rtu_kode,YEAR(timestamp) ORDER BY kwh_counter.timestamp ASC";    
        $query = $this->db->query($sql);
        $arrayly=$query->row_array();
        if ($arrayly['kwh_value']) $last_year=$arrayly['kwh_value'];else $last_year=0;
        


        //LAST Year YTD Perf
        $sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value
        FROM kwh_counter 
        WHERE rtu_kode='$rtu' AND YEAR(timestamp) = YEAR(CURRENT_DATE)
        GROUP BY rtu_kode,YEAR(timestamp) ORDER BY kwh_counter.timestamp ASC";    
        $query = $this->db->query($sql);
        $arraycy=$query->row_array();
        $current_year=$arraycy['kwh_value'];

        //LAST Month YTD Perf
        $sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value
        FROM kwh_counter 
        WHERE rtu_kode='$rtu' AND DATE(timestamp) BETWEEN DATE_FORMAT(NOW() ,'%Y-%m-01')- INTERVAL 1 MONTH AND NOW()- INTERVAL 1 MONTH
        GROUP BY rtu_kode,MONTH(timestamp) ORDER BY kwh_counter.timestamp ASC";    
        $query = $this->db->query($sql);
        $arraylm=$query->row_array();
        $last_month=$arraylm['kwh_value'];


        //LAST Month YTD Perf
        $sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value
        FROM kwh_counter 
        WHERE rtu_kode='$rtu' AND DATE(timestamp) BETWEEN DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW()
        GROUP BY rtu_kode,MONTH(timestamp) ORDER BY kwh_counter.timestamp ASC";    
        $query = $this->db->query($sql);
        $arraycm=$query->row_array();
        $current_month=$arraycm['kwh_value'];

        //LAST DAY YTD Perf
        $sql="SELECT rtu_kode,timestamp,ROUND(SUM(kwh_value)) as kwh_value
        FROM kwh_counter 
        WHERE rtu_kode='$rtu' AND HOUR(timestamp) BETWEEN 0 AND HOUR(CURRENT_TIMESTAMP) AND DATE(timestamp)=DATE(CURRENT_DATE)-INTERVAL 1 DAY
        GROUP BY rtu_kode,DATE(timestamp) ORDER BY kwh_counter.timestamp ASC";    
        $query = $this->db->query($sql);
        $arrayld=$query->row_array();
        $last_day=$arrayld['kwh_value'];


        //LAST DAY YTD Perf
        $sql="SELECT rtu_kode,timestamp,ROUND(SUM(kwh_value)) as kwh_value
        FROM kwh_counter 
        WHERE rtu_kode='$rtu' AND HOUR(timestamp) BETWEEN 0 AND HOUR(CURRENT_TIMESTAMP) AND DATE(timestamp)=DATE(CURRENT_DATE)
        GROUP BY rtu_kode,DATE(timestamp) ORDER BY kwh_counter.timestamp ASC";    
        $query = $this->db->query($sql);
        $arraycd=$query->row_array();
        $current_day=$arraycd['kwh_value'];

        $savingpercent['savingyearly']=$last_year-$current_year;
        $savingpercent['savingyearly_percent']=$this->pct_change($current_year,$last_year);
        $savingpercent['savingmonthly']=$last_month-$current_month;
        $savingpercent['savingmonthly_percent']=$this->pct_change($current_month,$last_month);
        $savingpercent['savingdaily']=$last_day-$current_day;
        $savingpercent['savingdaily_percent']=$this->pct_change($current_day,$last_day);

        return $savingpercent;
    }

    public function get_on_curr_year($rtuCode)
    {
        $year = date('Y');
        $this->db
            ->select('pue_value, timestamp')
            ->from($this->tableName)
            ->where('rtu_kode', $rtuCode)
            ->where('YEAR(timestamp)', $year)
            ->order_by('timestamp', 'desc');
            
        $data = $this->db->get()->result();
        return compact('year', 'data');
    }

    public function get_curr_value($rtuCode)
    {
        $this->db
            ->select('pue_value, timestamp')
            ->from($this->tableName)
            ->where('rtu_kode', $rtuCode)
            ->order_by('timestamp', 'desc');
            
        return $this->db->get()->row();
    }

    public function get_max_value($rtuCode)
    {
        $q = "SELECT pue_value, timestamp FROM $this->tableName
            WHERE rtu_kode='$rtuCode'
            AND pue_value=(SELECT MAX(pue_value) FROM $this->tableName WHERE rtu_kode='RTU-D7-BAL')";
        $query = $this->db->query($q);
        return $query->row();
    }

    public function get_avg_value($rtuCode)
    {
        $queries = [];
        $currTimes = [
            // ['yearly', 'YEAR', date('Y')],
            ['currMonth', 'MONTH', date('m')],
            ['currWeek', 'WEEK', date('W')],
            ['currDay', 'DAY', date('d')],
            ['currHour', 'HOUR', date('h')]
        ];

        foreach($currTimes as $item) {
            
            list($key, $timeKey, $val) = $item;
            $temp = "(SELECT AVG(pue_value) FROM pue_counter WHERE rtu_kode='RTU-D7-BAL' AND $timeKey(timestamp)=$val) AS $key";
            array_push($queries, $temp);
            
        }

        $q = 'SELECT ' . implode(', ', $queries);
        $query = $this->db->query($q);

        $data = $query->row_array();
        if(!is_array($data)) $data = [];
        $data['timestamp'] = date('Y-m-d h:i:s');

        return $data;
    }

    public function get_performance_value($rtuCode)
    {
        $queries = [];
        $currTimes = [
            ['currMonth', 'MONTH', date('m')],
            ['prevMonth', 'MONTH', date('m', strtotime('-1 month'))],
            ['currWeek', 'WEEK', date('W')],
            ['prevWeek', 'WEEK', date('W', strtotime('-1 week'))],
            ['currDay', 'DAY', date('d')],
            ['prevDay', 'DAY', date('d', strtotime('-1 day'))],
            ['currHour', 'HOUR', date('h')],
            ['prevHour', 'HOUR', date('h', strtotime('-1 hour'))]
        ];

        foreach($currTimes as $item) {
            
            list($key, $timeKey, $val) = $item;
            $temp = "(SELECT AVG(pue_value) FROM pue_counter WHERE rtu_kode='RTU-D7-BAL' AND $timeKey(timestamp)=$val) AS $key";
            array_push($queries, $temp);
            
        }

        $q = 'SELECT ' . implode(', ', $queries);
        $query = $this->db->query($q);
        $data = $query->row_array();

        if(!$data) return null;
        $result = [ 'timestamp' => date('Y-m-d h:i:s') ];

        if($data['currMonth']) {
            if($data['prevMonth']) {
                $data['currMonth'] = (double) $data['currMonth'];
                $data['prevMonth'] = (double) $data['prevMonth'];
                $result['currMonth'] = !$data['prevMonth'] ? 100 : ($data['currMonth'] - $data['prevMonth']) / $data['prevMonth'] * 100;
            } else {
                $result['currMonth'] = 0;
            }
        }

        if($data['currWeek']) {
            if($data['prevWeek']) {
                $data['currWeek'] = (double) $data['currWeek'];
                $data['prevWeek'] = (double) $data['prevWeek'];
                $result['currWeek'] = ($data['currWeek'] - $data['prevWeek']) / $data['prevWeek'] * 100;
            } else {
                $result['currWeek'] = 0;
            }
        }

        if($data['currDay']) {
            if($data['prevDay']) {
                $data['currDay'] = (double) $data['currDay'];
                $data['prevDay'] = (double) $data['prevDay'];
                $result['currDay'] = ($data['currDay'] - $data['prevDay']) / $data['prevDay'] * 100;
            } else {
                $result['currDay'] = 0;
            }
        }

        if($data['currHour']) {
            if($data['prevHour']) {
                $data['currHour'] = (double) $data['currHour'];
                $data['prevHour'] = (double) $data['prevHour'];
                $result['currHour'] = ($data['currHour'] - $data['prevHour']) / $data['prevHour'] * 100;
            } else {
                $result['currHour'] = 0;
            }
        }

        return $result;
    }
}