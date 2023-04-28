<?php

class Pue_location_model extends CI_Model
{
    private $tableName = 'pue_location';

    public $currUser;

    private $fillable_fields = [
        'divre_kode' => ['string', 'required'],
        'divre_name' => ['string', 'required'],
        'witel_kode' => ['string', 'required'],
        'witel_name' => ['string', 'required'],
        'lokasi' => ['string', 'required']
    ];

    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get_fields()
    {
        return $this->fillable_fields;
    }

    private function get_filter($filter, $prefix = null)
    {
        $filterKeys = [
            'witel' => 'witel_kode',
            'divre' => 'divre_kode',
            'id' => 'id_location'
        ];

        if($prefix) {
            foreach(['witel', 'divre', 'id'] as $key) {
                $filterKeys[$key] = $prefix . '.' . $filterKeys[$key];
            }
        }

        $appliedFilter = [];

        if(is_array($filter)) {
            if(isset($filter['witel'])) $appliedFilter[$filterKeys['witel']] = $filter['witel'];
            if(isset($filter['divre'])) $appliedFilter[$filterKeys['divre']] = $filter['divre'];
            if(isset($filter['idLocation'])) $appliedFilter[$filterKeys['id']] = $filter['idLocation'];
        }

        if(is_array($this->currUser)) {
            $locationId = $this->currUser['locationId'];
            if($this->currUser['level'] == 'witel') $appliedFilter[$filterKeys['witel']] = $locationId;
            elseif($this->currUser['level'] == 'divre') $appliedFilter[$filterKeys['divre']] = $locationId;
        }

        return $appliedFilter;
    }

    public function get($filter = [])
    {
        $filter = $this->get_filter($filter);
        $query = $this->db
            ->select()
            ->from($this->tableName)
            ->where($filter)
            ->get();

        $result = isset($filter['id_location']) ? $query->row_array() : $query->result_array();
        return $result;
    }

    public function save($body, $id = null)
    {
        if(is_null($id)) {
            
            $success = $this->db->insert($this->tableName, $body);

        } else {
            
            $filter = $this->get_filter([ 'idLocation' => $id ]);
            $this->db->where($filter);
            $success = $this->db->update($this->tableName, $body);

        }
        return $success;
    }

    public function delete($id)
    {
        $filter = $this->get_filter([ 'idLocation' => $id ]);
        $this->db->where($filter);
        return $this->db->delete($this->tableName);
    }
}