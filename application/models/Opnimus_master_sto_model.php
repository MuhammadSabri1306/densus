<?php
class Opnimus_master_sto_model extends CI_Model
{
    protected $tableName = 'master_sto';

    public $currUser;

    public function __construct()
    {
            $this->load->database('opnimus');
    }

    public function get_master_lokasi_gepee_cross_data()
    {
        $this->use_module('get_master_lokasi_gepee_cross_data', []);
        return $this->result;
    }
    
    public function get_witel_code_list()
    {
        $this->use_module('get_witel_code_list', []);
        return $this->result;
    }
}