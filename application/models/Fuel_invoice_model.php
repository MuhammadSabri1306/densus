<?php
class Fuel_invoice_model extends CI_Model
{
    private $tableName = 'fuel_invoice';
    private $tableLocationName = 'lokasi_bill';
    public $currUser;
    private $fillable_fields = [
        'location_foreign' => [
            'id_location' => ['int', 'required']
        ],
        'main' => [
            'harga' => ['int', 'required'],
            'ppn' => ['float', 'required'],
            'pph' => ['float', 'required'],
            'ppbkb' => ['float', 'required'],
            'jumlah' => ['float', 'required']
        ]
    ];

    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get_fields()
    {
        return array_merge($this->fillable_fields['location_foreign'], $this->fillable_fields['main']);
    }

    private function get_filter($filter)
    {
        $appliedFilter = [];

        if(is_array($filter) && $filter['witel']) $appliedFilter['loc.witel_kode'] = $filter['witel'];
        if(is_array($filter) && $filter['divre']) $appliedFilter['loc.divre_kode'] = $filter['divre'];
        if(is_array($filter) && $filter['location']) $appliedFilter['fuel.id_location'] = $filter['location'];
        if(is_array($filter) && $filter['id']) $appliedFilter['fuel.id'] = $filter['id'];

        if(is_array($this->currUser) && $this->currUser['level'] == 'witel') $appliedFilter['loc.witel_kode'] = $this->currUser['locationId'];
        elseif(is_array($this->currUser) && $this->currUser['level'] == 'divre') $appliedFilter['loc.divre_kode'] = $this->currUser['locationId'];

        return $appliedFilter;
    }

    private function apply_filter($filter = null)
    {
        $appliedFilter = $this->get_filter($filter);
        $this->db->where($appliedFilter);
    }

    public function get($filter = null)
    {
        $locFields = ['loc.id_pel_pln', 'loc.nama_pel_pln', 'loc.tarif_pel_pln', 'loc.daya_pel_pln',
            'loc.lokasi_pel_pln', 'loc.alamat_pel_pln', 'loc.gedung', 'loc.divre_kode', 'loc.divre_name',
            'loc.witel_kode', 'loc.witel_name', 'loc.sto_kode', 'loc.sto_name', 'loc.tipe', 'loc.rtu_kode'];

        $this->apply_filter($filter);
        $this->db
            ->select('fuel.*, '.implode(', ', $locFields))
            ->from("$this->tableName AS fuel")
            ->join("$this->tableLocationName AS loc", 'loc.id=fuel.id_location')
            ->order_by('loc.divre_kode')
            ->order_by('loc.witel_kode')
            ->order_by('fuel.created_at', 'desc');
        $query = $this->db->get();
        
        $data = isset($filter['id']) ? $query->result_row() : $query->result();
        return $data;
    }

    public function get_avg($filter = null)
    {
        $fields = array_map(function($f) {
            return "(SELECT COALESCE(AVG($f), 0) FROM $this->tableName WHERE id_location=loc.id) AS $f";
        }, array_keys($this->fillable_fields['main']));

        $locFields = ['loc.id AS id_location', 'loc.id_pel_pln', 'loc.nama_pel_pln', 'loc.tarif_pel_pln', 'loc.daya_pel_pln',
            'loc.lokasi_pel_pln', 'loc.alamat_pel_pln', 'loc.gedung', 'loc.divre_kode', 'loc.divre_name',
            'loc.witel_kode', 'loc.witel_name', 'loc.sto_kode', 'loc.sto_name', 'loc.tipe', 'loc.rtu_kode'];
        $selectQuery = 'SELECT '.implode(', ', $locFields).', '.implode(', ', $fields);

        $qArray = [
            $selectQuery,
            "FROM $this->tableLocationName AS loc",
            "LEFT JOIN $this->tableName AS fuel ON fuel.id_location=loc.id"
        ];
        
        $filter = $this->get_filter($filter);
        $queryFilter = array_map(function($val, $key) {
            return "$key='$val'";
        }, $filter, array_keys($filter));

        if(count($queryFilter) > 0) {
            $whereQuery = 'WHERE ' . implode(' AND ', $queryFilter);
            array_push($qArray, $whereQuery);
        }

        array_push($qArray, "ORDER BY loc.divre_kode, loc.witel_kode");
        
        $query = implode(' ', $qArray);
        $data = $this->db
            ->query($query)
            ->result();
        return $data;
    }
    
    public function save($body, $id = null)
    {
        $success = false;
        if(is_null($id)) {

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