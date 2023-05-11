<?php

class Pue_target_model extends CI_Model
{
    protected $tableName = 'pue_target';

    public $currUser;

    public function __construct()
    {
            $this->load->database('densus');
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

    protected function get_datetime_filter_query($filter, $prefix = null)
    {
        if(!is_array($filter) || !isset($filter['datetime'])) {
            return null;
        }
        
        $field = $prefix ? $prefix.'.created_at' : 'created_at';
        $startDate = $filter['datetime'][0];
        $endDate = $filter['datetime'][1];

        return "$field BETWEEN '$startDate' AND '$endDate'";
    }

    public function get_quarter_target_by_month($month)
    {
        
    }
}