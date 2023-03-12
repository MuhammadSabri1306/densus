<?php
class Activity_category_model extends CI_Model
{
    private $tableName = 'activity_category';

    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get()
    {
        $query = $this->db->get($this->tableName);
        return $query->result();
    }
}