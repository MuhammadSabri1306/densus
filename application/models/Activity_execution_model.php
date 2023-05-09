<?php
class Activity_execution_model extends CI_Model
{
    private $tableName = 'activity_execution';
    private $tableScheduleName = 'activity_schedule';
    private $tableCategoryName = 'activity_category';
    private $tableLocationName = 'master_lokasi_gepee';

    public $currUser;

    public function __construct()
    {
            $this->load->database('densus');
    }

    private function mysql_updatable_time($fieldName)
    {
        $updatableTime = EnvPattern::getUpdatableActivityTime();
        return "UNIX_TIMESTAMP(e.created_at)>=$updatableTime->start AND UNIX_TIMESTAMP(e.created_at)<=$updatableTime->end";
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

        $selectFields = [
            'e.*',
            "IF(e.evidence>'', CONCAT('".base_url(UPLOAD_ACTIVITY_EVIDENCE_PATH)."', e.evidence), '#') AS evidence_url"
        ];
        $selectQuery = implode(', ', $selectFields);

        $this->db
            ->select($selectQuery)
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
        
        $this->db
            ->select()
            ->from($this->tableName)
            ->where('id', $id);
        $exec = $this->db->get()->row_array();

        $isFileDeleted = false;
        if(isset($exec['evidence'])) {
            $filePath = FCPATH . UPLOAD_ACTIVITY_EVIDENCE_PATH . '/' . $exec['evidence'];
            $isFileDeleted = unlink($filePath);
        }

        if($isFileDeleted) {
            $this->db->where('id', $id);
            return $this->db->delete($this->tableName);
        }
        return false;
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

    public function get_performance($filter)
    {
        $selectScheduleId = 'SELECT s.id FROM activity_schedule AS s WHERE s.id_category=c.id AND s.id_lokasi=loc.id
            AND MONTH(s.created_at)=m.month AND YEAR(s.created_at)="2023" AND s.value=1';
        $selectExecCount = 'SELECT COUNT(*) FROM activity_execution AS e JOIN activity_schedule AS s ON s.id=e.id_schedule
            WHERE s.id_category=c.id AND s.id_lokasi=loc.id AND MONTH(s.created_at)=m.month AND YEAR(s.created_at)="'.date('Y').'"';
        $selectApprovedCount = $selectExecCount . ' AND e.status="approved"';

        $selectList = [ 'loc.id AS id_lokasi', 'loc.alamat_pel_pln', 'loc.divre_kode', 'loc.divre_name',
            'loc.witel_kode', 'loc.witel_name', 'loc.sto_kode', 'loc.sto_name', 'm.month', 'c.id AS category_id',
            'c.alias AS category_alias', 'c.activity AS category_name', "($selectScheduleId) AS id_schedule",
            "($selectExecCount) AS exec_count", "($selectApprovedCount) AS approved_count" ];
        $querySelect = implode(', ', $selectList);

        $appliedMonth = $filter['month'] ? [$filter['month']] : [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $monthQueryList = array_map(function($item) {
            return "SELECT $item AS month";
        }, $appliedMonth);
        $tableMonth = '('.implode(' UNION ALL ', $monthQueryList).')';

        $appliedLocation = [];
        if(is_array($filter)) {
            if($filter['witel']) $appliedLocation['loc.witel_kode'] = $filter['witel'];
            if($filter['divre']) $appliedLocation['loc.divre_kode'] = $filter['divre'];
        }
        if(is_array($this->currUser)) {
            if($this->currUser['level'] == 'witel') $appliedLocation['loc.witel_kode'] = $this->currUser['locationId'];
            if($this->currUser['level'] == 'divre') $appliedLocation['loc.divre_kode'] = $this->currUser['locationId'];
        }
        $whereQueryList = [];
        foreach($appliedLocation as $key => $val) {
            array_push($whereQueryList, "$key='$val'");
        }
        $queryWhere = count($whereQueryList) > 0 ? 'WHERE '.implode(' AND ', $whereQueryList) : '';

        $query = "SELECT $querySelect FROM $this->tableLocationName AS loc
            CROSS JOIN $this->tableCategoryName AS c
            CROSS JOIN $tableMonth AS m
            $queryWhere
            ORDER BY loc.id, m.month, c.id";
        
        $data = $this->db
            ->query($query)
            ->result_array();

        $data = array_map(function($item) {
            $item['exec_count'] = (int) $item['exec_count'];
            $item['approved_count'] = (int) $item['approved_count'];
            $item['percent'] = $item['exec_count'] > 0 ? $item['approved_count'] / $item['exec_count'] * 100 : 0;
            return $item;
        }, $data);
        return $data;
    }

    public function get_performance_v2($filter)
    {
        $locationFilter = [];
        if(is_array($filter)) {
            if($filter['witel']) $locationFilter['witel_kode'] = $filter['witel'];
            if($filter['divre']) $locationFilter['divre_kode'] = $filter['divre'];
        }
        if(is_array($this->currUser)) {
            if($this->currUser['level'] == 'witel') $locationFilter['witel_kode'] = $this->currUser['locationId'];
            if($this->currUser['level'] == 'divre') $locationFilter['divre_kode'] = $this->currUser['locationId'];
        }
        foreach($locationFilter as $lfKey => $lfVal) {
            $this->db->where($lfKey, $lfVal);
        }
        $locationList = $this->db
            ->select()
            ->from($this->tableLocationName)
            ->order_by('divre_kode')
            ->order_by('witel_kode')
            ->get()
            ->result_array();

        $categoryList = $this->db
            ->select()
            ->from($this->tableCategoryName)
            ->get()
            ->result_array();
        
        $startMonth = $filter['month'] ? (int) $filter['month'] : 1;
        $endMonth = $filter['month'] ? (int) $filter['month'] : 12;
        $currYear = date('Y');
        $result = [ 'month_list' => [], 'category_list' => $categoryList, 'performance' => [] ];
        foreach($locationList as $locData) {

            $item = [ 'location' => $locData, 'item' => [] ];

            for($month=$startMonth; $month<=$endMonth; $month++) {

                if(!in_array($month, $result['month_list'])) {
                    array_push($result['month_list'], $month);
                }

                foreach($categoryList as $catgData) {
                    
                    $locId = $locData['id'];
                    $catgId = $catgData['id'];
                    $selectScheduleId = "SELECT s.id FROM activity_schedule AS s WHERE s.id_category=$catgId AND s.id_lokasi=$locId
                        AND MONTH(s.created_at)='$month' AND YEAR(s.created_at)='$currYear' AND s.value=1
                        ORDER BY UNIX_TIMESTAMP(s.created_at) DESC LIMIT 1";
                    $selectExecCount = "SELECT COUNT(*) FROM activity_execution AS e JOIN activity_schedule AS s ON s.id=e.id_schedule
                        WHERE s.id_category=$catgId AND s.id_lokasi=$locId AND MONTH(s.created_at)='$month' AND YEAR(s.created_at)='$currYear'";
                    $selectApprovedCount = $selectExecCount . ' AND e.status="approved"';

                    $querySelect = "SELECT ($selectScheduleId) AS id_schedule, ($selectExecCount) AS exec_count,
                        ($selectApprovedCount) AS approved_count";
                    $queryResult = $this->db->query($querySelect)->row_array();

                    $colItem = null;
                    if(is_array($queryResult)) {
                        $colItem = $queryResult;
                        $colItem['percent'] = $queryResult['exec_count'] < 1 ? 0 : $queryResult['approved_count'] / $queryResult['exec_count'] * 100;
                        $colItem['isExists'] = $queryResult['id_schedule'] ? true : false;
                    }
                    array_push($item['item'], $colItem);
                                    
                }
            }

            array_push($result['performance'], $item);

        }
        
        return $result;
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

// SELECT
// 	loc.id AS id_lokasi,
//     m.month,
// 	c.id AS id_category,
//     (SELECT COUNT(*) FROM activity_schedule AS s
//      	WHERE s.id_category=c.id AND s.id_lokasi=loc.id AND MONTH(s.created_at)=m.month) AS schedule_count,
//     (SELECT COUNT(*) FROM activity_execution AS e JOIN activity_schedule AS s ON s.id=e.id_schedule
//      	WHERE s.id_category=c.id AND s.id_lokasi=loc.id AND MONTH(s.created_at)=m.month) AS exec_count,
//     (SELECT COUNT(*) FROM activity_execution AS e JOIN activity_schedule AS s ON s.id=e.id_schedule
//      	WHERE s.id_category=c.id AND s.id_lokasi=loc.id AND MONTH(s.created_at)=m.month) AS approved_count
// FROM master_lokasi_gepee AS loc
// CROSS JOIN activity_category AS c
// CROSS JOIN (
//     SELECT 3 AS month UNION ALL
//     SELECT 4 AS month
// ) AS m
// WHERE loc.witel_kode='DTT-ka200000'
// ORDER BY loc.id, m.month, c.id