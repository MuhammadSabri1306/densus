<?php

class Gepee_evidence_model extends CI_Model
{
    protected $tableName = 'gepee_evidence';
    protected $tableCategoryName = 'gepee_evidence_category';
    protected $tableLocationName = 'gepee_evidence_location';

    public $currUser;
    public $categories = [
        'A' => 10,
        'B' => 40,
        'C' => 50
    ];

    protected $fillable_fields = [
        'category_foreign' => [
            'id_category' => ['int', 'required']
        ],
        'location_foreign' => [
            'id_location' => ['int', 'required']
        ],
        'main' => [
            'deskripsi' => ['string', 'required'],
            'file' => ['string', 'required']
        ]
    ];

    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get_insertable_fields()
    {
        $fields = $this->fillable_fields;
        return array_merge($fields['category_foreign'], $fields['location_foreign'], $fields['main']);
    }

    public function get_updatable_fields()
    {
        $fields = $this->fillable_fields['main'];
        unset($fields['file']);
        return $fields;
    }

    protected function get_filter($filter, $key = 'main')
    {
        $appliedFilter = [];
        if(is_array($filter)) {
            if($key == 'location' && isset($filter['witel'])) $appliedFilter['loc.witel_kode'] = $filter['witel'];
            if($key == 'location' && isset($filter['divre'])) $appliedFilter['loc.divre_kode'] = $filter['divre'];
            if($key == 'main' && isset($filter['id'])) $appliedFilter['ev.id'] = $filter['id'];
        }

        if(is_array($this->currUser)) {
            $locationId = $this->currUser['locationId'];
            if($this->currUser['level'] == 'witel') $appliedFilter['loc.witel_kode'] = $locationId;
            elseif($this->currUser['level'] == 'divre') $appliedFilter['loc.divre_kode'] = $locationId;
        }

        return $appliedFilter;
    }

    protected function apply_filter($filter, $exclude = [])
    {
        $appliedFilter = $this->get_filter($filter, $exclude);
        $this->db->where($appliedFilter);
    }

    protected function get_location_filter($filter, $prefix = null)
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

    public function get_location_list($filter)
    {
        $this->use_module('get_location_list', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_location_detail($filter)
    {
        $this->use_module('get_location_detail', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_request_level($filter)
    {
        $this->use_module('get_request_level', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get($filter = null)
    {
        $this->use_module('get', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_category($code)
    {
        $this->use_module('get_category', [ 'code' => $code ]);
        return $this->result;
    }

    public function get_category_by_id($idCategory)
    {
        $this->use_module('get_category_by_id', [ 'idCategory' => $idCategory ]);
        return $this->result;
    }

    public function get_location_score($filter)
    {
        $this->use_module('get_location_score', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_category_data($filter)
    {
        $this->use_module('get_category_data', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_evidence_list($idCategory, $filter)
    {
        $this->use_module('get_evidence_list', [
            'idCategory' => $idCategory,
            'filter' => $filter
        ]);
        return $this->result;
    }

    public function get_evidence_by_id($id)
    {
        $this->use_module('get_evidence_by_id', [ 'id' => $id ]);
        return $this->result;
    }

    public function save($body, $id = null)
    {
        $this->use_module('save', [ 'body' => $body, 'id' => $id ]);
        return $this->result;
    }

    public function delete($id)
    {
        $this->use_module('delete', [ 'id' => $id ]);
        return $this->result;
    }

    public function get_excel_data($filter)
    {
        $this->use_module('get_excel_data', [ 'filter' => $filter ]);
        return $this->result;
    }
}