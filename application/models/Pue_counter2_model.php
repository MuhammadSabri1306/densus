<?php
class Pue_Counter2_model extends CI_Model {

    private $tableName = 'pue_counter';
    private $tableRtuMapName = 'rtu_map';

    public function __construct()
    {
        $this->load->database('densus');
    }

    private function get_filter($filter, $exclude = [])
    {
        $appliedFilter = [];
        if(count($exclude) > 0) {
            $temp = [];
            foreach($filter as $key => $val) {
                if(!in_array($key, $exclude)) $temp[$key] = $val;
            }
            $filter = $temp;
        }
        
        if(is_array($filter)) {
            if($filter['witel']) $appliedFilter['r.witel_kode'] = $filter['witel'];
            if($filter['divre']) $appliedFilter['r.divre_kode'] = $filter['divre'];
            if($filter['id']) $appliedFilter['s.id'] = $filter['id'];
            if($filter['month']) $appliedFilter['MONTH(s.created_at)'] = $filter['month'];
        }

        if(is_array($this->currUser)) {
            $locationId = $this->currUser['locationId'];
            if($this->currUser['level'] == 'witel') $appliedFilter['r.witel_kode'] = $locationId;
            elseif($this->currUser['level'] == 'divre') $appliedFilter['r.divre_kode'] = $locationId;
        }

        return $appliedFilter;
    }

    private function apply_filter($filter, $exclude = [])
    {
        $appliedFilter = $this->get_filter($filter, $exclude);
        $this->db->where($appliedFilter);
    }

    public function get_data_level_by_filter($filter)
    {
        $select = null;
        if(isset($filter['rtu'])) {
            $this->db->where('rtu_kode', $filter['rtu']);
            $select = 'divre_kode, divre_name, witel_kode, witel_name, rtu_kode, rtu_name';
        } elseif(isset($filter['witel'])) {
            $this->db->where('witel_kode', $filter['witel']);
            $select = 'divre_kode, divre_name, witel_kode, witel_name';
        } elseif(isset($filter['divre'])) {
            $this->db->where('divre_kode', $filter['divre']);
            $select = 'divre_kode, divre_name';
        }

        $this->db
            ->select($select)
            ->from($this->tableRtuMapName);
        return $this->db->get()->row_array();
    }

    public function get_curr_year($rtuCode)
    {
        $year = date('Y');
        $this->db
            ->select('pue_value, timestamp')
            ->from($this->tableName)
            ->where('rtu_kode', $rtuCode)
            ->where('YEAR(timestamp)', $year)
            ->order_by('timestamp', 'desc');
        
        $data = $this->db->get()->result();
        return $data;
    }

    public function get_zone_avg_on_curr_year($zone)
    {
        $start_of_last_week = new DateTime('-7 days');
        $end_of_last_week = new DateTime();
        $end_of_last_week->setTime(23, 59, 59);

        $start_date = $start_of_last_week->format('Y-m-d');
        $end_date = $end_of_last_week->format('Y-m-d');

        $filter = ' WHERE ';
        if(isset($zone['witel'])) {
            $code = $zone['witel'];
            $filter .= "r.witel_kode='$code' AND ";
        } elseif(isset($zone['divre'])) {
            $code = $zone['divre'];
            $filter .= "r.divre_kode='$code' AND ";
        }
        $filter .= "DATE(timestamp) BETWEEN DATE('$start_date') AND DATE('$end_date')";

        $group = ['DATE', 'MONTH', 'DAY', 'HOUR'];
        $groupFilters = array_map(function($item) {
            return "$item(timestamp)=$item(p.timestamp)";
        }, $group);
        $qGroupFilters = 'WHERE ' . implode(' AND ', $groupFilters);

        $groupItems = array_map(function($item) {
            return "$item(p.timestamp)";
        }, $group);
        $qGroupItems = 'GROUP BY ' . implode(', ', $groupItems);

        $q = "SELECT p.timestamp, (SELECT COALESCE(AVG(pue_value), 0) FROM pue_counter $qGroupFilters) AS pue_value
            FROM $this->tableName AS p
            JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode
            $filter $qGroupItems";
        
        $query = $this->db->query($q);
        return $query->result();
    }
    
