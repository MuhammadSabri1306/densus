<?php
class Monitoring_kwh_model extends CI_Model
{
    protected $tableName = 'kwh_counter';
    protected $tableRtuName = 'rtu';

    protected $densusDbName = 'juan5684_densus';
    protected $tableStoName = 'master_sto_densus';
    
    public $currUser;

    public function __construct()
    {
            $this->load->database('opnimus');
    }

    protected function get_loc_filter($filter, $prefix = null, $filterKeys = [])
    {
        // $filterKeys = [
        //     'witel' => 'witel_kode',
        //     'divre' => 'divre_kode',
        //     'id' => 'id'
        // ];
        if(!isset($filterKeys['witel'])) $filterKeys['witel'] = 'witel_kode';
        if(!isset($filterKeys['divre'])) $filterKeys['divre'] = 'divre_kode';
        if(!isset($filterKeys['id'])) $filterKeys['id'] = 'id';

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

    protected function extract_datetime_filter($fieldName, $datetime, $prefix = null)
    {
        $appliedFilter = [];
        $field = $prefix ? "$prefix.$fieldName" : $fieldName;

        $appliedFilter[$field.'>='] = $datetime[0];
        $appliedFilter[$field.'<='] = $datetime[1];
        return $appliedFilter;
    }

    protected function get_datetime_filter($fieldName, $filter, $prefix = null)
    {
        if(!is_array($filter) || !isset($filter['datetime'])) {
            return [];
        }
        return $this->extract_datetime_filter($fieldName, $filter['datetime'], $prefix);
    }

    public function get_daily($filter = null)
    {
        $this->load->helper('array');
        $this->use_module('get_daily', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_weekly($filter = null)
    {
        $this->load->helper('array');
        $this->use_module('get_weekly', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_monthly($filter = null)
    {
        $this->load->helper('array');
        $this->use_module('get_monthly', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_mom($filter = null)
    {
        $this->load->helper('array');
        $this->use_module('get_mom', [ 'filter' => $filter ]);
        return $this->result;
    }
}