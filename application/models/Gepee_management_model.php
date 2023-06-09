<?php
class Gepee_management_model extends CI_Model
{
    protected $tableExecutionName = 'activity_execution';
    protected $tableScheduleName = 'activity_schedule';
    protected $tableCategoryName = 'activity_category';
    protected $tableLocationName = 'master_lokasi_gepee';
    protected $tableRtuName = 'rtu_map';
    protected $tablePueOfflineName = 'pue_offline';
    protected $tablePueOnlineName = 'pue_counter';
    protected $tablePueTargetName = 'pue_location_target';
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

    protected function get_pue_category($pueValue)
    {
        $pueValue = (double) $pueValue;
        
        if($pueValue <= 1.8) return 'A';
        if($pueValue <= 2) return 'B';
        if($pueValue <= 4) return 'C';
        return 'D';
    }

    public function get_pue_category_item()
    {
        return [
            [ 'category' => 'A', 'title' => '<= 1.8' ],
            [ 'category' => 'B', 'title' => '1.81 - 2.00' ],
            [ 'category' => 'C', 'title' => '2.01 - 4.00' ],
            [ 'category' => 'D', 'title' => '>= 4.01' ]
        ];
    }

    public function get_report($filter, $pueLowLimit = 1.8)
    {
        $this->use_module('get_report', [
            'filter' => $filter,
            'pueLowLimit' => $pueLowLimit
        ]);
        return $this->result;
    }

    public function get_report_v2($filter, $pueLowLimit = 1.8)
    {
        $this->use_module('get_report_v2', [
            'filter' => $filter,
            'pueLowLimit' => $pueLowLimit
        ]);
        return $this->result;
    }
    
    public function get_report_sum_nasional($filter, $pueLowLimit)
    {
        $this->use_module('get_report_sum_nasional', [
            'filter' => $filter,
            'pueLowLimit' => $pueLowLimit
        ]);
        return $this->result;
    }
    
    public function get_report_summary_nasional($filter, $pueLowLimit)
    {
        $this->use_module('get_report_summary_nasional', [
            'filter' => $filter,
            'pueLowLimit' => $pueLowLimit
        ]);
        return $this->result;
    }

    public function get_pue_report($filter = [])
    {
        $this->use_module('get_pue_report', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_location_status($filter = [])
    {
        $this->use_module('get_location_status', [ 'filter' => $filter ]);
        return $this->result;
    }
}