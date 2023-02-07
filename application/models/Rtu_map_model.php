<?php

class Rtu_map_model extends CI_Model {

    public function __construct() {
        $this->load->database('densus');
    }

    public function get($id = null) {
        $this->db
            ->select()
            ->from('rtu_map');
        
        if(is_null($id)) {
            return $this->db
                ->get()
                ->result();
        }

        $this->db->where('id', $id);
        return $this->db
            ->get()
            ->row();
    }

    public function get_by_rtu_code($rtuCode) {
        $query = $this->db
            ->select()
            ->from('rtu_map')
            ->where('id', $id)
            ->get();
        return $query->row();
    }

    public function save($body, $id = null)
    {
        if(is_null($id)) {
            $success = $this->db->insert('rtu_map', $body);
        } else {
            $success = $this->db
                ->where('id', $id)
                ->update('rtu_map', $body);
        }
        return $success;
    }

    public function delete($id)
    {
        return $this->db->delete('rtu_map', [ 'id' => $id ]);
    }

}