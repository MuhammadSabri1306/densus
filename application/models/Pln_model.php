<?php
class Pln_model extends CI_Model
{
    private $tableName = 'pln';
    private $tableLocationName = 'master_lokasi_gepee';

    public function __construct()
    {
            $this->load->database('densus');
    }

    private function get_filter($filter, $currUser)
    {
        $appliedFilter = [];

        if(is_array($filter) && $filter['witel']) $appliedFilter['loc.witel_kode'] = $filter['witel'];
        if(is_array($filter) && $filter['divre']) $appliedFilter['loc.divre_kode'] = $filter['divre'];
        if(is_array($filter) && $filter['location']) $appliedFilter['pln.id_lokasi_gepee'] = $filter['location'];
        if(is_array($filter) && $filter['id']) $appliedFilter['pln.id'] = $filter['id'];

        if(is_array($currUser) && $currUser['level'] == 'witel') $appliedFilter['loc.witel_kode'] = $filter['witel'];
        elseif(is_array($currUser) && $currUser['level'] == 'divre') $appliedFilter['loc.divre_kode'] = $filter['divre'];

        return $appliedFilter;
    }

    private function apply_filter($filter, $currUser)
    {
        $appliedFilter = $this->get_filter($filter, $currUser);
        $this->db->where($appliedFilter);
    }

    public function get($filter = null, $currUser = null)
    {
        if($filter && isset($filter['location']) || $filter && isset($filter['id'])) {
            $fields = ['pln.id', 'pln.id_lokasi_gepee', 'pln.tgl', 'loc.id_pel_pln', 'loc.nama_pel_pln',
                'loc.tarif_pel_pln', 'loc.daya_pel_pln', 'loc.lokasi_pel_pln', 'loc.alamat_pel_pln', 'loc.gedung',
                'loc.divre_kode', 'loc.divre_name', 'loc.witel_kode', 'loc.witel_name', 'loc.sto_kode', 'loc.sto_name',
                'loc.tipe', 'loc.rtu_kode'];

            $fields = array_merge($fields, ['pln.port_kwh1_low', 'pln.port_kwh1_high', 'pln.port_kwh2_low', 'pln.port_kwh2_low',
                'pln.port_kva', 'pln.port_kw', 'pln.port_power_factor']);
            $this->db
                ->select(implode(', ', $fields))
                ->from("$this->tableName AS pln")
                ->join("$this->tableLocationName AS loc", 'loc.id=pln.id_lokasi_gepee');
            $query = $this->db->get();

            if(isset($filter['id'])) {
                return $query->result_row();
            }
            return $query->result();
        }

        // $fields = array_merge($fields, ['COALESCE(AVG(pln.port_kwh1_low), 0) AS port_kwh1_low', 'COALESCE(AVG(pln.port_kwh1_high), 0) AS port_kwh1_high',
        // 'COALESCE(AVG(pln.port_kwh2_low), 0) AS port_kwh2_low', 'COALESCE(AVG(pln.port_kwh2_high), 0) AS port_kwh2_high', 'COALESCE(AVG(pln.port_kva), 0) AS port_kva',
        // 'COALESCE(AVG(pln.port_kw), 0) AS port_kw', 'COALESCE(AVG(pln.port_power_factor), 0) AS port_power_factor']);
        // $this->db
        //     ->select(implode(', ', $fields))
        //     ->from("$this->tableLocationName AS loc")
        //     ->join("$this->tableName AS pln", 'pln.id_lokasi_gepee=loc.id', 'right')
        //     ->group_by('loc.id');

        $fields = ['loc.*', 'loc.id AS id_lokasi_gepee'];
        $fields2 = ['port_kwh1_low', 'port_kwh1_high', 'port_kwh2_low', 'port_kwh2_high', 'port_kva', 'port_kw', 'port_power_factor'];
        foreach($fields2 as $f) {
            $temp = "(SELECT COALESCE(AVG($f), 0) FROM $this->tableName WHERE id_lokasi_gepee=loc.id) AS $f";
            array_push($fields, $temp);
        }
        
        $queryFilter = $this->get_filter($filter, $currUser);
        $where = [];
        foreach($queryFilter as $key => $val) {
            array_push($where, "$key='$val'");
        }

        $select = implode(', ', $fields);
        $where = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $q = "SELECT $select FROM $this->tableLocationName AS loc
            $where";

        $query = $this->db->query($q);
        return $query->result();
    }
    
    public function save($body, $id = null, $currUser = null)
    {
        if(is_null($id)) {
            $success = $this->db->insert($this->tableName, $body);
        } else {
            if($currUser) $this->apply_filter(null, $currUser);
            $this->db->where('id', $id);

            $success = $this->db->update($this->tableName, $body);
        }
        return $success;
    }

    public function delete($id, $currUser = null)
    {
        if($currUser) $this->apply_filter(null, $currUser);
        $this->db->where('id', $id);
        return $this->db->delete($this->tableName);
    }
}