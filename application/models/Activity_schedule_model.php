<?php
class Activity_schedule_model extends CI_Model
{
    private $tableName = 'activity_schedule';
    private $tableExecutionName = 'activity_execution';
    private $tableCategoryName = 'activity_category';
    private $tableLocationName = 'master_lokasi_gepee';

    public $currUser;

    public function __construct()
    {
            $this->load->database('densus');
    }

    private function is_time_updatable($dateTimeString)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        $itemEpoch = $dateTime->getTimestamp();
        
        $updatableTime = EnvPattern::getUpdatableActivityTime();
        return $itemEpoch >= $updatableTime->start && $itemEpoch <= $updatableTime->end;
    }

    private function mysql_updatable_time($fieldName)
    {
        $updatableTime = EnvPattern::getUpdatableActivityTime();
        return "UNIX_TIMESTAMP($fieldName)>=$updatableTime->start AND UNIX_TIMESTAMP($fieldName)<=$updatableTime->end";
    }

    private function get_filter($filter, $exclude = [])
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

    private function apply_filter($filter, $exclude = [])
    {
        $appliedFilter = $this->get_filter($filter, $exclude);
        $this->db->where($appliedFilter);
    }

    private function filter_for_curr_user($currUser)
    {
        if($currUser && $currUser['level'] == 'witel') {
            $this->db->where('witel_kode', $currUser['locationId']);
        } elseif($currUser && $currUser['level'] == 'divre') {
            $this->db->where('divre_kode', $currUser['locationId']);
        }
    }

    public function get_filtered($filter = [], $currUser = null)
    {
        $this->filter_for_curr_user($currUser);

        if(isset($filter['divre'])) $this->db->where('l.divre_kode', $filter['divre']);
        if(isset($filter['witel'])) $this->db->where('l.witel_kode', $filter['witel']);
        if(isset($filter['sto'])) $this->db->where('l.sto_kode', $filter['sto']);
        if(isset($filter['rtu'])) $this->db->where('l.rtu_kode', $filter['rtu']);
        if(isset($filter['idLokasi'])) $this->db->where('id_lokasi', $filter['idLokasi']);
        if(isset($filter['month'])) $this->db->where('MONTH(created_at)', $filter['month']);
        if(isset($filter['isChecked'])) $this->db->where('value', $filter['isChecked']);

        $currMonth = date('n');
        $query = "s.*,
            IF(MONTH(s.created_at)='$currMonth', 1, 0) AS is_enabled,
            (SELECT COUNT(*) FROM $this->tableExecutionName WHERE id_schedule=s.id) AS execution_count,
            (SELECT COUNT(*) FROM $this->tableExecutionName WHERE id_schedule=s.id AND status='approved') AS approved_count";

        $this->db
            ->select($query)
            ->from($this->tableName.' AS s')
            ->join('master_lokasi_gepee AS l', 'l.id=s.id_lokasi');
        $query = $this->db->get();

        return $query->result_array();
    }

    public function get_filtered_count($filter = [], $currUser = null)
    {
        $this->filter_for_curr_user($currUser);

        if(isset($filter['divre'])) $this->db->where('lokasi.divre_kode', $filter['divre']);
        if(isset($filter['witel'])) $this->db->where('lokasi.witel_kode', $filter['witel']);
        if(isset($filter['sto'])) $this->db->where('lokasi.sto_kode', $filter['sto']);
        if(isset($filter['rtu'])) $this->db->where('lokasi.rtu_kode', $filter['rtu']);
        if(isset($filter['idLokasi'])) $this->db->where('id_lokasi', $filter['idLokasi']);
        if(isset($filter['month'])) $this->db->where('MONTH(created_at)', $filter['month']);
        if(isset($filter['isChecked'])) $this->db->where('value', $filter['isChecked']);

        $query = $this->db
            ->select('COUNT(*) AS count')
            ->from($this->tableName)
            ->join('master_lokasi_gepee AS lokasi', 'lokasi.id='.$this->tableName.'.id_lokasi')
            ->get();
        return $query->row_array();
    }

    public function store($params, $filter, $currUser)
    {
        $month = $filter['month'];
        $divreCode = $filter['divre'];

        $timeStr = implode('-', [date('d'), $month, date('Y')]) . ' ' . date('H:i:s');
        $timestamp = date('Y-m-d H:i:s', strtotime($timeStr));
        $body = [];

        // ALLOWED LOCATION
        $this->db
            ->select('id')
            ->from($this->tableLocationName)
            ->where('divre_kode', $divreCode);
        
        if(isset($filter['witel'])) {
            $this->db->where('witel_kode', $filter['witel']);
        }

        $this->filter_for_curr_user($currUser);

        $locations = $this->db->get()->result_array();
        $locationsId = count($locations) > 0 ? array_column($locations, 'id') : [];

        // IS CHECKED
        foreach($params as $item) {
            if($item['month'] == $month && in_array($item['id_lokasi'], $locationsId)) {
                $temp = [
                    'id_category' => $item['id_category'],
                    'id_lokasi' => $item['id_lokasi'],
                    'value' => TRUE,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
                array_push($body, $temp);
            }
        }

        // IS UNCHECKED
        $this->db->reset_query();
        $categories = $this->db->get($this->tableCategoryName)->result_array();
        foreach($categories as $category) {
            foreach($locationsId as $locId) {

                $isUnchecked = ($item['month'] == $month) && in_array($category['id'], array_column($params, 'id_category'));
                if($isUnchecked) {
                    $temp = [
                        'id_category' => $category['id'],
                        'id_lokasi' => $locId,
                        'value' => FALSE,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                    array_push($body, $temp);
                }

            }
        }

        // INSERT BODY
        $this->db->reset_query();
        $this->db->insert_batch($this->tableName, $body);

        return $this->db->affected_rows() > 0;
    }

    public function update($params, $filter, $currUser)
    {
        $month = $filter['month'];
        $divreCode = $filter['divre'];

        $timeStr = implode('-', [date('d'), $month, date('Y')]) . ' ' . date('H:i:s');
        $timestamp = date('Y-m-d H:i:s', strtotime($timeStr));

        $this->filter_for_curr_user($currUser);
        $this->db
            ->select($this->tableName.'.*')
            ->from($this->tableName)
            ->join($this->tableLocationName, $this->tableLocationName.'.id='.$this->tableName.'.id_lokasi')
            ->where($this->tableLocationName.'.divre_kode', $divreCode)
            ->where('MONTH(created_at)', $month);
        
        if(isset($filter['witel'])) {
            $this->db->where($this->tableLocationName.'.witel_kode', $filter['witel']);
        }
        
        $savedSchedule = $this->db->get()->result_array();
        $checked = []; $unchecked = []; $matchedParamsIndex = [];

        foreach($savedSchedule AS $saved) {
            $isExists = in_array($saved['id_category'], array_column($params, 'id_category')) && in_array($saved['id_lokasi'], array_column($params, 'id_lokasi'));
            if($isExists) {

                $paramsIndex = -1;
                for($i=0; $i<count($params); $i++) {
                    if($params[$i]['id_category'] == $saved['id_category'] && $params[$i]['id_lokasi'] == $saved['id_lokasi']) {
                        $paramsIndex = $i;
                        $i = count($params);

                        array_push($matchedParamsIndex, $paramsIndex);
                    }
                }
                
                if($paramsIndex < 0) {
                    array_push($unchecked, [
                        'id' => $saved['id'],
                        'value' => FALSE,
                        'updated_at' => $timestamp
                    ]);
                } else {
                    array_push($checked, [
                        'id' => $saved['id'],
                        'value' => TRUE,
                        'updated_at' => $timestamp
                    ]);
                }
            }


        }

        $checkedSuccess = false;
        $uncheckedSuccess = false;

        // UPDATE CHECKED
        if(count($checked) > 0) {
            $this->db->reset_query();
            $checkedSuccess = $this->db->update_batch($this->tableName, $checked, 'id');
        }

        // UPDATE UNCHECKED
        if(count($unchecked) > 0) {
            $this->db->reset_query();
            $uncheckedSuccess = $this->db->update_batch($this->tableName, $unchecked, 'id');
        }

        $newChecked = [];
        for($i=0; $i<count($params); $i++) {
            if(!in_array($i, $matchedParamsIndex)) {
                array_push($newChecked, [
                    'id_category' => $params[$i]['id_category'],
                    'id_lokasi' => $params[$i]['id_lokasi'],
                    'value' => TRUE,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ]);
            }
        }

        if(count($newChecked) > 0) {
            $this->db->insert_batch($this->tableName, $newChecked);
        }

        return $checkedSuccess && $uncheckedSuccess;
    }

    public function save($schedule, $filter)
    {
        $this->apply_filter($filter);
        $savedSchedule = $this->db
            ->select('s.*, MONTH(s.created_at) AS month')
            ->from("$this->tableName AS s")
            ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
            ->where($this->mysql_updatable_time('s.created_at'))
            ->get()
            ->result_array();
            
        $this->apply_filter($filter, ['id', 'month']);
        $locationList = $this->db
            ->select()
            ->from("$this->tableLocationName AS l")
            ->get()
            ->result_array();
        
        // SET ITEM TIMESTAMP
        $currTimestamp = date('Y-m-d H:i:s');
        
        // UPDATE WHEN EXISTS
        $updateData = [];
        for($j=0; $j<count($savedSchedule); $j++) {

            $isUpdatable = $this->is_time_updatable($savedSchedule[$j]['created_at']);
            if($isUpdatable) {
                
                $savedItem = $savedSchedule[$j];
                $matchIndex = -1;
                for($i=0; $i<count($schedule); $i++) {

                    if(isset($schedule[$i]['id'])) {
                        $isMatch = $savedItem['id'] == $schedule[$i]['id'];
                    } else {
                        $isMatch = $savedItem['id_lokasi'] == $schedule[$i]['location'];
                        $isMatch = $isMatch && $savedItem['month'] == $schedule[$i]['month'];
                        $isMatch = $isMatch && $savedItem['id_category'] == $schedule[$i]['category'];
                    }
                    if($isMatch) {
                        $matchIndex = $i;
                        $i = count($schedule);
                    }
                }
    
                if($matchIndex >= 0) {
                    $updateItem = [
                        'id' => (int) $savedItem['id'],
                        'value' => $schedule[$matchIndex]['value'],
                        'updated_at' => $currTimestamp
                    ];
    
                    array_push($updateData, $updateItem);
                    array_splice($schedule, $matchIndex, 1);
                }
            }
        }
        
        $isUpdateSuccess = true;
        if(count($updateData) > 0) {
            $this->db->update_batch($this->tableName, $updateData, 'id');
            $isUpdateSuccess = $this->db->affected_rows() > 0;
        }
        
        // INSERT UNEXISTS
        $isInsertSuccess = true;
        if($isUpdateSuccess && count($schedule) > 0) {
            
            $insertData = [];
            list($currYear, $currDate, $currTime) = explode('-', date('Y-d-H:i:s'));
            $currDateTime = new DateTime('now');
            foreach($schedule as $insertItem) {

                $itemDateTimeStr = "$currYear-".$insertItem['month']."-$currDate $currTime";
                $isInsertable = $this->is_time_updatable($itemDateTimeStr);
                $isInsertable = $isInsertable && in_array($insertItem['location'], array_column($locationList, 'id'));
                if($isInsertable) {
                    array_push($insertData, [
                        'id_category' => (int) $insertItem['category'],
                        'id_lokasi' => (int) $insertItem['location'],
                        'value' => $insertItem['value'],
                        'created_at' => $itemDateTimeStr,
                        'updated_at' => $itemDateTimeStr
                    ]);
                }
            }
            
            if(count($insertData) > 0) {
                $this->db->insert_batch($this->tableName, $insertData);
                $isInsertSuccess = $this->db->affected_rows() > 0;
            }
        }

        $result = $isInsertSuccess ? 'successfull'
            : ($isUpdateSuccess ? 'some_error' : 'error');
        return $result;
    }

    public function get($filter)
    {
        $currMonth = date('n');
        $query = "s.*, l.divre_kode, l.witel_kode,
            IF(MONTH(s.created_at)='$currMonth', 1, 0) AS is_enabled,
            (SELECT COUNT(*) FROM $this->tableExecutionName WHERE id_schedule=s.id) AS execution_count,
            (SELECT COUNT(*) FROM $this->tableExecutionName WHERE id_schedule=s.id AND status='approved') AS approved_count";

        $this->apply_filter($filter);
        $this->db
            ->select($query)
            ->from($this->tableName.' AS s')
            ->join('master_lokasi_gepee AS l', 'l.id=s.id_lokasi');
        
        if(isset($filter['id'])) {
            $data = $this->db->get()->row_array();
            $data['is_enabled'] = $this->is_time_updatable($data['created_at']);
            
            return $data;
        }
            
        $result = $this->db->get()->result_array();
        $data = [];
        foreach($result as $row) {
            $temp = $row;
            $temp['is_enabled'] = $this->is_time_updatable($row['created_at']);
            array_push($data, $temp);
        }

        return $data;
    }
}