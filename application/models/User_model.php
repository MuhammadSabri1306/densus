<?php
class User_model extends CI_Model
{
    public function __construct()
    {
            $this->load->database('densus');
    }

    public function get_all($id = null)
    {
        $select = 'id, username, nama, organisasi, role, no_hp, email, is_ldap, telegram_id, telegram_username, witel_code, witel_name, divre_code, divre_name, is_active';
        if(is_null($id)) {
            $query = $this->db
                ->select($select)
                ->from('user')
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
            ->from('user')
            ->where('id', $id)
            ->get();
            
        $result = $query->row();
        $result->is_ldap = $result->is_ldap == 1;
        $result->is_active = $result->is_active == 1;
        return $result;
    }

    public function save($body, $id = null)
    {
        if(is_null($id)) {
            $success = $this->db->insert('user', $body);
        } else {
            $success = $this->db
                ->where('id', $id)
                ->update('user', $body);
        }
        return $success;
    }

    public function delete($id)
    {
        return $this->db->delete('user', [ 'id' => $id ]);
    }

    public function get_by_username($username)
    {
        $query = $this->db
            ->select('*')
            ->from('user')
            ->where('username', $username)
            ->get();
        return $query->row();
    }
}