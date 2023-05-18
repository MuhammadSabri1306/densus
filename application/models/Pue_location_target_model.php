<?php

class Pue_location_target_model extends CI_Model
{
    protected $tableName = 'pue_location_target';
    protected $tableLocationName = 'master_lokasi_gepee';

    public $currUser;

    protected $fillable_fields = [
        'divre_kode' => ['string', 'required'],
        'divre_name' => ['string', 'required'],
        'witel_kode' => ['string', 'required'],
        'witel_name' => ['string', 'required'],
        'target' => ['int', 'required']
    ];

    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get_insertable_fields()
    {
        return $this->fillable_fields;
    }
    
    public function get_updatable_fields()
    {
        return [ 'target' => $this->fillable_fields['target'] ];
    }

    protected function get_filter($filter, $prefix = null)
    {
        if(!is_array($filter) || !isset($filter['id'])) {
            return [];
        }

        $field = $prefix ? $prefix.'.id' : 'id';
        return [ $field => $filter['id'] ];
    }

    protected function get_loc_filter($filter, $prefix = null)
    {
        $filterKeys = [
            'witel' => 'witel_kode',
            'divre' => 'divre_kode'
        ];

        if($prefix) {
            foreach(['witel', 'divre'] as $key) {
                $filterKeys[$key] = $prefix . '.' . $filterKeys[$key];
            }
        }

        $appliedFilter = [];

        if(is_array($filter)) {
            if(isset($filter['witel'])) $appliedFilter[$filterKeys['witel']] = $filter['witel'];
            if(isset($filter['divre'])) $appliedFilter[$filterKeys['divre']] = $filter['divre'];
        }

        if(is_array($this->currUser)) {
            $locationId = $this->currUser['locationId'];
            if($this->currUser['level'] == 'witel') $appliedFilter[$filterKeys['witel']] = $locationId;
            elseif($this->currUser['level'] == 'divre') $appliedFilter[$filterKeys['divre']] = $locationId;
        }

        return $appliedFilter;
    }

    protected function get_datetime_filter($filter, $prefix = null)
    {
        if(!is_array($filter) || !isset($filter['datetime'])) {
            return [];
        }

        $appliedFilter = [];
        $field = $prefix ? $prefix.'.created_at' : 'created_at';

        $appliedFilter[$field.'>='] = $filter['datetime'][0];
        $appliedFilter[$field.'<='] = $filter['datetime'][1];

        return $appliedFilter;
    }

    public function get($filter)
    {
        $this->use_module('get', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function save($body, $id = null)
    {
        if(is_null($id)) {
            
            $success = $this->db->insert($this->tableName, $body);

        } else {
            
            $filter = $this->get_filter([ 'id' => $id ]);
            $this->db->where($filter);
            $success = $this->db->update($this->tableName, $body);

        }
        return $success;
    }

    public function delete($id)
    {
        $filter = $this->get_filter([ 'id' => $id ]);
        $this->db->where($filter);
        return $this->db->delete($this->tableName);
    }
}