    public function get_curr_year_avg_on_sto($zone)
    {
        $currTimes = get_time_range('currMonth', 'currWeek', 'currDay');
        $qAvgs = array_map(function($row) {

            list($key, $startDate, $endDate) = $row;
            return "(SELECT COALESCE(AVG(pue_value), 0) FROM $this->tableName
                WHERE rtu_kode=p.rtu_kode AND (timestamp BETWEEN '$startDate' AND '$endDate')) AS $key";

        }, $currTimes);
        $qAvgs = implode(', ', $qAvgs);
        
        $q = "SELECT r.divre_kode, r.divre_name, r.witel_kode, r.witel_name, r.rtu_kode, r.rtu_name, $qAvgs
            FROM $this->tableName AS p JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode";
        
        if(isset($zone['witel'])) {
            $code = $zone['witel'];
            $q .= " WHERE r.witel_kode='$code'";
        } elseif(isset($zone['divre'])) {
            $code = $zone['divre'];
            $q .= " WHERE r.divre_kode='$code'";
        }

        $q .= ' GROUP BY r.rtu_kode ORDER BY r.divre_kode, r.witel_kode, r.rtu_kode';
        $query = $this->db->query($q);
        $data = $query->result();

        return [
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    public function get_latest_value($rtuCode)
    {
        $this->db
            ->select('pue_value, timestamp')
            ->from($this->tableName)
            ->where('rtu_kode', $rtuCode)
            ->order_by('timestamp', 'desc');
        return $this->db->get()->row();
    }

    public function get_latest_avg_value_on_zone($zone)
    {
        $filter = ["(p.rtu_kode, p.timestamp) IN (SELECT rtu_kode, MAX(timestamp) FROM $this->tableName GROUP BY rtu_kode)"];
        if(isset($zone['witel'])) {
            $code = $zone['witel'];
            array_push($filter, "r.witel_kode='$code'");
        } elseif(isset($zone['divre'])) {
            $code = $zone['divre'];
            array_push($filter, "r.divre_kode='$code'");
        }
        array_push($filter, "p.pue_value>0");

        $appliedFilter = implode(' AND ', $filter);
        $q = "SELECT COALESCE(AVG(p.pue_value), 0) AS avg, p.timestamp FROM $this->tableName AS p
            JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode WHERE $appliedFilter";
        $query = $this->db->query($q);
        return $query->row();
    }

    public function get_max_value($zone)
    {
        $filter = ['YEAR(p.timestamp)=' . date('Y')];
        if(isset($zone['rtu'])) {
            $code = $zone['rtu'];
            array_push($filter, "r.rtu_kode='$code'");
        } elseif(isset($zone['witel'])) {
            $code = $zone['witel'];
            array_push($filter, "r.witel_kode='$code'");
        } elseif(isset($zone['divre'])) {
            $code = $zone['divre'];
            array_push($filter, "r.divre_kode='$code'");
        }

        $appliedFilter = implode(' AND ', $filter);
        $join = "$this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode";
        $q = "SELECT pue_value, timestamp FROM $this->tableName AS p JOIN $join WHERE $appliedFilter
            AND p.pue_value=(SELECT MAX(p.pue_value) FROM $this->tableName AS p JOIN $join WHERE $appliedFilter)
            ORDER BY p.timestamp DESC LIMIT 1";
            
        $query = $this->db->query($q);
        return $query->row();
    }

    public function get_avg_value($zone)
    {
        $queries = [];
        $currTimes = get_time_range('currMonth', 'currWeek', 'currDay', 'currHour');
        $filter = [];
        if(isset($zone['rtu'])) {
            $code = $zone['rtu'];
            array_push($filter, "r.rtu_kode='$code'");
        } elseif(isset($zone['witel'])) {
            $code = $zone['witel'];
            array_push($filter, "r.witel_kode='$code'");
        } elseif(isset($zone['divre'])) {
            $code = $zone['divre'];
            array_push($filter, "r.divre_kode='$code'");
        }
        array_push($filter, "p.pue_value>0");

        foreach($currTimes as $item) {
            
            list($key, $startDate, $endDate) = $item;
            $basicFilter = ["(p.timestamp BETWEEN '$startDate' AND '$endDate')"];
            $appliedFilter = array_merge($filter, $basicFilter);
            $appliedFilter = implode(' AND ', $appliedFilter);

            $keySum = $key.'Sum';
            $keyCount = $key.'Count';
            $temp = "(SELECT SUM(p.pue_value) FROM $this->tableName AS p JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode WHERE $appliedFilter) AS $keySum,
                (SELECT COUNT(*) FROM $this->tableName AS p JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode WHERE $appliedFilter) AS $keyCount";
            array_push($queries, $temp);
            
        }

        $q = 'SELECT ' . implode(', ', $queries);
        $query = $this->db->query($q);
        $data = $query->row_array();

        $result = [
            'currMonth' => null,
            'currWeek' => null,
            'currDay' => null,
            'currHour' => null,
            'timestamp' => date('Y-m-d h:i:s')
        ];
        
        if(is_array($data)) {

            if(isset($data['currMonthSum'], $data['currMonthCount'])) {
                $result['currMonth'] = $data['currMonthCount'] > 0 ? $data['currMonthSum'] / $data['currMonthCount'] : 0;
            }

            if(isset($data['currWeekSum'], $data['currWeekCount'])) {
                $result['currWeek'] = $data['currWeekCount'] > 0 ? $data['currWeekSum'] / $data['currWeekCount'] : 0;
            }

            if(isset($data['currDaySum'], $data['currDayCount'])) {
                $result['currDay'] = $data['currDayCount'] > 0 ? $data['currDaySum'] / $data['currDayCount'] : 0;
            }

            if(isset($data['currHourSum'], $data['currHourCount'])) {
                $result['currHour'] = $data['currHourCount'] > 0 ? $data['currHourSum'] / $data['currHourCount'] : 0;
            }

        }
            
        return $result;
    }

    public function get_avg_value_v2($zone)
    {
        $filter = [];
        if(isset($zone['rtu'])) {
            $filter['r.rtu_kode'] = $zone['rtu'];
        } elseif(isset($zone['witel'])) {
            $filter['r.witel_kode'] = $zone['witel'];
        } elseif(isset($zone['divre'])) {
            $filter['r.divre_kode'] = $zone['divre'];
        }

        foreach($currTimes as $item) {
            
            list($key, $startDate, $endDate) = $item;
            $basicFilter = ["(p.timestamp BETWEEN '$startDate' AND '$endDate')"];
            $appliedFilter = array_merge($filter, $basicFilter);
            $appliedFilter = implode(' AND ', $appliedFilter);

            $temp = "(SELECT AVG(p.pue_value) FROM $this->tableName AS p
                JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode
                WHERE $appliedFilter) AS $key";
            // $temp = "(SELECT SUM(p.pue_value) FROM $this->tableName AS p JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode WHERE $appliedFilter) AS $key,
            // (SELECT SUM(IF(p.pue_value>0,1,0)) FROM $this->tableName AS p JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode WHERE $appliedFilter) AS $key";
            array_push($queries, $temp);
            
        }

        $q = 'SELECT ' . implode(', ', $queries);
        // dd($q);
        $query = $this->db->query($q);

        $data = $query->row_array();
        if(!is_array($data)) $data = [];
        $data['timestamp'] = date('Y-m-d h:i:s');

        return $data;
    }

