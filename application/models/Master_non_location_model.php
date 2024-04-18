<?php
class Master_non_location_model extends CI_Model
{
    protected $tableRegionalName = 'master_non_regional';
    protected $tableWitelName = 'master_non_witel';

    public function __construct()
    {
        $this->load->database('densus');
    }

    public function get_divre()
    {
        return $this->db
            ->order_by('regional_name')
            ->get($this->tableRegionalName)
            ->result_array();
    }

    public function find_divre($divreCode)
    {
        return $this->db
            ->where('divre_kode', $divreCode)
            ->get($this->tableRegionalName)
            ->row_array();
    }

    public function find_divre_by_witel($witelCode)
    {
        return $this->db
            ->select('regional.*')
            ->from("$this->tableRegionalName as regional")
            ->join("$this->tableWitelName as witel", 'witel.regional_id=regional.regional_id')
            ->where('witel.witel_kode', $witelCode)
            ->get()
            ->row_array();
    }

    public function get_witel()
    {
        return $this->db
            ->select('*')
            ->from("$this->tableWitelName as witel")
            ->join("$this->tableRegionalName as regional", 'regional.regional_id=witel.regional_id')
            ->order_by('regional_name', 'witel_name')
            ->get()
            ->result_array();
    }

    public function get_witel_by_divre($divreCode)
    {
        return $this->db
            ->select('*')
            ->from("$this->tableWitelName as witel")
            ->join("$this->tableRegionalName as regional", 'regional.regional_id=witel.regional_id')
            ->where('regional.divre_kode', $divreCode)
            ->order_by('regional_name')
            ->order_by('witel_name')
            ->get()
            ->result_array();
    }

    public function find_witel($witelCode)
    {
        return $this->db
            ->select('*')
            ->from("$this->tableWitelName as witel")
            ->join("$this->tableRegionalName as regional", 'regional.regional_id=witel.regional_id')
            ->where('witel.witel_kode', $witelCode)
            ->order_by('regional_name', 'witel_name')
            ->get()
            ->row_array();
    }
}