<?php
class Rtu_list_model extends CI_Model
{
	private $tableName = 'rtu';

    public function __construct()
    {
        $this->load->database('opnimus');
    }

	private function filter_for_curr_user($currUser)
    {
		if($currUser && $currUser['level'] != 'nasional') {
			$this->db->where($currUser['level'], $currUser['locationId']);
		}
    }
    
    public function get_divre($divreCode = null, $currUser = null)
	{
		$this->filter_for_curr_user($currUser);
		$this->db->where('LENGTH(divre)', 12);

        if(is_null($divreCode)) {
            $query = $this->db
                ->select('divre AS divre_kode, divre_name')
                ->from($this->tableName)
                ->order_by('divre', 'ASC')
                ->group_by('divre')
                ->get();
            return $query->result();
        }

        $query = $this->db
            ->select('divre AS divre_kode, divre_name')
            ->from($this->tableName)
            ->where('divre_kode', $divreCode)
            ->get();
            
        return $query->row();
	}

	public function get_witel($divreCode = null, $witelCode = null, $currUser = null)
    {
		$this->filter_for_curr_user($currUser);
		$this->db->where('LENGTH(divre)', 12);

        if(!is_null($divreCode)) {
            $this->db->where('divre', $divreCode);
        }

        if(is_null($witelCode)) {
            $query = $this->db
                ->select('divre AS divre_kode, divre_name, witel AS witel_kode, witel_name')
                ->from($this->tableName)
                ->order_by('divre', 'ASC')
                ->order_by('witel', 'ASC')
                ->group_by('witel')
                ->get();
            return $query->result();
        }

        $query = $this->db
            ->select('divre AS divre_kode, divre_name, witel AS witel_kode, witel_name')
            ->from($this->tableName)
            ->where('witel', $witelCode)
            ->get();
            
        return $query->row();
    }

    public function get_witel_by_divre($divreCode)
	{
        $query = $this->db
			->select('divre AS divre_code, divre_name, witel AS witel_code, witel_name')
			->from('rtu')
			->where('divre', $divreCode)
			->group_by('witel')
            ->get();

		return $query->result_array();
	}

	public function get_divre_by_witel($witelCode)
	{
        $query = $this->db
			->select('divre AS divre_code, divre_name, witel AS witel_code, witel_name')
			->from('rtu')
			->where('witel', $witelCode)
			->group_by('witel')
            ->get();

		return $query->row();
	}

	public function get_witel_dev($divreCode, $witelKey)
	{
		$this->db
			->select()
			->from('witel')
			->where("kode_divre='$divreCode' AND witel LIKE '%$witelKey%'");
		return $this->db->get()->row();
	}
}