<?php

class Ike_model extends CI_Model
{
    protected $tableName = 'ike_master';
    protected $tableLocationName = 'master_lokasi_gepee';

    public $currUser;

    private $fillable_fields = [
        'location_foreign' => [
            'id_location' => ['int', 'required']
        ],
        'main' => [
            'kwh_usage' => ['int', 'required'],
            'area_value' => ['double', 'required'],
            'week' => ['int', 'required'],
        ]
    ];

    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get_insertable_fields()
    {
        $locFields = $this->fillable_fields['location_foreign'];
        $mainFields = $this->fillable_fields['main'];
        return array_merge($locFields, $mainFields);
    }

    public function get_updatable_fields()
    {
        $fields = $this->fillable_fields['main'];
        unset($fields['week']);
        return $fields;
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
            'divre' => 'divre_kode',
            'id' => 'id'
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

    protected function get_datetime_filter($fieldName, $filter, $prefix = null)
    {
        if(!is_array($filter) || !isset($filter['datetime'])) {
            return [];
        }

        $appliedFilter = [];
        $field = $prefix ? "$prefix.$fieldName" : $fieldName;

        $appliedFilter[$field.'>='] = $filter['datetime'][0];
        $appliedFilter[$field.'<='] = $filter['datetime'][1];

        return $appliedFilter;
    }

    public function get_location_data($filter = [])
    {
        $locFilter = $this->get_loc_filter($filter);
        $dateFilterQuery = $this->get_datetime_filter_query($filter);

        $locationList = $this->db
            ->select('id AS id_location, divre_kode, divre_name, witel_kode, witel_name, sto_name AS lokasi')
            ->from($this->tableLocationName)
            ->where($locFilter)
            ->order_by('divre_kode')
            ->order_by('witel_kode')
            ->get()
            ->result_array();

        $result = [];
        foreach($locationList as $locItem) {
            
            $temp = [ 'location' => $locItem ];
            $itemFilterQuery = 'id_location='.$locItem['id_location'];
            if($dateFilterQuery) {
                $itemFilterQuery .= ' AND '.$dateFilterQuery;
            }

            $itemQuery = "SELECT
                AVG(pue_value) AS pue_value,
                YEAR(created_at) AS year,
                MONTH(created_at) AS month,
                (CASE
                    WHEN (DAY(created_at)/7+1) < 2 THEN 1
                    WHEN (DAY(created_at)/7+1) < 3 THEN 2
                    WHEN (DAY(created_at)/7+1) < 4 THEN 3
                    ELSE 4
                END) AS week
                FROM $this->tableName WHERE $itemFilterQuery
                GROUP BY year, month, week";
            $temp['pue'] = $this->db->query($itemQuery)->result_array();
            
            array_push($result, $temp);
            
        }

        return $result;
    }

    public function get_list($filter)
    {
        $this->load->helper('array');
        $this->use_module('get_list', [
            'filter' => $filter
        ]);

        return $this->result;
    }

    public function get_detail($filter)
    {
        $this->load->helper('array');
        $this->use_module('get_detail', [
            'filter' => $filter
        ]);

        return $this->result;
    }

    public function save($body, $id = null)
    {
        $body['ike_value'] = $body['kwh_usage'] / $body['area_value'];

        if(is_null($id)) {
            
            $success = $this->db->insert($this->tableName, $body);

        } else {
            
            $mainFilter = $this->get_filter([ 'id' => $id ]);
            $this->db->where($mainFilter);
            $success = $this->db->update($this->tableName, $body);

        }
        return $success;
    }

    public function delete($id)
    {
        $locFilter = $this->get_loc_filter([], 'loc');
        $mainFilter = $this->get_filter([ 'id' => $id ], 'ike');
        $this->db
            ->select('ike.*')
            ->from("$this->tableName AS ike")
            ->join("$this->tableLocationName AS loc", 'loc.id=ike.id_location')
            ->where($locFilter)
            ->where($mainFilter);
        $ike = $this->db->get()->row_array();
        if(!$ike || !isset($ike['id'])) {
            return false;
        }
        
        $this->db->where([ 'id' => $ike['id'] ]);
        return $this->db->delete($this->tableName);
    }
}