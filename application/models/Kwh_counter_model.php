<?php
class Kwh_counter_model extends CI_Model
{
    public function __construct()
    {
            $this->load->database('opnimus');
    }

    public function get_kwhdata($rtu)
    {
        $sql_daily = "SELECT kwh_value AS kwh, timestamp FROM kwh_counter 
        WHERE rtu_kode='$rtu' AND DATE(timestamp) BETWEEN CURRENT_DATE-2 AND CURRENT_DATE
            ORDER BY timestamp DESC";
            
        $query = $this->db->query($sql_daily);
        
        return $query->result();
    }
}