<?php
class Activity_schedule_model extends CI_Model
{
    private $tableName = 'activity_schedule';
    private $tableExecutionName = 'activity_execution';
    private $tableCategoryName = 'activity_category';
    private $tableLocationName = 'master_lokasi_gepee';

    public function __construct()
    {
            $this->load->database('densus');
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
        // $query = $this->db->get_compiled_select();
        // dd($query);

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

    public function store($params, $month, $divreCode, $currUser)
    {
        $timeStr = implode('-', [date('d'), $month, date('Y')]) . ' ' . date('H:i:s');
        $timestamp = date('Y-m-d H:i:s', strtotime($timeStr));
        $body = [];

        // ALLOWED LOCATION
        $this->db
            ->select('id')
            ->from($this->tableLocationName)
            ->where('divre_kode', $divreCode);
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

    public function update($params, $month, $divreCode, $currUser)
    {
        $timeStr = implode('-', [date('d'), $month, date('Y')]) . ' ' . date('H:i:s');
        $timestamp = date('Y-m-d H:i:s', strtotime($timeStr));

        $this->filter_for_curr_user($currUser);
        $this->db
            ->select($this->tableName.'.*')
            ->from($this->tableName)
            ->join($this->tableLocationName, $this->tableLocationName.'.id='.$this->tableName.'.id_lokasi')
            ->where($this->tableLocationName.'.divre_kode', $divreCode)
            ->where('MONTH(created_at)', $month);
        
        $savedSchedule = $this->db->get()->result_array();
        $checked = []; $unchecked = [];

        foreach($savedSchedule AS $saved) {
            $paramsIndex = -1;
            
            $isExists = in_array($saved['id_category'], array_column($params, 'id_category')) && in_array($saved['id_lokasi'], array_column($params, 'id_lokasi'));
            if($isExists) {
                for($i=0; $i<count($params); $i++) {
                    if($params[$i]['id_category'] == $saved['id_category'] && $params[$i]['id_lokasi'] == $saved['id_lokasi']) {
                        $paramsIndex = $i;
                        $i = count($params);
                    }
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

        $checkedSuccess = false;
        $uncheckedSuccess = false;

        // dd($checked);

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

        return $checkedSuccess && $uncheckedSuccess;
    }
}