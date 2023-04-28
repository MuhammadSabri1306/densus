<?php
class Pln_bill_location_model extends CI_Model
{
    private $tableName = 'lokasi_bill';
    public $currUser;
    private $fillable_fields = [
        'id_pel_pln' => ['string', 'required'],
        'nama_pel_pln' => ['string', 'required'],
        'tarif_pel_pln' => ['string', 'required'],
        'daya_pel_pln' => ['int', 'required'],
        'lokasi_pel_pln' => ['string', 'nullable'],
        'alamat_pel_pln' => ['string', 'nullable'],
        'gedung' => ['string', 'nullable'],
        'divre_kode' => ['string', 'required'],
        'divre_name' => ['string', 'required'],
        'witel_kode' => ['string', 'required'],
        'witel_name' => ['string', 'required'],
        'sto_kode' => ['string', 'nullable'],
        'sto_name' => ['string', 'nullable'],
        'tipe' => ['string', 'nullable'],
        'rtu_kode' => ['string', 'nullable']
    ];

    public function __construct()
    {
        $this->load->database('densus');
    }

    public function get_fields()
    {
        return $this->fillable_fields;
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