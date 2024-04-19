<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class Auth extends RestController
{
    function __construct()
    {
        // header('Access-Control-Allow-Origin: *');
        // header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }
    
    public function login_post()
    {
        $this->load->library('input_handler');
        $this->input_handler->set_fields('username', 'password', 'is_ldap');
        $this->input_handler->set_required('username', 'password', 'is_ldap');
        $this->input_handler->set_boolean_fields('is_ldap');

        $input = $this->input_handler->get_body('post');
        $this->load->model('user_model');
        $dataUser = null;
        $status = 200;
        
        if(!$input['valid']) {
            
            $data = [ 'success' => false, 'message' => $input['msg'] ];
            $status = REST_ERR_BAD_REQ;
            
        } elseif($input['body']['is_ldap']) {
            
            $ldapurl = EnvPattern::$api_ldap . 'login.php?username=' . $input['body']['username'] . '&password=' . $input['body']['password'];
            $content = file_get_contents($ldapurl);
            $authdata = json_decode($content, TRUE);
            if ($authdata['status'] === "success") {
                $dataUser = $this->user_model->get_by_username($authdata['nik']);
            } else {
                $status = REST_ERR_BAD_REQ;
            }

        } else {
            
            $dataUser = $this->user_model->get_by_username($input['body']['username']);
            if($dataUser && !password_verify($input['body']['password'], $dataUser->password)) {
                $status = REST_ERR_BAD_REQ;
            }

        }

        if($dataUser && $status === 200) {
            
            $this->user_log
                ->userId($dataUser->id)
                ->username($dataUser->username)
                ->name($dataUser->nama)
                ->activity('login')
                ->log();
            
            $this->auth_jwt->id = $dataUser->id;
            $this->auth_jwt->username = $dataUser->username;
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
                $this->auth_jwt->location = 'nasional';
                $this->auth_jwt->locationId = null;
            }
            $this->auth_jwt->divreCode = $dataUser->divre_code;
            $this->auth_jwt->divreName = $dataUser->divre_name;
            $this->auth_jwt->witelCode = $dataUser->witel_code;
            $this->auth_jwt->witelName = $dataUser->witel_name;
            
            $data = [
                'success' => true,
                'user' => $this->auth_jwt->get_payload()
            ];
            
            $data['user']['token'] = $this->auth_jwt->create_token();
            $this->response($data, 200);

        } else {

            if(!isset($data)) {
                $data = [
                    'success' => false,
                    'message' => 'Username or password not matched'
                ];
            }
            $this->response($data, REST_ERR_BAD_REQ);
            
        }
    }

    public function logout_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('logout')
                ->log();
        }
        $this->response([ 'success' => true ], 200);
    }

    function update_password_put()
    {
		$status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_handler');
			$this->input_handler->set_fields('old_password', 'new_password');
            $this->input_handler->set_required('old_password', 'new_password');
            $this->input_handler->set_hashstring_fields('new_password');

			$input = $this->input_handler->get_body('put');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model("user_model");
            
            $dataUser = $this->user_model->get_own($currUser);
            if(!$dataUser || !password_verify($input['body']['old_password'], $dataUser->password)) {
                $status = REST_ERR_UNMATCH_OLD_PASS_STATUS;
                $data = REST_ERR_UNMATCH_OLD_PASS_DATA;
            }
        }
        
        if($status === 200) {
            $success = $this->user_model->update_pass($currUser['id'], $input['body']['new_password']);
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('update own password')
                ->log();
        }

        if($status === 200) {
            $this->auth_jwt->id = $dataUser->id;
            $this->auth_jwt->username = $dataUser->username;
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
                $this->auth_jwt->location = 'nasional';
                $this->auth_jwt->locationId = null;
            }
            
            $data = [
                'success' => true,
                'user' => $this->auth_jwt->get_payload()
            ];
            
            $data['user']['token'] = $this->auth_jwt->create_token();
        }

        $this->response($data, $status);
    }
}