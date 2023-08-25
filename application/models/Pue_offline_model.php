<?php

class Pue_offline_model extends CI_Model
{
    private $tableName = 'pue_offline';
    private $tableLocationName = 'master_lokasi_gepee';

    public $currUser;

    private $fillable_fields = [
        'location_foreign' => [
            'id_location' => ['int', 'required']
        ],
        'main' => [
            'daya_sdp_a' => ['int', 'required'],
            'daya_sdp_b' => ['int', 'required'],
            'daya_sdp_c' => ['int', 'required'],
            'power_factor_sdp' => ['double', 'nullable'],
            'daya_eq_a' => ['int', 'required'],
            'daya_eq_b' => ['int', 'required'],
            'daya_eq_c' => ['int', 'nullable'],
            'evidence' => ['string', 'required']
        ]
    ];

    private $default_cos_phi = 1;

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
            // 'id' => 'id_location',
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

    // public function get_location_data($filter = [])
    // {
    //     $locFilter = $this->get_loc_filter($filter);
    //     $dateFilterQuery = $this->get_datetime_filter_query($filter);

    //     $locationList = $this->db
    //         ->select()
    //         ->from($this->tableLocationName)
    //         ->where($locFilter)
    //         ->order_by('divre_kode')
    //         ->order_by('witel_kode')
    //         ->get()
    //         ->result_array();

    //     $result = [];
    //     foreach($locationList as $locItem) {
            
    //         $temp = [ 'location' => $locItem ];
    //         $itemFilterQuery = 'id_location='.$locItem['id_location'];
    //         if($dateFilterQuery) {
    //             $itemFilterQuery .= ' AND '.$dateFilterQuery;
    //         }

    //         $itemQuery = "SELECT
    //             AVG(pue_value) AS pue_value,
    //             YEAR(created_at) AS year,
    //             MONTH(created_at) AS month,
    //             (CASE
    //                 WHEN (DAY(created_at)/7+1) < 2 THEN 1
    //                 WHEN (DAY(created_at)/7+1) < 3 THEN 2
    //                 WHEN (DAY(created_at)/7+1) < 4 THEN 3
    //                 ELSE 4
    //             END) AS week
    //             FROM $this->tableName WHERE $itemFilterQuery
    //             GROUP BY year, month, week";
    //         $temp['pue'] = $this->db->query($itemQuery)->result_array();
            
    //         array_push($result, $temp);
            
    //     }

    //     return $result;
    // }

