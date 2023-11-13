<?php
class Pi_laten_model extends CI_Model
{
    protected $tablePlnName = 't_pln_transaksi'; // db amc
    protected $tableIndoorName = 't_pln_indoor'; // db amc
    protected $tableLocationName = 'master_lokasi_gepee'; // db densus
    protected $tableWitelName = 'master_amc_witel'; // db densus
    public $currUser;

    public function __construct()
    {
        $this->load->database('densus');
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

    protected function get_amc_month_key($startMonth, $endMonth = null)
    {
        $monthKeys = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus',
            'september', 'oktober', 'november', 'desember'];

        if(is_int($endMonth) && $startMonth <= $endMonth) {
            $months = [];
            for($i = $startMonth - 1; $i < $endMonth; $i++) {
                array_push($months, $monthKeys[$i]);
            }
            return $months;
        }

        $monthIndex = (int) $startMonth - 1;
        return $monthKeys[$monthIndex];
    }

    // protected function get_datetime_filter($fieldName, $filter, $prefix = null)
    // {
    //     if(!is_array($filter) || !isset($filter['datetime'])) {
    //         return [];
    //     }

    //     $appliedFilter = [];
    //     $field = $prefix ? "$prefix.$fieldName" : $fieldName;

    //     $appliedFilter[$field.'>='] = $filter['datetime'][0];
    //     $appliedFilter[$field.'<='] = $filter['datetime'][1];

    //     return $appliedFilter;
    // }

    public function get_gepee($filter)
    {
        $this->use_module('get_gepee', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_gepee_v2($filter)
    {
        $this->load->helper('array');
        
        $savingTarget = 1 / 100;
        $this->use_module('get_gepee_v2', [
            'filter' => $filter,
            'savingTarget' => $savingTarget
        ]);
        return $this->result;
    }

    public function get_amc($filter)
    {
        $this->load->helper('array');
        $this->use_module('get_amc', [ 'filter' => $filter ]);
        return $this->result;
    }
}