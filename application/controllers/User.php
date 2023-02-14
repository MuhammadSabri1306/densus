<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class User extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
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

    public function index_delete($id)
	{
        $status = $this->auth_jwt->auth('admin');
        if($status === 200) {

            $this->load->model("user_model");
            $currUser = $this->auth_jwt->get_payload();
            $success = $this->user_model->delete($id, $currUser);
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