<?php
class User_model extends CI_Model
{
    protected $tableName = 'user';
    protected $tableLoginFailedName = 'user_login_failed';

    public function __construct()
    {
            $this->load->database('densus');
    }

    private function filter_for_curr_user($currUser)
    {
        if($currUser && $currUser['level'] == 'witel') {
            $this->db->where('organisasi', 'witel');
            $this->db->where('witel_code', $currUser['locationId']);
            $this->db->where('id !=', $currUser['id']);
        } elseif($currUser && $currUser['level'] == 'divre') {
            $this->db->where('organisasi !=', 'nasional');
            $this->db->where('divre_code', $currUser['locationId']);
            $this->db->where('id !=', $currUser['id']);
        }
    }

    public function get_all($id = null, $currUser = null)
    {
        $select = 'id, username, nama, organisasi, role, no_hp, email, is_ldap, telegram_id, telegram_username, witel_code, witel_name, divre_code, divre_name, is_active';
        $this->filter_for_curr_user($currUser);

        if(is_null($id)) {
            $query = $this->db
                ->select($select)
                ->from($this->tableName)
                ->order_by('id', 'DESC')
                ->get();
            $result = $query->result();

            return array_map(function($item) {
                $temp = $item;
                $temp->is_ldap = $temp->is_ldap == 1;
                $temp->is_active = $temp->is_active == 1;
                return $temp;
            }, $result);
        }

        $query = $this->db
            ->select($select)
            ->from($this->tableName)
            ->where('id', $id)
            ->get();
            
        $result = $query->row();
        if($result) {
            $result->is_ldap = $result->is_ldap == 1;
            $result->is_active = $result->is_active == 1;
        }
        return $result;
    }

    public function save($body, $id = null, $currUser = null)
    {
        if(is_null($id)) {
            $success = $this->db->insert($this->tableName, $body);
        } else {
            $this->filter_for_curr_user($currUser);
            $this->db->where('id', $id);

            $success = $this->db->update($this->tableName, $body);
        }
        return $success;
    }

    public function delete($id, $currUser = null)
    {
        $this->filter_for_curr_user($currUser);
        $this->db->where('id', $id);
        return $this->db->delete($this->tableName);
    }

    public function get_by_username($username)
    {
        $query = $this->db
            ->select('*')
            ->from($this->tableName)
            ->where('username', $username)
            ->get();
        return $query->row();
    }

    public function get_own($currUser)
    {
        $query = $this->db
            ->select('id, username, nama, organisasi, role, no_hp, email, is_ldap, telegram_id, telegram_username, witel_code, witel_name, divre_code, divre_name, is_active')
            ->from($this->tableName)
            ->where('id', $currUser['id'])
            ->get();
            
        $result = $query->row();
        if($result) {
            $result->is_ldap = $result->is_ldap == 1;
            $result->is_active = $result->is_active == 1;
        }
        return $result;
    }

    public function update_pass($id, $password)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, [ 'password' => $password ]);
    }
}