<?php
class Oxisp_check_model extends CI_Model
{
    protected $tableName = 'oxisp_check';
    protected $tableCategoryName = 'oxisp_check_category';
    protected $tableLocationName = 'master_lokasi_gepee';
    
    public $currUser;

    public function __construct()
    {
            $this->load->database('densus');
    }

    protected function get_loc_filter($filter, $prefix = null, $filterKeys = [])
    {
        $filterKeys = [
            'witel' => isset($filterKeys['witel']) ? $filterKeys['witel'] : 'witel_kode',
            'divre' => isset($filterKeys['divre']) ? $filterKeys['divre'] : 'divre_kode',
            'id' => isset($filterKeys['id']) ? $filterKeys['id'] : 'id'
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

    public function get_categories()
    {
        $this->db
            ->select('*')
            ->from($this->tableCategoryName);
        return $this->db->get()->result_array();
    }

    public function get_enable_months()
    {
        $time = EnvPattern::getOxispCheckTime(true);
        $startDate = new DateTime($time->start);
        $endDate = new DateTime($time->end);
        $months = [];
        while($startDate <= $endDate) {
            array_push($months, intval($startDate->format('n')));
            $startDate->modify('+1 month');
        }
        return $months;
    }

    public function get_list_months($startDate, $endDate)
    {
        $startDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
        $monthList = [];
        while($startDate <= $endDate) {
            array_push($monthList, intval($startDate->format('n')));
            $startDate->modify('+1 month');
        }
        return $monthList;
    }

    public function get($filter = null)
    {
        $this->load->helper('array');
        $this->use_module('get', [ 'filter' => $filter ]);
        return $this->result;
    }
}