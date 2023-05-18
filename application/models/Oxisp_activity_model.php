<?php
class Oxisp_activity_model extends CI_Model
{
    protected $tableName = 'oxisp_activity';
    protected $tableLocationName = 'master_sto_densus';

    public $currUser;

    protected $fillable_fields = [
        'location_foreign' => [
            'id_sto' => ['int', 'required']
        ],
        'main' => [
            'title' => ['string', 'required'],
            'descr' => ['string', 'required'],
            'descr_before' => ['string', 'required'],
            'descr_after' => ['string', 'required'],
            'evidence' => ['string', 'required']
            // 'status' => ['string', 'required'],
            // 'reject_descr' => ['string', 'required']
        ]
    ];

    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get_insertable_fields()
    {
        $locFields = $this->fillable_fields['location_foreign'];
        $mainFields = $this->fillable_fields['main'];
        return array_merge($locFields, $mainFields);
    }

    public function get_updatable_fields()
    {
        return $this->fillable_fields['main'];
    }

    protected function mysql_updatable_time($fieldName)
    {
        $updatableTime = EnvPattern::getUpdatableOxispTime();
        return "UNIX_TIMESTAMP($fieldName)>=$updatableTime->start AND UNIX_TIMESTAMP($fieldName)<=$updatableTime->end";
    }

    protected function is_date_updatable($year, $month, $date)
    {
        $datetime = new DateTime("$year-$month-$date");
        $datetime->setTime(0, 0, 0);
        $updatableTime = EnvPattern::getUpdatableOxispTime();
        return $datetime->getTimestamp() >= $updatableTime->start && $datetime->getTimestamp() <= $updatableTime->end;
    }

    protected function get_loc_filter($filter, $prefix = null)
    {
        $filterKeys = [
            'witel' => 'witel_kode',
            'divre' => 'divre_kode',
            'id' => 'id_sto'
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

    protected function get_filter($filter, $prefix = null)
    {
        $avlbFilter = ['id', 'status'];
        $appliedFilter = [];
        foreach($filter as $key => $val) {
            if(in_array($key, $avlbFilter)) {
                $filterKey = $prefix ? "$prefix.$key" : $key;
                $appliedFilter[$filterKey] = $val;
            }
        }
        return $appliedFilter;
    }

    public function get_list($filter)
    {
        $this->use_module('get_list', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function get_performance($filter)
    {
        $this->use_module('get_performance', [ 'filter' => $filter ]);
        return $this->result;
    }

    public function delete_by_location($idLocation)
    {
        $this->use_module('delete_by_location', [ 'idLocation' => $idLocation ]);
        return $this->result;
    }

    public function delete($id)
    {
        $this->use_module('delete', [ 'id' => $id ]);
        return $this->result;
    }
    
    public function save($body, $id = null)
    {
        $this->use_module('save', [
            'body' => $body,
            'id' => $id
        ]);
        return $this->result;
    }
}