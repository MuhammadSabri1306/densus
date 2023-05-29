<?php

class Master_sto_densus_model extends CI_Model
{
    protected $tableName = 'master_sto_densus';

    public $currUser;

    protected $fillable_fields = [
        'divre_kode' => ['string', 'required'],
        'divre_name' => ['string', 'required'],
        'witel_kode' => ['string', 'required'],
        'witel_name' => ['string', 'required'],
        'datel' => ['string', 'required'],
        'sto_kode' => ['string', 'required'],
        'sto_name' => ['string', 'required'],
    ];

    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get_fields()
    {
        return $this->fillable_fields;
    }
    
    protected function get_loc_filter($filter, $prefix = null)
    {
        $filterKeys = [
            'witel' => 'witel_kode',
            'divre' => 'divre_kode',
            'id' => 'id_sto'
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
        $filterLoc = $this->get_loc_filter($filter);
        $query = $this->db
            ->select()
            ->from($this->tableName)
            ->where($filterLoc)
            ->get();

        $result = isset($filter['idLocation']) ? $query->row_array() : $query->result_array();
        return $result;
    }

    public function get_witel($filter = [])
    {
        $filterLoc = $this->get_loc_filter($filter);
        $query = $this->db
            ->select('divre_kode, divre_name, witel_kode, witel_name')
            ->from($this->tableName)
            ->where($filterLoc)
            ->group_by('witel_kode')
            ->get();

        $result = isset($filter['witel']) ? $query->row_array() : $query->result_array();
        return $result;
    }
    
    public function get_divre($filter = [])
    {
        $filterLoc = $this->get_loc_filter($filter);
        $query = $this->db
            ->select('divre_kode, divre_name')
            ->from($this->tableName)
            ->where($filterLoc)
            ->group_by('divre_kode')
            ->get();

        $result = isset($filter['divre']) ? $query->row_array() : $query->result_array();
        return $result;
    }

    public function save($body, $id = null)
    {
        if(is_null($id)) {
            
            $success = $this->db->insert($this->tableName, $body);

        } else {
            
            $filter = $this->get_loc_filter([ 'idLocation' => $id ]);
            $this->db->where($filter);
            $success = $this->db->update($this->tableName, $body);

        }
        return $success;
    }

    public function delete($id)
    {
        $filter = $this->get_loc_filter([ 'idLocation' => $id ]);
        $this->db->where($filter);
        return $this->db->delete($this->tableName);
    }
}