<?php
class Activity_execution_model extends CI_Model
{
    protected $tableName = 'activity_execution';
    protected $tableScheduleName = 'activity_schedule';
    protected $tableCategoryName = 'activity_category';
    protected $tableLocationName = 'master_lokasi_gepee';

    public $currUser;

    public function __construct()
    {
            $this->load->database('densus');
    }

    protected function mysql_updatable_time($fieldName)
    {
        $updatableTime = EnvPattern::getUpdatableActivityTime();
        return "UNIX_TIMESTAMP(e.created_at)>=$updatableTime->start AND UNIX_TIMESTAMP(e.created_at)<=$updatableTime->end";
    }

    protected function filter_for_curr_user($currUser)
    {
        if($currUser && $currUser['level'] == 'witel') {
            $this->db->where('l.witel_kode', $currUser['locationId']);
        } elseif($currUser && $currUser['level'] == 'divre') {
            $this->db->where('l.divre_kode', $currUser['locationId']);
        }
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

    public function get_filtered($scheduleId, $currUser = null)
    {
        $this->use_module('get_filtered', [
            'scheduleId' => $scheduleId,
            'currUser' => $currUser
        ]);
        return $this->result;
    }

    public function save($body, $id = null, $currUser = null)
    {
        $this->use_module('save', [
            'body' => $body,
            'id' => $id,
            'currUser' => $currUser
        ]);
        return $this->result;
    }

    public function delete($id, $currUser = null)
    {
        $this->use_module('delete', [
            'id' => $id,
            'currUser' => $currUser
        ]);
        return $this->result;
    }
    
    public function approve($id, $currUser = null)
    {
        $body = [
            'status' => 'approved',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->use_module('save', [
            'body' => $body,
            'id' => $id,
            'currUser' => $currUser
        ]);
        return $this->result;
    }

    public function reject($id, $description, $currUser = null)
    {
        $body = [
            'status' => 'rejected',
            'reject_description' => $description,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->use_module('save', [
            'body' => $body,
            'id' => $id,
            'currUser' => $currUser
        ]);
        return $this->result;
    }

    public function get_consistency_percent($currUser = null)
    {
        $this->use_module('get_consistency_percent', [ 'currUser' => $currUser ]);
        return $this->result;
    }

    public function get_calc_on_month($month, $currUser = null)
    {
        $this->use_module('get_calc_on_month', [
            'month' => $month,
            'currUser' => $currUser
        ]);
        return $this->result;
    }

    public function get_consistency_percent_on_year($currYear, $currUser = null)
    {
        $this->use_module('get_consistency_percent_on_year', [
            'currYear' => $currYear,
            'currUser' => $currUser
        ]);
        return $this->result;
    }

    public function get_status_count($currYear, $currUser = null)
    {
        $this->use_module('get_status_count', [
            'currYear' => $currYear,
            'currUser' => $currUser
        ]);
        return $this->result;
    }

    public function get_consistency_on_month($currYear, $currUser)
    {
        $this->use_module('get_consistency_on_month', [
            'currYear' => $currYear,
            'currUser' => $currUser
        ]);
        return $this->result;
    }

    public function get_performance($filter)
    {
        $this->use_module('get_performance', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_performance_v2($filter)
    {
        $this->use_module('get_performance_v2', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_performance_v3($filter)
    {
        $this->use_module('get_performance_v3', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_file_list()
    {
        $list = $this->db
            ->select('evidence')
            ->from($this->tableName)
            ->order_by('evidence')
            ->get()
            ->result_array();
        if(count($list) < 1)
            return [];
        return array_column($list, 'evidence');
    }
}