    public function get_performance_value($zone)
    {
        $queries = [];
        $currTimes = get_time_range('currMonth', 'prevMonth', 'currWeek', 'prevWeek', 'currDay', 'prevDay');

        $filter = [];
        if(isset($zone['rtu'])) {
            $code = $zone['rtu'];
            array_push($filter, "r.rtu_kode='$code'");
        } elseif(isset($zone['witel'])) {
            $code = $zone['witel'];
            array_push($filter, "r.witel_kode='$code'");
        } elseif(isset($zone['divre'])) {
            $code = $zone['divre'];
            array_push($filter, "r.divre_kode='$code'");
        }
        array_push($filter, 'p.pue_value>0');

        foreach($currTimes as $item) {
            
            list($key, $startDate, $endDate) = $item;
            $appliedFilter = array_merge($filter, ["(p.timestamp BETWEEN '$startDate' AND '$endDate')"]);
            $appliedFilter = implode(' AND ', $appliedFilter);

            $temp = "(SELECT COALESCE(AVG(p.pue_value), 0) FROM $this->tableName AS p
                JOIN $this->tableRtuMapName AS r ON r.rtu_kode=p.rtu_kode
                WHERE $appliedFilter) AS $key";
            array_push($queries, $temp);
            
        }

        $q = 'SELECT ' . implode(', ', $queries);
        $query = $this->db->query($q);
        $data = $query->row_array();

