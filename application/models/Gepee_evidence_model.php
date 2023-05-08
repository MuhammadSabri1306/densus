<?php

class Gepee_evidence_model extends CI_Model
{
    private $tableName = 'gepee_evidence';
    private $tableCategoryName = 'gepee_evidence_category';
    private $tableLocationName = 'gepee_evidence_location';

    public $currUser;
    public $categories = [
        'A' => 10,
        'B' => 40,
        'C' => 50
    ];

    private $fillable_fields = [
        'category_foreign' => [
            'id_category' => ['int', 'required']
        ],
        'location_foreign' => [
            'id_location' => ['int', 'required']
        ],
        'main' => [
            'deskripsi' => ['string', 'required'],
            'file' => ['string', 'required']
        ]
    ];

    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get_insertable_fields()
    {
        $fields = $this->fillable_fields;
        return array_merge($fields['category_foreign'], $fields['location_foreign'], $fields['main']);
    }

    public function get_updatable_fields()
    {
        $fields = $this->fillable_fields['main'];
        unset($fields['file']);
        return $fields;
    }

    private function get_filter($filter, $key = 'main')
    {
        $appliedFilter = [];
        if(is_array($filter)) {
            if($key == 'location' && isset($filter['witel'])) $appliedFilter['loc.witel_kode'] = $filter['witel'];
            if($key == 'location' && isset($filter['divre'])) $appliedFilter['loc.divre_kode'] = $filter['divre'];
            if($key == 'main' && isset($filter['id'])) $appliedFilter['ev.id'] = $filter['id'];
        }

        if(is_array($this->currUser)) {
            $locationId = $this->currUser['locationId'];
            if($this->currUser['level'] == 'witel') $appliedFilter['loc.witel_kode'] = $locationId;
            elseif($this->currUser['level'] == 'divre') $appliedFilter['loc.divre_kode'] = $locationId;
        }

        return $appliedFilter;
    }

    private function apply_filter($filter, $exclude = [])
    {
        $appliedFilter = $this->get_filter($filter, $exclude);
        $this->db->where($appliedFilter);
    }

