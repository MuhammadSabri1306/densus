<?php

class Rtu_map_model extends CI_Model {

    public function __construct() {
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

    public function setFilter($filters = [])
    {
        if(count($filters) > 0) {
            $this->db->where($filters);
        }
    }

    public function get($id = null, $currUser = null) {
        $this->filter_for_curr_user($currUser);
        $this->db
            ->select()
            ->from('rtu_map');

        if(is_null($id)) {
            return $this->db
                ->order_by('divre_kode')
                ->order_by('witel_kode')
                ->order_by('sto_kode')
                ->get()
                ->result();
        }

        return $this->db
            ->where('id', $id)
            ->get()
            ->row();
    }

    public function get_by_rtu_code($rtuCode, $currUser = null) {
        $this->filter_for_curr_user($currUser);
        $query = $this->db
            ->select()
            ->from('rtu_map')
            ->where('id', $id)
            ->get();
        return $query->row();
    }

    public function save($body, $id = null, $currUser = null)
    {
        if(is_null($id)) {
            $success = $this->db->insert('rtu_map', $body);
        } else {
            $this->filter_for_curr_user($currUser);
            $this->db->where('id', $id);

            $success = $this->db->update('rtu_map', $body);
        }
        return $success;
    }

    public function delete($id, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);
        $this->db->where('id', $id);
        return $this->db->delete('rtu_map');
    }

    public function getByPue($filter, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);
        if(isset($filter['divre'])) $this->db->where('divre_kode', $filter['divre']);
        if(isset($filter['witel'])) $this->db->where('witel_kode', $filter['witel']);

        $query = $this->db
            ->select('*')
            ->from('rtu_map')
            ->where('port_pue!=""')
            ->get();
        return $query->result();
    }

}