        if(!$data) return null;
        $result = [ 'timestamp' => date('Y-m-d h:i:s') ];

        if($data['currMonth']) {
            if($data['prevMonth']) {
                $data['currMonth'] = (double) $data['currMonth'];
                $data['prevMonth'] = (double) $data['prevMonth'];
                $result['currMonth'] = !$data['prevMonth'] ? 100 : ($data['currMonth'] - $data['prevMonth']) / $data['prevMonth'] * 100;
            } else {
                $result['currMonth'] = 0;
            }
        }

        if($data['currWeek']) {
            if($data['prevWeek']) {
                $data['currWeek'] = (double) $data['currWeek'];
                $data['prevWeek'] = (double) $data['prevWeek'];
                $result['currWeek'] = ($data['currWeek'] - $data['prevWeek']) / $data['prevWeek'] * 100;
            } else {
                $result['currWeek'] = 0;
            }
        }

        if($data['currDay']) {
            if($data['prevDay']) {
                $data['currDay'] = (double) $data['currDay'];
                $data['prevDay'] = (double) $data['prevDay'];
                $result['currDay'] = ($data['currDay'] - $data['prevDay']) / $data['prevDay'] * 100;
            } else {
                $result['currDay'] = 0;
            }
        }

        if($data['currHour']) {
            if($data['prevHour']) {
                $data['currHour'] = (double) $data['currHour'];
                $data['prevHour'] = (double) $data['prevHour'];
                $result['currHour'] = ($data['currHour'] - $data['prevHour']) / $data['prevHour'] * 100;
            } else {
                $result['currHour'] = 0;
            }
        }

        return $result;
    }

    public function get_all($filter = [])
    {
        if(isset($filter['rtu'])) $this->db->where('r.rtu_kode', $filter['rtu']);
        if(isset($filter['witel'])) $this->db->where('r.witel_kode', $filter['witel']);
        if(isset($filter['divre'])) $this->db->where('r.divre_kode', $filter['divre']);
        if(isset($filter['startDate'])) $this->db->where('p.timestamp >=', $filter['startDate']);
        if(isset($filter['endDate'])) $this->db->where('p.timestamp <=', $filter['endDate']);

        $selectFields = ['r.divre_kode', 'r.divre_name', 'r.witel_kode', 'r.witel_name', 'r.rtu_kode', 'r.rtu_name',
            'r.sto_kode', 'r.lokasi', 'p.no_port', 'p.pue_value', 'p.satuan', 'p.timestamp'];

        $this->db
            ->select(implode(', ', $selectFields))
            ->from("$this->tableName AS p")
            ->join("$this->tableRtuMapName AS r", 'r.rtu_kode=p.rtu_kode')
            ->order_by('r.divre_kode')
            ->order_by('r.witel_kode')
            ->order_by('r.rtu_kode')
            ->order_by('p.timestamp', 'DESC');
            
        $data = $this->db->get()->result_array();

        return $data;
    }
}