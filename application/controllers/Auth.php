<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class Auth extends RestController
{
    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        parent::__construct();
        $this->load->library('auth_jwt');
    }

    // FOR DEVElOPMENT
    public function login_get($isLdap, $username, $password)
    {

        // if($isLdap) {}

        $this->load->model('user_model');
        $dataUser = $this->user_model->get_by_username($username);

        if($dataUser && password_verify($password, $dataUser->password)) {

            $this->auth_jwt->id = $dataUser->id;
            $this->auth_jwt->name = $dataUser->nama;
            $this->auth_jwt->role = $dataUser->role;
            $this->auth_jwt->level = $dataUser->organisasi;

            if($dataUser->organisasi == 'divre') {
                $this->auth_jwt->location = $dataUser->divre_name;
                $this->auth_jwt->locationId = $dataUser->divre_code;
            } elseif($dataUser->organisasi == 'witel') {
                $this->auth_jwt->location = $dataUser->witel_name;
                $this->auth_jwt->locationId = $dataUser->witel_code;
            } else {
                $this->auth_jwt->location = null;
                $this->auth_jwt->locationId = null;
            }

            $data = [
                'success' => true,
                'user' => $this->auth_jwt->get_payload()
            ];
            $data['user']['token'] = $this->auth_jwt->create_token();
            $this->response($data, 200);

        } else {

            $data = [
                'success' => false,
                'message' => 'Username or password not matched'
            ];
            $this->response($data, REST_ERR_BAD_REQ);
            
        }
    }

    public function login_post()
    {
        $isLdap = $this->post('is_ldap');
        $username = $this->post('username');
        $password = $this->post('password');

        // if($isLdap) {}

        $this->load->model('user_model');
        $dataUser = $this->user_model->get_by_username($username);

        if($dataUser && password_verify($password, $dataUser->password)) {

            $this->auth_jwt->id = $dataUser->id;
            $this->auth_jwt->name = $dataUser->nama;
            $this->auth_jwt->role = $dataUser->role;
            $this->auth_jwt->level = $dataUser->organisasi;

            if($dataUser->organisasi == 'divre') {
                $this->auth_jwt->location = $dataUser->divre_name;
                $this->auth_jwt->locationId = $dataUser->divre_code;
            } elseif($dataUser->organisasi == 'witel') {
                $this->auth_jwt->location = $dataUser->witel_name;
                $this->auth_jwt->locationId = $dataUser->witel_code;
            } else {
                $this->auth_jwt->location = null;
                $this->auth_jwt->locationId = null;
            }

            $data = [
                'success' => true,
                'user' => $this->auth_jwt->get_payload()
            ];
            $data['user']['token'] = $this->auth_jwt->create_token();
            $this->response($data, 200);

        } else {

            $data = [
                'success' => false,
                'message' => 'Username or password not matched'
            ];
            $this->response($data, REST_ERR_BAD_REQ);
            
        }
    }
}

// Array ( 
//     [cn] => Array ( 
//        [count] => 2 
//        [0] => Valliant Ferlyando 
//        [1] => valliant.ferlyando@telkom.co.id ) 
//     [0] => cn 
//     [mail] => Array ( 
//         [count] => 1 
//         [0] => valliant.ferlyando@telkom.co.id ) 
//     [1] => mail 
//     [mailaddress] => Array ( 
//          [count] => 1 
//          [0] => valliant.ferlyando@telkom.co.id ) 
//     [2] => mailaddress 
//     [location] => Array ( 
//         [count] => 1 
//         [0] => MAKASSAR ) 
//     [3] => location 
//     [sn] => Array ( 
//         [count] => 1 
//         [0] => Valliant Ferlyando ) 
//     [4] => sn 
//     [uid] => Array ( 
//         [count] => 1 
//         [0] => 950022 ) 
//     [5] => uid 
//     [mailserver] => Array ( 
//         [count] => 1 
//         [0] => CN=mail07a,OU=srv,O=Telkom ) 
//     [6] => mailserver 
//     [mailfile] => Array ( 
//         [count] => 1 [0] => mail\950022 ) 
//     [7] => mailfile 
//     [count] => 8 
//     [dn] => CN=Valliant Ferlyando,OU=950022,O=Telkom 
//     )