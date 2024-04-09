<?php
class Monitoring_rtu_model extends CI_Model
{
    protected $tableRtuName = 'rtu_map';
    protected $tableKwhName = 'kwh_counter';
    protected $tablePortStatusName = 'rtu_port_status';
    protected $tableNonRegionalName = 'master_non_regional'; // non => Newosase/New
    protected $tableNonWitelName = 'master_non_witel';

    protected $currUser = null;

    const BBM_PRICE = 28440;
    const BBM_CSM_RATE = 0.21;
    const LWBP_COST = 1091;
    const WBP_COST = 1609;

    public function setCurrUser($currUser)
    {
        $this->currUser = $currUser;
    }

    public function isWbpTime(string|int $dateTime): bool
    {
        if(is_int($dateTime)) $dateTime = date('Y-m-d H:i:s', $dateTime);
        $dateTime = new \DateTime($dateTime);
        $hour = (int) $dateTime->format('H');
        return $hour >= 17 && $hour <= 22;
    }

    public function isLwbpTime(string|int $dateTime): bool
    {
        return !$this->isWbpTime($dateTime);
    }

    public function find_rtu($rtuCode)
    {
        $this->load->database('densus');
        $this->db
            ->select('*')
            ->from($this->tableRtuName)
            ->where('rtu_kode', $rtuCode);

        if($this->currUser['level'] == 'divre') {
            $this->db->where('divre_kode', $this->currUser['locationId']);
        } elseif($this->currUser['level'] == 'witel') {
            $this->db->where('witel_kode', $this->currUser['locationId']);
        }

        return $this->db->get()->row_array();
    }

    public function get_rtu_list($divreCode = null, $witelCode = null)
    {
        $this->load->database('densus');
        $this->use_module('get_energy_cost_data', [ 'divreCode' => $divreCode, 'witelCode' => $witelCode ]);
        return $this->result;
    }

    public function get_energy_cost_data($rtuCode)
    {
        $this->load->database('opnimus');
        $this->use_module('get_energy_cost_data', [ 'rtuCode' => $rtuCode ]);
        return $this->result;
    }
}