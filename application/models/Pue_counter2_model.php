<?php
class Pue_Counter2_model extends CI_Model {

    protected $tableName = 'pue_counter';
    protected $tableRtuMapName = 'rtu_map';

    public function __construct()
    {
        $this->load->database('densus');
    }

    protected function get_filter($filter, $exclude = [])
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

    protected function apply_filter($filter, $exclude = [])
    {
        $appliedFilter = $this->get_filter($filter, $exclude);
        $this->db->where($appliedFilter);
    }

    public function get_data_level_by_filter($filter)
    {
        $this->use_module('get_data_level_by_filter', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_curr_year($rtuCode)
    {
        $this->use_module('get_curr_year', [ 'rtuCode' => $rtuCode ]);
        return $this->result;
    }

    public function get_zone_avg_on_curr_year($zone)
    {
        $this->use_module('get_zone_avg_on_curr_year', [ 'zone' => $zone ]);
        return $this->result;
    }

    public function get_zone_avg_on_curr_year_v2($zone)
    {
        $this->use_module('get_zone_avg_on_curr_year_v2', [ 'zone' => $zone ]);
        return $this->result;
    }

    public function get_curr_year_avg_on_sto($zone)
    {
        $this->use_module('get_curr_year_avg_on_sto', [ 'zone' => $zone ]);
        return $this->result;
    }

    public function get_pue_monitoring($zone)
    {
        $this->use_module('get_pue_monitoring', [ 'zone' => $zone ]);
        return $this->result;
    }

    public function get_pue_monitoring_excel($zone)
    {
        $this->use_module('get_pue_monitoring_excel', [ 'zone' => $zone ]);
        return $this->result;
    }

    public function get_rtu_pue_hourly($zone, $rtuCode)
    {
        $this->use_module('get_rtu_pue_hourly', [ 'zone' => $zone, 'rtuCode' => $rtuCode ]);
        return $this->result;
    }

    public function get_rtu_pue_hourly_excel($zone, $rtuCode)
    {
        $this->use_module('get_rtu_pue_hourly_excel', [ 'zone' => $zone, 'rtuCode' => $rtuCode ]);
        return $this->result;
    }

    public function get_excel_data($zone)
    {
        $this->use_module('get_excel_data', [ 'zone' => $zone ]);
        return $this->result;
    }

    public function get_latest_value($rtuCode)
    {
        $this->use_module('get_latest_value', [ 'rtuCode' => $rtuCode ]);
        return $this->result;
    }

    public function get_latest_avg_value_on_zone($zone)
    {
        $this->use_module('get_latest_avg_value_on_zone', [ 'zone' => $zone ]);
        return $this->result;
    }

    public function get_max_value($zone)
    {
        $this->use_module('get_max_value', [ 'zone' => $zone ]);
        return $this->result;
    }

    public function get_avg_value($zone)
    {
        $this->use_module('get_avg_value', [ 'zone' => $zone ]);
        return $this->result;
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
        $this->use_module('get_performance_value', [ 'zone' => $zone ]);
        return $this->result;
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