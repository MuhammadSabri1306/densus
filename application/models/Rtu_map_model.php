<?php

class Rtu_map_model extends CI_Model
{
    protected $tableRtuName = 'rtu_map';

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

    public function get($id = null, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);
        $this->db
            ->select()
            ->from($this->tableRtuName);

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
            ->from($this->tableRtuName)
            ->where('id', $id)
            ->get();
        return $query->row();
    }

    public function save($body, $id = null, $currUser = null)
    {
        if(is_null($id)) {
            $isRtuDuplicate = $this->db
                ->select('rtu_kode')
                ->from($this->tableRtuName)
                ->where('rtu_kode', $body['rtu_kode'])
                ->get()
                ->num_rows() > 0;
            if($isRtuDuplicate) {
                throw new FormValidationException(
                    'Kode RTU sudah ada dan tidak dapat ditambahkan. Silahkan menggunakan fitur update RTU untuk melakukan perubahan.',
                    [ 'is_rtu_kode_duplicate' => true ]
                );
            }
            $success = $this->db->insert($this->tableRtuName, $body);
        } else {
            $this->filter_for_curr_user($currUser);
            $this->db->where('id', $id);

            $success = $this->db->update($this->tableRtuName, $body);
        }
        return $success;
    }

    public function delete($id, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);
        $this->db->where('id', $id);
        return $this->db->delete($this->tableRtuName);
    }

    public function getByPue($filter, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);
        if(isset($filter['divre'])) $this->db->where('divre_kode', $filter['divre']);
        if(isset($filter['witel'])) $this->db->where('witel_kode', $filter['witel']);

        $query = $this->db
            ->select('*')
            ->from($this->tableRtuName)
            ->where('port_pue!=""')
            ->get();
        return $query->result();
    }

}