    public function get_location_data($filter = [])
    {
        $locFilter = $this->get_loc_filter($filter);
        $dateFilterQuery = $this->get_datetime_filter_query($filter);

        $locationList = $this->db
            ->select('id AS id_location, divre_kode, divre_name, witel_kode, witel_name, sto_name AS lokasi')
            ->from($this->tableLocationName)
            ->where('tipe_perhitungan', 'pue')
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

    // public function get($filter = [])
    // {
    //     $pueFilter = $this->get_filter($filter, 'pue');
    //     $dateFilter = $this->get_datetime_filter($filter, 'pue');
    //     $locFilter = $this->get_loc_filter($filter, 'loc');

    //     $fileBasicPath = base_url(UPLOAD_PUE_EVIDENCE_PATH);
    //     $selectFields = [
    //         'pue.*', 'loc.*',
    //         "IF(pue.evidence>'', CONCAT('$fileBasicPath', pue.evidence), '#') AS evidence_url"
    //     ];

    //     $query = $this->db
    //         ->select(implode(', ', $selectFields))
    //         ->from("$this->tableName AS pue")
    //         ->join("$this->tableLocationName AS loc", 'loc.id_location=pue.id_location')
    //         ->where($pueFilter)
    //         ->where($dateFilter)
    //         ->where($locFilter)
    //         ->get();
            
    //     $result = isset($filter['pue.id']) ? $query->row_array() : $query->result_array();
    //     return $result;
    // }

    public function get($filter = [])
    {
        $pueFilter = $this->get_filter($filter, 'pue');
        $dateFilter = $this->get_datetime_filter($filter, 'pue');
        $locFilter = $this->get_loc_filter($filter, 'loc');

        $fileBasicPath = base_url(UPLOAD_PUE_EVIDENCE_PATH);
        $selectFields = [
            'pue.*', 'loc.id AS id_location', 'loc.divre_kode', 'loc.divre_name',
            'loc.witel_kode', 'loc.witel_name', 'loc.sto_name AS lokasi',
            "IF(pue.evidence>'', CONCAT('$fileBasicPath', pue.evidence), '#') AS evidence_url"
        ];

        $query = $this->db
            ->select(implode(', ', $selectFields))
            ->from("$this->tableName AS pue")
            ->join("$this->tableLocationName AS loc", 'loc.id=pue.id_location')
            ->where('loc.tipe_perhitungan', 'pue')
            ->where($pueFilter)
            ->where($dateFilter)
            ->where($locFilter)
            ->order_by('pue.created_at', 'desc')
            ->get();
            
        $result = isset($filter['pue.id']) ? $query->row_array() : $query->result_array();
        return $result;
    }

    public function save($body, $id = null)
    {
        $cosPhi = isset($body['power_factor_sdp']) ? $body['power_factor_sdp'] : 1;
        $powerTotal = ($body['daya_sdp_a'] + $body['daya_sdp_b'] + $body['daya_sdp_c']) * $cosPhi;
        if(isset($body['power_factor_sdp'])) {
            $powerTotal *= $body['power_factor_sdp']; // for Cos Phi
        }
        
        $powerEqp = $body['daya_eq_a'] + $body['daya_eq_b'];
        if(isset($body['daya_eq_c'])) {
            $powerEqp += $body['daya_eq_c'];
        }

        $body['pue_value'] = $powerTotal / $powerEqp;

        if(is_null($id)) {
            
            $success = $this->db->insert($this->tableName, $body);

        } else {
            
            $mainFilter = $this->get_filter([ 'id' => $id ]);
            // if(!isset($body['power_factor_sdp'])) {
            //     $body['power_factor_sdp'] = $this->default_cos_phi;
            // }

            $this->db->where($mainFilter);
            $success = $this->db->update($this->tableName, $body);

        }
        return $success;
    }

    // public function delete($id)
    // {
    //     $locFilter = $this->get_loc_filter([], 'loc');
    //     $mainFilter = $this->get_filter([ 'id' => $id ], 'pue');

    //     $this->db
    //         ->select('loc.*, pue.*')
    //         ->from("$this->tableName AS pue")
    //         ->join("$this->tableLocationName AS loc", 'loc.id_location=pue.id_location')
    //         ->where($locFilter)
    //         ->where($mainFilter);
    //     $pue = $this->db->get()->row_array();

    //     $isFileDeleted = false;
    //     if(isset($pue['evidence'])) {
    //         $filePath = FCPATH . UPLOAD_PUE_EVIDENCE_PATH . '/' . $pue['evidence'];
    //         $isFileDeleted = unlink($filePath);
    //     }

    //     if($isFileDeleted) {
    //         $this->db->where(['id' => $id]);
    //         return $this->db->delete($this->tableName);
    //     }

    //     return false;
    // }

    public function delete($id)
    {
        $locFilter = $this->get_loc_filter([], 'loc');
        $mainFilter = $this->get_filter([ 'id' => $id ], 'pue');
        
        $selectFields = ['loc.id AS id_location', 'loc.divre_kode', 'loc.divre_name',
            'loc.witel_kode',  'loc.witel_name', 'loc.sto_name AS lokasi', 'pue.*'];
        $this->db
            ->select(implode(', ', $selectFields))
            ->from("$this->tableName AS pue")
            ->join("$this->tableLocationName AS loc", 'loc.id=pue.id_location')
            ->where($locFilter)
            ->where($mainFilter);
        $pue = $this->db->get()->row_array();

        $isFileDeleted = false;
        if(isset($pue['evidence'])) {
            $filePath = FCPATH . UPLOAD_PUE_EVIDENCE_PATH . '/' . $pue['evidence'];
            $isFileDeleted = unlink($filePath);
        }

        if($isFileDeleted) {
            $this->db->where(['id' => $id]);
            return $this->db->delete($this->tableName);
        }

        return false;
    }

    public function get_location($filter = [])
    {
        $filter = $this->get_loc_filter($filter);
        $query = $this->db
            ->select('id AS id_location, divre_kode, divre_name, witel_kode, witel_name, sto_name AS lokasi')
            ->from($this->tableLocationName)
            ->where($filter)
            ->order_by('divre_kode')
            ->order_by('witel_kode')
            ->get();

        $result = isset($filter['id']) ? $query->row_array() : $query->result_array();
        return $result;
    }
}