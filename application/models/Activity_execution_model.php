<?php
class Activity_execution_model extends CI_Model
{
    private $tableName = 'activity_execution';
    private $tableScheduleName = 'activity_schedule';
    private $tableCategoryName = 'activity_category';
    private $tableLocationName = 'master_lokasi_gepee';

    public function __construct()
    {
            $this->load->database('densus');
    }

    private function filter_for_curr_user($currUser)
    {
        if($currUser && $currUser['level'] == 'witel') {
            $this->db->where('l.witel_kode', $currUser['locationId']);
        } elseif($currUser && $currUser['level'] == 'divre') {
            $this->db->where('l.divre_kode', $currUser['locationId']);
        }
    }

    public function get_filtered($scheduleId, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);

        $this->db
            ->select("e.*, IF(e.evidence>'', CONCAT('".base_url(UPLOAD_ACTIVITY_EVIDENCE_PATH)."', e.evidence), '#') AS evidence_url")
            ->from("$this->tableName AS e")
            ->join("$this->tableScheduleName AS s", 's.id=e.id_schedule')
            ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
            ->where('e.id_schedule', $scheduleId)
            ->order_by('updated_at', 'DESC');
        // dd($this->db->get_compiled_select());
        $query = $this->db->get();
        return $query->result_array();
    }

    public function save($body, $id = null, $currUser = null)
    {
        if(is_null($id)) {
            $success = $this->db->insert($this->tableName, $body);
        } else {
            // $this->db->_protect_identifiers = false;
            // foreach($body AS $key => $val) {
            //     $this->db->set("e.$key", $val);
            // }

            // $this->filter_for_curr_user($currUser);
            $this->db->where('id', $id);

            // $sqlJoin = "$this->tableName AS e JOIN $this->tableScheduleName AS s ON s.id=e.id_schedule JOIN $this->tableLocationName AS l ON l.id=s.id_lokasi";
            $success = $this->db->update($this->tableName, $body);
        }
        return $success;
    }

    public function delete($id, $currUser = null)
    {
        // $this->filter_for_curr_user($currUser);
        $this->db->where('id', $id);
        return $this->db->delete($this->tableName);
    }
    
    public function approve($id, $currUser = null)
    {
        $this->db->where('id', $id);

        $body = [
            'status' => 'approved',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->update($this->tableName, $body);
    }

    public function reject($id, $description, $currUser = null)
    {
        $this->db->where('id', $id);
        $body = [
            'status' => 'rejected',
            'reject_description' => $description,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->update($this->tableName, $body);
    }

    public function get_consistency_percent($currUser = null)
    {
        if(!is_null($currUser)) $this->filter_for_curr_user($currUser);
        if(isset($filter['divre'])) $this->db->where('l.divre_kode', $filter['divre']);
        if(isset($filter['witel'])) $this->db->where('l.witel_kode', $filter['witel']);
        if(isset($filter['month'])) $this->db->where('MONTH(e.created_at)', $filter['month']);

        $this->db
            ->select('COUNT(*) AS all_activity, SUM(IF(e.status="approved",1,0)) AS approved_activity')
            ->from("$this->tableName AS e")
            ->join("$this->tableScheduleName AS s", 's.id=e.id_schedule')
            ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
            ->where('s.value', '1');

        $data = $this->db->get()->row();
        $result = $data ? ($data->approved_activity / $data->all_activity) * 100 : 0;
        $result = round($result, 2);
        return $result;
    }

    public function get_calc_on_month($month, $currUser = null)
    {
        $result = [
            'total_activity' => null,
            'total_approved' => null,
            'unactive_witel' => null
        ];

        if(!is_null($currUser)) $this->filter_for_curr_user($currUser);
        if(isset($filter['divre'])) $this->db->where('l.divre_kode', $filter['divre']);
        if(isset($filter['witel'])) $this->db->where('l.witel_kode', $filter['witel']);
        if(isset($filter['month'])) $this->db->where('MONTH(e.created_at)', $filter['month']);

        $this->db
            ->select('COUNT(*) AS total, SUM(IF(e.status="approved",1,0)) AS total_approved')
            ->from("$this->tableName AS e")
            ->join("$this->tableScheduleName AS s", 's.id=e.id_schedule')
            ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
            ->where('s.value', '1');

        $data = $this->db->get()->row();
        if($data) {
            $result['total_activity'] = (int) $data->total;
            $result['total_approved'] = (int) $data->total_approved;
        }

        if(!is_null($currUser)) $this->filter_for_curr_user($currUser);
        if(isset($filter['divre'])) $this->db->where('l.divre_kode', $filter['divre']);
        if(isset($filter['witel'])) $this->db->where('l.witel_kode', $filter['witel']);
        if(isset($filter['month'])) {
            $this->db->where("s.id IN (SELECT id_schedule FROM $this->tableName) WHERE MONTH(created_at)=".$filter['month']);
        } else {
            $this->db->where("s.id IN (SELECT id_schedule FROM $this->tableName)");
        }
        $hasActivityCount = $this->db
            ->distinct()
            ->select('l.witel_name')
            ->from("$this->tableScheduleName AS s")
            ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
            ->get()
            ->num_rows();

        if(!is_null($currUser)) $this->filter_for_curr_user($currUser);
        if(isset($filter['divre'])) $this->db->where('l.divre_kode', $filter['divre']);
        if(isset($filter['witel'])) $this->db->where('l.witel_kode', $filter['witel']);
        $witelCount = $this->db
            ->select('witel_kode')
            ->distinct()
            ->from("$this->tableLocationName AS l")
            ->get()
            ->num_rows();
            
        if($hasActivityCount && $witelCount) {
            $result['unactive_witel'] = $witelCount - $hasActivityCount;
        }
        
        return $result;
    }

    public function get_consistency_percent_on_year($currYear, $currUser = null)
    {
        if(!is_null($currUser)) $this->filter_for_curr_user($currUser);

        $this->db
            ->select('COUNT(*) AS all_activity, COALESCE(SUM(IF(e.status="approved",1,0)), 0) AS approved_activity')
            ->from("$this->tableName AS e")
            ->join("$this->tableScheduleName AS s", 's.id=e.id_schedule')
            ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
            ->where('s.value', '1')
            ->where('YEAR(e.created_at)', $currYear);
            
        $data = $this->db->get()->row();
        $result = is_null($data) || $data->all_activity == 0 ? 0 : ($data->approved_activity / $data->all_activity) * 100;
        return $result;
    }

    public function get_status_count($currYear, $currUser = null)
    {
        if(!is_null($currUser)) $this->filter_for_curr_user($currUser);

        $this->db
            ->select('COALESCE(SUM(IF(e.status="approved",1,0)), 0) AS approved, COALESCE(SUM(IF(e.status="rejected",1,0)), 0) AS rejected, COALESCE(SUM(IF(e.status="submitted",1,0)), 0) AS submitted')
            ->from("$this->tableName AS e")
            ->join("$this->tableScheduleName AS s", 's.id=e.id_schedule')
            ->join("$this->tableLocationName AS l", 'l.id=s.id_lokasi')
            ->where('s.value', '1')
            ->where('YEAR(e.created_at)', $currYear);
        // dd($this->db->get_compiled_select());
        $data = $this->db->get()->row();
        return $data;
    }

    public function get_consistency_on_month($currYear, $currUser)
    {
        $currMonth = (int) date('n');
        $queryMonthArr = [];
        for($month=1; $month<=$currMonth; $month++) {
             array_push($queryMonthArr, "SELECT $month AS month");
        }
        $queryMonthList = implode(' UNION ', $queryMonthArr);

        $filterTotal = [ 's.value=1', 'MONTH(e.created_at)=m.month', "YEAR(e.created_at)=$currYear" ];
        if(isset($currUser)) {
            extract($currUser, EXTR_PREFIX_ALL, 'user');
            if(isset($user_level) && $user_level=='witel') {
                array_push($filterTotal, "l.witel_kode='$user_locationId'");
            } elseif(isset($user_level) && $user_level=='divre') {
                array_push($filterTotal, "l.divre_kode='$user_locationId'");
            }
        }

        $filterApproved = array_merge([ 'status="approved"' ], $filterTotal);
        $filterApproved = implode(' AND ', $filterApproved);
        $filterTotal = implode(' AND ', $filterTotal);
        $querySelect = "SELECT COUNT(*) FROM $this->tableName AS e
            JOIN $this->tableScheduleName AS s ON s.id=e.id_schedule
            JOIN $this->tableLocationName AS l ON l.id=s.id_lokasi";
        
        $queryApproved = "$querySelect WHERE $filterApproved";
        $queryTotal = "$querySelect WHERE $filterTotal";

        $sql_query = "SELECT m.month, (($queryApproved)/($queryTotal)*100) AS percent FROM ($queryMonthList) AS m";
        $query = $this->db->query($sql_query);
        
        $data = $query->result();
        return $data;
    }
}