    private function get_location_filter($filter, $prefix = null)
    {
        $filterKeys = [
            'witel' => 'witel_kode',
            'divre' => 'divre_kode',
            'id' => 'id_location'
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

    private function get_datetime_filter($filter, $prefix = null)
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

    // public function get_location_list($filter)
    // {
    //     $locFilter = $this->get_location_filter($filter);
        
    //     $locfields = ['divre_kode', 'divre_name'];
    //     if(isset($locFilter['divre_kode'])) {
    //         $locfields = ['id_location', 'divre_kode', 'divre_name', 'witel_kode', 'witel_name'];
    //     }

    //     $locationList = $this->db
    //         ->select(implode(', ', $locfields))
    //         ->from($this->tableLocationName)
    //         ->where($locFilter)
    //         ->get()
    //         ->result_array();
        
    //     $categoryList = $this->db
    //         ->select('id_category, code, category, sub_category')
    //         ->from($this->tableCategoryName)
    //         ->get()
    //         ->result_array();
        
    //     $result = [];
    //     if(is_array($locationList)) {
    //         foreach($locationList as $locItem) {

    //             $temp = $locItem;

    //             if(is_array($categoryList)) {
    //                 foreach($categoryList AS $catItem) {

    //                     if(!isset($temp[$catItem['code']])) {
    //                         $temp[$catItem['code']]['category_name'] = $catItem['category'];
    //                         $temp[$catItem['code']]['count'] = 0;
    //                         $temp[$catItem['code']]['total'] = 0;
    //                     }

    //                     $itemCount = $this->db
    //                         ->select()
    //                         ->from($this->tableName)
    //                         ->where('id_location', $locItem['id_location'])
    //                         ->where('id_category', $catItem['id_category'])
    //                         ->count_all_results();
                        
    //                     if($itemCount > 0) $temp[$catItem['code']]['count']++;
    //                     $temp[$catItem['code']]['total']++;

    //                 }
    //             }

    //             array_push($result, $temp);

    //         }
    //     }

    //     return $result;
    // }

    public function get_location_list($filter)
    {
        $locFilter = $this->get_location_filter($filter, 'loc');
        $dateFilter = $this->get_datetime_filter($filter, 'evd');
        
        if(isset($locFilter['loc.divre_kode'])) {
            $locfields = ['loc.*'];
        } else {
            $locfields = ['loc.divre_kode', 'loc.divre_name'];
            $this->db->group_by('divre_kode');
        }

        $locationList = $this->db
            ->select(implode(', ', $locfields))
            ->from("$this->tableLocationName AS loc")
            ->where($locFilter)
            ->get()
            ->result_array();
        
        $categoryList = $this->db
            ->select('id_category, code, category, sub_category')
            ->from($this->tableCategoryName)
            ->get()
            ->result_array();
        
        $result = [];
        if(is_array($locationList)) {
            foreach($locationList as $locItem) {

                $temp = $locItem;

                if(is_array($categoryList)) {
                    foreach($categoryList AS $catItem) {

                        if(!isset($temp[$catItem['code']])) {
                            $temp[$catItem['code']]['category_name'] = $catItem['category'];
                            $temp[$catItem['code']]['id_category'] = $catItem['id_category'];
                            $temp[$catItem['code']]['count'] = 0;
                            $temp[$catItem['code']]['total'] = 0;
                        }

                        if(isset($locFilter['loc.divre_kode'])) {
                            $this->db
                                ->select('*')
                                ->from("$this->tableName AS evd")
                                ->where('evd.id_location', $locItem['id_location']);
                        } else {
                            $this->db
                                ->select('evd.*')
                                ->from("$this->tableName AS evd")
                                ->join("$this->tableLocationName AS loc", 'loc.id_location=evd.id_location')
                                ->where('loc.divre_kode', $locItem['divre_kode']);
                        }

                        $itemCount = $this->db
                            ->where('id_category', $catItem['id_category'])
                            ->where($dateFilter)
                            ->count_all_results();
                        
                        if($itemCount > 0) {
                            $temp[$catItem['code']]['count']++;
                        }
                        $temp[$catItem['code']]['total']++;

                    }
                }

                $temp['scores'] = 0;
                $countAll = 0;
                $totalAll = 0;
                foreach($this->categories as $code => $point) {    
                    $countAll += $temp[$code]['count'];
                    // $countAll += $temp[$code]['count'] * 100;
                    $totalAll += $temp[$code]['total'];
                    // $totalAll = $temp[$code]['total'] * $point;
                }

                $temp['scores'] = $totalAll > 0 ? $countAll / $totalAll * 100 : 0;
                $temp['test'] = [$countAll, $totalAll];
                
                array_push($result, $temp);

            }
        }

        return $result;
    }

    public function get_location_detail($filter)
    {
        $locFilter = $this->get_location_filter($filter, 'loc');
        $dateFilter = $this->get_datetime_filter($filter, 'evd');
        
        $filter = [];
        foreach($locFilter as $key => $val) {
            array_push($filter, "$key='$val'");
        }
        foreach($dateFilter as $key => $val) {
            array_push($filter, "$key'$val'");
        }

        $whereQuery = '';
        if(count($filter) > 0) {
            $whereQuery = ' WHERE evd.id_category=cat.id_category AND '.implode(' AND ', $filter);
        }

        $query = "SELECT cat.code, cat.category, cat.use_target, (SELECT COUNT(*) FROM $this->tableName AS evd
            LEFT JOIN $this->tableLocationName AS loc ON loc.id_location=evd.id_location
            $whereQuery) AS count FROM $this->tableCategoryName AS cat";
        
        $data = $this->db->query($query)->result_array();
        $result = [];

        foreach($data as $item) {
            
            if(!isset($result[$item['code']])) {
                $result[$item['code']] = [ 'category' => $item['category'] ];
                $result[$item['code']]['checkedCount'] = 0;
                $result[$item['code']]['targetCount'] = 0;
            }
            
            if((int) $item['count'] > 0) $result[$item['code']]['checkedCount']++;
            $result[$item['code']]['targetCount']++;
            // if(boolval($item['use_target'])) {
            // }

        }
        
        return $result;
    }

    public function get_request_level($filter)
    {
        $locFilter = $this->get_location_filter($filter);
        $locFields = []; $level = 'nasional';
        
        if(isset($locFilter['witel_kode']) || isset($locFilter['id_location'])) {
            $locFields = ['id_location', 'divre_kode', 'divre_name', 'witel_kode', 'witel_name'];
            $level = 'witel';
        } elseif(isset($locFilter['divre_kode'])) {
            $locFields = ['divre_kode', 'divre_name'];
            $level = 'divre';
        }
        if(count($locFields) > 0) {
            
            $this->db
                ->select(implode(',', $locFields))
                ->from($this->tableLocationName)
                ->where($locFilter);

            $result = $this->db->get()->row_array();
            $result['level'] = $level;

        } else {
            $result = [ 'level' => $level ];
        }

        return $result;
    }

    public function get($filter = null)
    {
        // $locFields = ['loc.id_pel_pln', 'loc.nama_pel_pln', 'loc.tarif_pel_pln', 'loc.daya_pel_pln',
        //     'loc.lokasi_pel_pln', 'loc.alamat_pel_pln', 'loc.gedung', 'loc.divre_kode', 'loc.divre_name',
        //     'loc.witel_kode', 'loc.witel_name', 'loc.sto_kode', 'loc.sto_name', 'loc.tipe', 'loc.rtu_kode'];

        $this->apply_filter($filter);
        $this->db
            ->select('*')
            ->from("$this->tableName AS ev")
            ->join("$this->tableLocationName AS loc", 'loc.id_location=ev.id_location')
            ->order_by('loc.divre_kode')
            ->order_by('loc.witel_kode')
            ->order_by('ev.created_at', 'desc');
        $query = $this->db->get();
        
        $data = isset($filter['id']) ? $query->result_row() : $query->result();
        return $data;
    }

    public function get_category($code)
    {
        if($code) {
            $this->db->where('code', $code);
        }

        $this->db
            ->select()
            ->from($this->tableCategoryName);
        
        return $this->db->get()->result_array();
    }

    public function get_category_by_id($idCategory)
    {
        $this->db
            ->select()
            ->from($this->tableCategoryName)
            ->where('id_category', $idCategory);
        return $this->db->get()->row_array();
    }

    public function get_location_score($filter)
    {
        $locFilter = $this->get_location_filter($filter, 'loc');
        $dateFilter = $this->get_datetime_filter($filter, 'evd');
        $filter = array_merge($locFilter, $dateFilter, [ 'cat.use_target' => 1 ]);

        $this->db
            ->select('evd.*')
            ->from("$this->tableName AS evd")
            ->join("$this->tableCategoryName AS cat", 'cat.id_category=evd.id_category')
            ->join("$this->tableLocationName AS loc", 'loc.id_location=evd.id_location')
            ->where($filter)
            ->group_by('evd.id_category');
        $checkedCount = $this->db->count_all_results();
        
        $this->db
            ->select()
            ->from($this->tableCategoryName)
            ->where('use_target', 1);
        $targetCount = $this->db->count_all_results();
        // dd($checkedCount, $targetCount);
        return $checkedCount / $targetCount * 100;
    }

    public function get_category_data($filter)
    {
        $locFilter = $this->get_location_filter($filter, 'loc');
        $dateFilter = $this->get_datetime_filter($filter, 'evd');
        
        $filter = [];
        foreach($locFilter as $key => $val) {
            array_push($filter, "$key='$val'");
        }
        foreach($dateFilter as $key => $val) {
            array_push($filter, "$key'$val'");
        }

        $whereQuery = '';
        if(count($filter) > 0) {
            $whereQuery = ' WHERE evd.id_category=cat.id_category AND '.implode(' AND ', $filter);
        }

        $query = "SELECT cat.id_category, cat.code, cat.category, cat.sub_category, cat.use_target,
            (SELECT COUNT(*) FROM $this->tableName AS evd JOIN $this->tableLocationName AS loc ON loc.id_location=evd.id_location
            $whereQuery) AS data_count FROM $this->tableCategoryName AS cat";
        
        $data = $this->db->query($query)->result_array();
        $result = [];

        foreach($data as $item) {
            
            if(!isset($result[$item['code']])) {
                $result[$item['code']] = [];
            }

            $temp = $item;
            if(isset($temp['use_target'])) {
                $temp['use_target'] = boolval($temp['use_target']);
            }
            if(isset($temp['data_count'])) {
                $temp['data_count'] = (int) $temp['data_count'];
            }
            array_push($result[$item['code']], $temp);

        }
        
        return $result;
    }

    public function get_evidence_list($idCategory, $filter)
    {
        $locFilter = $this->get_location_filter($filter, 'loc');
        $dateFilter = $this->get_datetime_filter($filter, 'evd');

        $fileBasicPath = base_url(UPLOAD_GEPEE_EVIDENCE_PATH);
        $selectedFields = [
            'evd.*', 'cat.*', 'loc.*',
            "IF(evd.file>'', CONCAT('$fileBasicPath', evd.file), '#') AS file_url"
        ];

        $this->db
            ->select(implode(', ', $selectedFields))
            ->from("$this->tableName AS evd")
            ->join("$this->tableCategoryName AS cat", 'cat.id_category=evd.id_category')
            ->join("$this->tableLocationName AS loc", 'loc.id_location=evd.id_location')
            ->where($locFilter)
            ->where($dateFilter)
            ->where('evd.id_category', $idCategory);
        
        $data = $this->db->get()->result_array();
        return $data;
    }

    public function get_evidence_by_id($id)
    {
        $locFilter = $this->get_location_filter([], 'loc');
        $fields = [
            'evd.*',
            "IF(e.file>'', CONCAT('".base_url(UPLOAD_GEPEE_EVIDENCE_PATH)."', e.file), '#') AS file_url"
        ];

        $this->db
            ->select(implode(', ', $fields))
            ->from("$this->tableName AS evd")
            ->join("$this->tableLocationName AS loc", 'loc.id_location=evd.id_location')
            ->where($locFilter)
            ->where('evd.id', $id);
        
        $data = $this->db->get()->row_array();
        return $data;
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
        $evd = $this->db->get()->row_array();

        $isFileDeleted = false;
        if(isset($evd['file'])) {
            $filePath = FCPATH . UPLOAD_GEPEE_EVIDENCE_PATH . '/' . $evd['file'];
            $isFileDeleted = unlink($filePath);
        }

        if($isFileDeleted) {
            $this->db->where('id', $id);
            return $this->db->delete($this->tableName);
        }
        return false;
    }
}