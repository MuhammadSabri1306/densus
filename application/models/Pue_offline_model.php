<?php

class Pue_offline_model extends CI_Model
{
    private $tableName = 'pue_offline';
    private $tableLocationName = 'pue_location';

    public $currUser;

    private $fillable_fields = [
        'location_foreign' => [
            'id_location' => ['int', 'required']
        ],
        'main' => [
            'daya_sdp_a' => ['int', 'required'],
            'daya_sdp_b' => ['int', 'required'],
            'daya_sdp_c' => ['int', 'required'],
            'power_factor_sdp' => ['double'],
            'daya_eq_a' => ['int', 'required'],
            'daya_eq_b' => ['int', 'required'],
            'daya_eq_c' => ['int', 'required'],
            'evidence' => ['string', 'required']
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
        return $this->fillable_fields['main'];
    }

    private function get_filter($filter, $prefix = null)
    {
        if(!is_array($filter) || !isset($filter['id'])) {
            return [];
        }

        $field = $prefix ? $prefix.'.id' : 'id';
        return [ $field => $filter['id'] ];
    }
    
    private function get_loc_filter($filter, $prefix = null)
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

    private function get_datetime_filter($filter, $prefix = null)
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

    private function get_datetime_filter_query($filter, $prefix = null)
    {
        if(!is_array($filter) || !isset($filter['datetime'])) {
            return null;
        }
        
        $field = $prefix ? $prefix.'.created_at' : 'created_at';
        $startDate = $filter['datetime'][0];
        $endDate = $filter['datetime'][1];

        return "$field BETWEEN '$startDate' AND '$endDate'";
    }

    public function get_location_data($filter = [])
    {
        $locFilter = $this->get_loc_filter($filter, 'loc');
        // $pueFilter = $this->get_filter($filter, 'pue');
        $dateFilterQuery = $this->get_datetime_filter_query($filter);

        $locationList = $this->db
            ->select()
            ->from($this->tableLocationName)
            ->where($locFilter)
            ->get()
            ->result_array();

        $result = [];
        foreach($locationList as $locItem) {
            
            $temp = [ 'location' => $locItem ];
            $itemFilterQuery = 'id_location='.$locItem['loc.id_location'];
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

    public function get($filter = [])
    {
        $pueFilter = $this->get_filter($filter, 'pue');
        $dateFilter = $this->get_datetime_filter($filter, 'pue');
        $locFilter = $this->get_loc_filter($filter, 'loc');

        $query = $this->db
            ->select('pue.*, loc.*')
            ->from($this->tableName)
            ->where($pueFilter)
            ->where($dateFilter)
            ->where($locFilter)
            ->get();

        $result = isset($filter['pue.id']) ? $query->row_array() : $query->result_array();
        return $result;
    }

    public function save($body, $id = null)
    {
        $cosPhi = isset($body['power_factor_sdp']) ? $body['power_factor_sdp'] : 1;
        $powerTotal = ($body['daya_sdp_a'] + $body['daya_sdp_b'] + $body['daya_sdp_c']) * $cosPhi;
        $powerEqp = $body['daya_eq_a'] + $body['daya_eq_b'] + $body['daya_eq_c'];
        
        $body['pue_value'] = $powerTotal / $powerEqp;
        $body['updated_at'] = date('Y-m-d H:i:s');

        if(is_null($id)) {
            
            $body['created_at'] = $body['updated_at'];
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