<?php

class Pue_target_model extends CI_Model
{
    protected $tableName = 'pue_target';

    public $currUser;
    public $defaultLowLimit = 1.8;

    private $fillable_fields = [
        'pue_low_limit' => ['double', 'required'],
        'divre_kode' => ['string', 'required'],
        'divre_name' => ['string', 'required'],
        'witel_kode' => ['string', 'required'],
        'witel_name' => ['string', 'required']
    ];

    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get_fields()
    {
        return $this->$fillable_fields;
    }

    protected function get_filter($filter, $prefix = null)
    {
        if(!is_array($filter) || !isset($filter['id'])) {
            return [];
        }

        $field = $prefix ? $prefix.'.id' : 'id';
        return [ $field => $filter['id'] ];
    }

    protected function get_location_filter($filter, $prefix = null)
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

    public function has_target($filter)
    {
        $this->use_module('has_target', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_target($filter)
    {
        $this->use_module('get_target', [ 'filter' => $filter ]);
        return $this->result;
    }

    // public function save
}