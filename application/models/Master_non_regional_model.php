<?php
class Master_non_regional_model extends CI_Model
{
    protected $tableRegionalName = 'master_non_regional';

    public function __construct()
    {
        $this->load->database('densus');
    }

    public function find($regionalId)
    {
        $this->db
            ->select('*')
            ->from($this->tableRegionalName)
            ->where('regional_id', $regionalId);
        return $this->get()->row_array();
    }
}