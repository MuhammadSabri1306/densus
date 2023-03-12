<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class User extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    public function index_get($id = null)
    {
        $status = $this->auth_jwt->auth('admin');
        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('user_model');
            $dataUser = $this->user_model->get_all($id, $currUser);

            if($id && !$dataUser) {
                show_404();
            } else {
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('get user list')
                    ->log();
                $data = [ 'success' => true, 'user' => $dataUser ];
                $this->response($data, 200);
            }


        } else {

            switch($status) {
                case REST_ERR_AUTH_CODE: $data = REST_ERR_AUTH_DATA; break;
                case REST_ERR_EXP_CODE: $data = REST_ERR_EXP_DATA; break;
                default: $data = null;
            }
            $this->response($data, $status);

        }
    }

    public function profile_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('user_model');
            $dataUser = $this->user_model->get_own($currUser);

            if($id && !$dataUser) {
                show_404();
                exit();
            } else {
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('get user list')
                    ->log();
                $data = [ 'success' => true, 'user' => $dataUser ];
            }
        }
        
        $this->response($data, $status);
    }

    public function index_post()
    {
        $this->load->library('input_handler');
        $status = $this->auth_jwt->auth('admin');
        if($status === 200) {

            $this->input_handler->set_fields('username', 'password', 'nama', 'role', 'organisasi', 'is_ldap', 'email', 'no_hp', 'telegram_id', 'telegram_name');
            $this->input_handler->set_hashstring_fields('password');
            $this->input_handler->set_required('username', 'nama', 'role', 'organisasi', 'is_ldap', 'email', 'no_hp');
            $this->input_handler->set_boolean_fields('is_ldap');
            $this->input_handler->set_string_fields('no_hp');

            $input = $this->input_handler->get_body('post');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ;
            }
            
            if($input['body']['is_ldap'] === false) {
                $this->input_handler->set_fields('password');
                $this->input_handler->set_required('password');
            }

            if($input['body']['organisasi'] == 'divre') {
                $this->input_handler->set_fields('divre_code', 'divre_name');
                $this->input_handler->set_required('divre_code', 'divre_name');
            }
            if($input['body']['organisasi'] == 'witel') {
                $this->input_handler->set_fields('divre_code', 'divre_name', 'witel_code', 'witel_name');
                $this->input_handler->set_required('divre_code', 'divre_name', 'witel_code', 'witel_name');
            }

            $input = $this->input_handler->get_body('post');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ;
            }

            $currUser = $this->auth_jwt->get_payload();
            $levelValidations = [
                $currUser['level'] == 'nasional',
                ($currUser['level'] == 'divre' && $input['body']['divre_code'] == $currUser['locationId']),
                ($currUser['level'] == 'witre' && $input['body']['witel_code'] == $currUser['locationId'])
            ];
            if(!in_array(true, $levelValidations)) {
                $data = [ 'success' => false ];
                $status = REST_ERR_BAD_REQ;
            }

            if($status === 200) {
                $this->load->model("user_model");
                $hasUser = $this->user_model->get_by_username($input['body']['username']);
                
                if($hasUser) {
                    $data = [
                        'success' => false,
                        'message' => 'Username is exists'
                    ];
                    $status = REST_ERR_BAD_REQ;
                } else {
                    $success = $this->user_model->save($input['body']);
                    if($success) {
                        $this->user_log
                            ->userId($currUser['id'])
                            ->username($currUser['username'])
                            ->name($currUser['name'])
                            ->activity('input new user')
                            ->log();
                    }

                    $data = [ 'success' => $success ];
                    $status = 200;
                }
            }
            $this->response($data, $status);

        } else {

            switch($status) {
                case REST_ERR_AUTH_CODE: $data = REST_ERR_AUTH_DATA; break;
                case REST_ERR_EXP_CODE: $data = REST_ERR_EXP_DATA; break;
                default: $data = null;
            }
            $this->response($data, $status);

        }
    }

    public function index_put($id)
    {
        $this->load->library('input_handler');
        $status = $this->auth_jwt->auth('admin');
        if($status === 200) {

            // $this->input_handler->set_fields('username', 'password', 'nama', 'role', 'organisasi', 'divre_code', 'divre_name', 'witel_code', 'witel_name', 'is_ldap', 'email', 'no_hp', 'telegram_id', 'telegram_name', 'is_active');
            // username dan is_ldap is not updatable
            $this->input_handler->set_fields('password', 'nama', 'role', 'organisasi', 'divre_code', 'divre_name', 'witel_code', 'witel_name', 'email', 'no_hp', 'telegram_id', 'telegram_name', 'is_active');
            $this->input_handler->set_hashstring_fields('password');
            $this->input_handler->set_boolean_fields('is_active');
            $this->input_handler->set_string_fields('no_hp');

            $input = $this->input_handler->get_body('put');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ;
            }

            if(isset($input['body']['organisasi'])) {
                if($input['body']['organisasi'] == 'divre') {
                    // $this->input_handler->set_fields('divre_code', 'divre_name');
                    $this->input_handler->set_required('divre_code', 'divre_name');
                } elseif($input['body']['organisasi'] == 'witel') {
                    // $this->input_handler->set_fields('divre_code', 'divre_name', 'witel_code', 'witel_name');
                    $this->input_handler->set_required('divre_code', 'divre_name', 'witel_code', 'witel_name');
                }
            }
            
            $input = $this->input_handler->get_body('put');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ;
            }

            if($status === 200) {    
                $this->load->model("user_model");
                $currUser = $this->auth_jwt->get_payload();
                $success = $this->user_model->save($input['body'], $id, $currUser);
                $data = [ 'success' => $success ];

                if(!$success) {
                    $status = REST_ERR_BAD_REQ;
                } else {
                    $this->user_log
                        ->userId($currUser['id'])
                        ->username($currUser['username'])
                        ->name($currUser['name'])
                        ->activity('update user')
                        ->log();
                }
            }

            $this->response($data, $status);

        } else {

            switch($status) {
                case REST_ERR_AUTH_CODE: $data = REST_ERR_AUTH_DATA; break;
                case REST_ERR_EXP_CODE: $data = REST_ERR_EXP_DATA; break;
                default: $data = null;
            }
            $this->response($data, $status);

        }
    }

    public function update_put($id)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $fields = [
                'nama' => ['string', 'required'],
                'role' => ['string', 'required'],
                'organisasi' => ['string', 'required'],
                'email' => ['string', 'required'],
                'no_hp' => ['string', 'required'],
                'telegram_id' => ['string', 'nullable'],
                'telegram_username' => ['string', 'nullable']
            ];

            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('put');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $fields = [];

            if($input['body']['organisasi'] != 'nasional') {
                $fields['divre_code'] = ['string', 'required'];
                $fields['divre_name'] = ['string', 'required'];
                $read = true;
            } else {
                $fields['divre_code'] = ['string', 'nullable'];
                $fields['divre_name'] = ['string', 'nullable'];
            }

            if($input['body']['organisasi'] == 'witel') {
                $fields['witel_code'] = ['string', 'required'];
                $fields['witel_name'] = ['string', 'required'];
            } else {
                $fields['witel_code'] = ['string', 'nullable'];
                $fields['witel_name'] = ['string', 'nullable'];
            }

            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('put');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $this->load->model("user_model");
            $currUser = $this->auth_jwt->get_payload();
            $success = $this->user_model->save($input['body'], $id, $currUser);
            $data = [ 'success' => $success ];
        }
        
        if($status === 200 && $data['success']) {
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('update user')
                ->log();
        }

        $this->response($data, $status);
    }

    public function update_active_put($id)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $fields = [ 'is_active' => ['bool', 'required'] ];

            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $this->load->model("user_model");
            $currUser = $this->auth_jwt->get_payload();
            $success = $this->user_model->save($input['body'], $id, $currUser);
            $data = [ 'success' => $success ];
        }
        
        if($status === 200 && $data['success']) {
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('update user:is_active')
                ->log();
        }

        $this->response($data, $status);
    }

    public function index_delete($id)
	{
        $status = $this->auth_jwt->auth('admin');
        if($status === 200) {

            $this->load->model("user_model");
            $currUser = $this->auth_jwt->get_payload();
            $success = $this->user_model->delete($id, $currUser);
            
            if($success) {
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('delete user')
                    ->log();
            }
            
            $data = [ 'success' => $success ];
            $this->response($data, 200);

        } else {

            switch($status) {
                case REST_ERR_AUTH_CODE: $data = REST_ERR_AUTH_DATA; break;
                case REST_ERR_EXP_CODE: $data = REST_ERR_EXP_DATA; break;
                default: $data = null;
            }
            $this->response($data, $status);

        }
	}
}