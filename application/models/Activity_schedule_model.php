<?php
class Activity_schedule_model extends CI_Model
{
    protected $tableName = 'activity_schedule';
    protected $tableExecutionName = 'activity_execution';
    protected $tableCategoryName = 'activity_category';
    protected $tableLocationName = 'master_lokasi_gepee';

    public $currUser;

    public function __construct()
    {
            $this->load->database('densus');
    }

    protected function is_time_updatable($dateTimeString)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        $itemEpoch = $dateTime->getTimestamp();
        
        $updatableTime = EnvPattern::getUpdatableActivityTime();
        return $itemEpoch >= $updatableTime->start && $itemEpoch <= $updatableTime->end;
    }

    protected function is_schedule_updatable($dateTimeString)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        $itemEpoch = $dateTime->getTimestamp();
        
        $updatableTime = EnvPattern::getActivityScheduleTime();
        return $itemEpoch >= $updatableTime->start && $itemEpoch <= $updatableTime->end;
    }

    protected function is_execution_updatable($dateTimeString)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        $itemEpoch = $dateTime->getTimestamp();
        
        $updatableTime = EnvPattern::getActivityExecutionTime();
        return $itemEpoch >= $updatableTime->start && $itemEpoch <= $updatableTime->end;
    }

    protected function mysql_updatable_time($fieldName)
    {
        $updatableTime = EnvPattern::getUpdatableActivityTime();
        return "UNIX_TIMESTAMP($fieldName)>=$updatableTime->start AND UNIX_TIMESTAMP($fieldName)<=$updatableTime->end";
    }

    protected function mysql_schedule_time($fieldName)
    {
        $updatableTime = EnvPattern::getActivityScheduleTime();
        return "UNIX_TIMESTAMP($fieldName)>=$updatableTime->start AND UNIX_TIMESTAMP($fieldName)<=$updatableTime->end";
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

    protected function get_filter($filter, $exclude = [])
    {
        // $appliedFilter = [ $appliedFilter['YEAR(s.created_at)'] = date('Y') ];
        if(count($exclude) > 0) {
            $temp = [];
            foreach($filter as $key => $val) {
                if(!in_array($key, $exclude)) $temp[$key] = $val;
            }
            $filter = $temp;
        }
        
        if(is_array($filter)) {
            if($filter['witel']) $appliedFilter['l.witel_kode'] = $filter['witel'];
            if($filter['divre']) $appliedFilter['l.divre_kode'] = $filter['divre'];
            if($filter['id']) $appliedFilter['s.id'] = $filter['id'];
            if($filter['month']) $appliedFilter['MONTH(s.created_at)'] = $filter['month'];
        }

        if(is_array($this->currUser)) {
            $locationId = $this->currUser['locationId'];
            if($this->currUser['level'] == 'witel') $appliedFilter['l.witel_kode'] = $locationId;
            elseif($this->currUser['level'] == 'divre') $appliedFilter['l.divre_kode'] = $locationId;
        }

        return $appliedFilter;
    }

    protected function apply_filter($filter, $exclude = [])
    {
        $appliedFilter = $this->get_filter($filter, $exclude);
        $this->db->where($appliedFilter);
    }

    protected function filter_for_curr_user($currUser)
    {
        if($currUser && $currUser['level'] == 'witel') {
            $this->db->where('witel_kode', $currUser['locationId']);
        } elseif($currUser && $currUser['level'] == 'divre') {
            $this->db->where('divre_kode', $currUser['locationId']);
        }
    }

    public function get_filtered($filter = [], $currUser = null)
    {
        $this->use_module('get_filtered', [ 'filter' => $filter, 'currUser' => $currUser ]);
        return $this->result;
    }

    public function get_filtered_count($filter = [], $currUser = null)
    {
        $this->use_module('get_filtered_count', [ 'filter' => $filter, 'currUser' => $currUser ]);
        return $this->result;
    }

    public function store($params, $filter, $currUser)
    {
        $this->use_module('store', [
            'params' => $params,
            'filter' => $filter,
            'currUser' => $currUser
        ]);
        return $this->result;
    }

    public function update($params, $filter, $currUser)
    {
        $this->use_module('update', [
            'params' => $params,
            'filter' => $filter,
            'currUser' => $currUser
        ]);
        return $this->result;
    }

    public function save($schedule, $filter)
    {
        $this->use_module('save', [
            'schedule' => $schedule,
            'filter' => $filter
        ]);
        return $this->result;
    }

    public function get($filter)
    {
        $this->use_module('get', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_all_v2($filter)
    {
        $this->use_module('get_all_v2', [ 'filter' => $filter ]);
        return $this->result;
    }
}