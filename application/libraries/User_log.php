<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_log
{
    private $table = [
        'name' => 'user_log',
        'field' => [
            'id' => 'id',
            'userId' => 'id_user',
            'username' => 'username',
            'name' => 'name',
            'activity' => 'activity',
            'timestamp' => 'timestamp'
        ]
    ];

    private $id;
    private $userId;
    private $username;
    private $name;
    private $activity;

    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->table['field'] = (object) $this->table['field'];
        $this->table = (object) $this->table;
    }

    public function userId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function username($username)
    {
        $this->username = $username;
        return $this;
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function activity($activity)
    {
        $this->activity = $activity;
        return $this;
    }

    public function log()
    {
        $data = [
            $this->table->field->userId => $this->userId,
            $this->table->field->username => $this->username,
            $this->table->field->name => $this->name,
            $this->table->field->activity => $this->activity,
            $this->table->field->timestamp => date('Y-m-d H:i:s')
        ];

        $this->CI->db->insert($this->table->name, $data);
        $this->id = $this->CI->db->insert_id();
    }
}