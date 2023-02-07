<?php
class Rtu_list_model extends CI_Model {

    public function __construct()
    {
        $this->load->database('opnimus');
    }
    
    public function get_divre()
	{
		$query = $this->db
			->select('divre AS divre_code, divre_name')
			->from('rtu')
            ->where('LENGTH(divre)', 12)
			->group_by('divre')
            ->get();

		return $query->result_array();
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
}