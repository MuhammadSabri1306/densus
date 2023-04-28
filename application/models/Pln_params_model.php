<?php
class Pln_params_model extends CI_Model
{
    private $tableName = 'pln_params';
    public $currUser;
    private $fillable_fields = [
        'static' => [
            'rtu_kode' => ['string', 'required'],
            'rtu_name' => ['string', 'required'],
            'lokasi' => ['string', 'required'],
            'sto_kode' => ['string', 'required'],
            'alamat' => ['string', 'required'],
            'divre_kode' => ['string', 'required'],
            'divre_name' => ['string', 'required'],
            'witel_kode' => ['string', 'required'],
            'witel_name' => ['string', 'required'],
            'daya' => ['int', 'required']
        ],
        'updatable' => [
            'port_kwh1_low' => ['string', 'required'],
            'port_kwh1_high' => ['string', 'required'],
            'port_kwh2_low' => ['string'],
            'port_kwh2_high' => ['string'],
            'port_kva' => ['string'],
            'port_kw' => ['string'],
            'port_power_factor' => ['string', 'required']
        ]
    ];

    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get_insert_fields()
    {
        return array_merge($this->fillable_fields['static'], $this->fillable_fields['updatable']);
    }

    public function get_update_fields()
    {
        return $this->fillable_fields['updatable'];
    }

    private function get_filter($filter)
    {
        $appliedFilter = [];

        if(is_array($filter) && $filter['witel']) $appliedFilter['witel_kode'] = $filter['witel'];
        if(is_array($filter) && $filter['divre']) $appliedFilter['divre_kode'] = $filter['divre'];
        if(is_array($filter) && $filter['id']) $appliedFilter['id'] = $filter['id'];

        if(is_array($this->currUser) && $this->currUser['level'] == 'witel') $appliedFilter['witel_kode'] = $this->currUser['locationId'];
        elseif(is_array($this->currUser) && $this->currUser['level'] == 'divre') $appliedFilter['divre_kode'] = $this->currUser['locationId'];

        return $appliedFilter;
    }

    private function apply_filter($filter = null)
    {
        $appliedFilter = $this->get_filter($filter);
        $this->db->where($appliedFilter);
    }

    private function is_valid_store($body)
    {
        if(!is_array($this->currUser)) return false;
        if($this->currUser['level'] == 'witel' && $this->currUser['locationId'] != $body['witel_kode']) return false;
        if($this->currUser['level'] == 'divre' && $this->currUser['locationId'] != $body['divre_kode']) return false;
        return true;
    }

    public function get($filter = null)
    {
        $this->apply_filter($filter);
        $this->db
            ->select()
            ->from("$this->tableName")
            ->order_by('divre_kode')
            ->order_by('witel_kode');
        $query = $this->db->get();
        
        $data = isset($filter['id']) ? $query->result_row() : $query->result();
        return $data;
    }
    
    public function save($body, $id = null)
    {
        $success = false;
        if(is_null($id) && $this->is_valid_store($body)) {

            $body['created_at'] = date('Y-m-d H:i:s');
            $body['updated_at'] = $body['created_at'];
            $success = $this->db->insert($this->tableName, $body);

        } else {

            $body['updated_at'] = date('Y-m-d H:i:s');
            $this->apply_filter(['id' => $id]);
            $success = $this->db->update($this->tableName, $body);

        }
        return $success;
    }

    public function delete($id)
    {
        $this->apply_filter(['id' => $id]);
        return $this->db->delete($this->tableName);
    }
}