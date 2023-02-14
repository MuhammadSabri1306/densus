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

    public function get($id = null, $currUser = null) {
        $this->filter_for_curr_user($currUser);
        if(is_null($id)) {
            $query = $this->db
                ->select()
                ->from('rtu_map')
                ->get();
            return $query->result();
        }

        $query = $this->db
            ->select()
            ->from('rtu_map')
            ->where('id', $id)
            ->get();
            
        return $query->row();
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

}