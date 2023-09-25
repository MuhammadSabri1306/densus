<?php
class Oxisp_check_model extends CI_Model
{
    protected $tableName = 'oxisp_check';
    protected $tableCategoryName = 'oxisp_check_category';
    protected $tableRoomName = 'oxisp_check_room';
    protected $tableLocationName = 'master_sto_densus';
    
    public $currUser;
    public $enabledDateTime;
    public $enabledTime;

    protected $fillable_fields = [
        'id_location' => ['int', 'required'],
        'id_room' => ['int', 'required'],
        'check_category_code' => ['string', 'required'],
        'is_ok' => ['bool', 'required'],
        'is_room_exists' => ['bool', 'required'],
        'note' => ['string', 'required'],
        'evidence' => ['string', 'required'],
        'reject_description' => ['string', 'required'],
        'year' => ['int', 'required'],
        'month' => ['int', 'required'],
    ];

    public function __construct()
    {
            $this->load->database('densus');
            $this->enabledDateTime = EnvPattern::getOxispCheckTime(true);
            $this->enabledTime = EnvPattern::getOxispCheckTime(false);
    }

    public function get_insertable_fields()
    {
        $keys = array_diff(array_keys($this->fillable_fields), ['reject_description']);
        $fields = [];
        foreach($keys as $key) {
            $fields[$key] = $this->fillable_fields[$key];
        }
        return $fields;
    }

    public function get_updatable_fields()
    {
        $keys = ['is_ok', 'is_room_exists', 'note', 'evidence'];
        $fields = [];
        foreach($keys as $key) {
            $fields[$key] = $this->fillable_fields[$key];
        }
        return $fields;
    }

    public function get_rejectable_fields()
    {
        $keys = ['reject_description'];
        $fields = [];
        foreach($keys as $key) {
            $fields[$key] = $this->fillable_fields[$key];
        }
        return $fields;
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
    
    public function get_rooms()
    {
        $this->db
            ->select('*')
            ->from($this->tableRoomName);
        return $this->db->get()->result_array();
    }

    public function get_enable_months()
    {
        $time = $this->enabledDateTime;
        $startDate = new DateTime($time->start);
        $endDate = new DateTime($time->end);
        $months = [];
        while($startDate <= $endDate) {
            array_push($months, intval($startDate->format('n')));
            $startDate->modify('+1 month');
        }
        return $months;
    }

    public function is_month_enabled($month, $year)
    {
        $enabledTime = $this->enabledTime;
        $time = ( new DateTime("$year-$month-01") )->getTimestamp();
        return $time >= $enabledTime->start && $time <= $enabledTime->end;
    }

    public function is_datetime_enabled($datetimeStr)
    {
        $enabledTime = $this->enabledTime;
        $time = ( new DateTime($datetimeStr) )->getTimestamp();
        return $time >= $enabledTime->start && $time <= $enabledTime->end;
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

    public function get_list($filter = [])
    {
        $this->load->helper('array');
        $this->use_module('get_list', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get($filter = [])
    {
        $this->load->helper('array');
        $this->use_module('get', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function save($body, $id = null)
    {
        if(is_null($id)) {
            
            $success = $this->db->insert($this->tableName, $body);

        } else {
            
            $this->db->where('id', $id);
            $success = $this->db->update($this->tableName, $body);

        }
        return $success;
    }

    public function delete($id)
    {
        $this->db
            ->select()
            ->from($this->tableName)
            ->where('id', $id);
        $check = $this->db->get()->row_array();

        $isFileDeleted = false;
        if(isset($check['evidence'])) {
            $filePath = FCPATH . UPLOAD_OXISP_CHECK_EVIDENCE_PATH . '/' . $check['evidence'];
            $isFileDeleted = unlink($filePath);
        } else {
            $isFileDeleted = true;
        }

        if($isFileDeleted) {
            $this->db->where('id', $id);
            return $this->db->delete($this->tableName);
        }

        return false;
    }
}