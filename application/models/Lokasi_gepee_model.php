<?php
class Lokasi_gepee_model extends CI_Model
{
    private $tableName = 'master_lokasi_gepee';

    public function __construct()
    {
            $this->load->database('densus');
    }

    private function filter_for_curr_user($currUser)
    {
        if($currUser && $currUser['level'] == 'witel') {
            $this->db->where('witel_kode', $currUser['locationId']);
        } elseif($currUser && $currUser['level'] == 'divre') {
            $this->db->where('divre_kode', $currUser['locationId']);
        }
    }

    public function get_divre($divreCode = null, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);
        if(is_null($divreCode)) {
            $query = $this->db
                ->select('divre_kode, divre_name')
                ->from($this->tableName)
                ->order_by('divre_kode', 'ASC')
                ->order_by('witel_kode', 'ASC')
                ->group_by('divre_kode')
                ->get();
            return $query->result();
        }

        $query = $this->db
            ->select('divre_kode, divre_name')
            ->from($this->tableName)
            ->where('divre_kode', $divreCode)
            ->get();
            
        return $query->row();
    }

    public function get_witel($divreCode = null, $witelCode = null, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);
        if(!is_null($divreCode)) {
            $this->db->where('divre_kode', $divreCode);
        }

        if(is_null($witelCode)) {
            $query = $this->db
                ->select('divre_kode, divre_name, witel_kode, witel_name')
                ->from($this->tableName)
                ->order_by('divre_kode', 'ASC')
                ->order_by('witel_kode', 'ASC')
                ->group_by('witel_kode')
                ->get();
            return $query->result();
        }

        $query = $this->db
            ->select('divre_kode, divre_name, witel_kode, witel_name')
            ->from($this->tableName)
            ->where('witel_kode', $witelCode)
            ->get();
            
        return $query->row();
    }

    public function get_by_id($id, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);

        $query = $this->db
            ->select()
            ->from($this->tableName)
            ->where('witel_kode', $witelCode)
            ->get();
            
        return $query->row();
    }

    public function get_by_code($areaCode = [], $currUser = null)
    {
        $this->filter_for_curr_user($currUser);

        if(isset($areaCode['divre'])) $this->db->where('divre_kode', $areaCode['divre']);
        if(isset($areaCode['witel'])) $this->db->where('witel_kode', $areaCode['witel']);
        if(isset($areaCode['sto'])) $this->db->where('sto_kode', $areaCode['sto']);
        if(isset($areaCode['rtu'])) $this->db->where('rtu_kode', $areaCode['rtu']);

        $query = $this->db
            ->select()
            ->from($this->tableName)
            ->order_by('divre_kode', 'ASC')
            ->order_by('witel_kode', 'ASC')
            ->get();
        return $query->result();
    }

    public function get_all()
    {
        $query = $this->db
            ->select()
            ->from($this->tableName)
            ->get();
        return $query->result_array();
    }    

    public function save($body, $id = null, $currUser = null)
    {
        if(is_null($id)) {
            $success = $this->db->insert($this->tableName, $body);
        } else {
            if($currUser) $this->apply_filter(null, $currUser);
            $this->db->where('id', $id);

            $success = $this->db->update($this->tableName, $body);
        }
        return $success;
    }
}