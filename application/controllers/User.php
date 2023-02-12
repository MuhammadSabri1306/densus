<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class User extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    }

    public function index_get($id = null)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer');
        if($status === 200) {

            $this->load->model('user_model');
            $data = [
                'success' => true,
                'user' => $this->user_model->get_all($id)
            ];
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

    public function index_post()
    {
        $this->load->library('input_handler');
        $status = $this->auth_jwt->auth('admin');
        if($status === 200) {

            $this->input_handler->set_fields('username', 'password', 'nama', 'role', 'organisasi', 'is_ldap', 'email', 'no_hp', 'telegram_id', 'telegram_name');
            $this->input_handler->set_hashstring_fields('password');
            $this->input_handler->set_required('nama', 'role', 'organisasi', 'is_ldap', 'email', 'no_hp');
            $this->input_handler->set_boolean_fields('is_ldap');

            $input = $this->input_handler->get_body('post');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ;
            }

            $this->input_handler->set_fields('username', 'password', 'nama', 'role', 'organisasi', 'is_ldap', 'email', 'no_hp', 'telegram_id', 'telegram_name');
            $this->input_handler->set_hashstring_fields('password');
            $this->input_handler->set_required('nama', 'role', 'organisasi', 'is_ldap', 'email', 'no_hp');
            $this->input_handler->set_boolean_fields('is_ldap');

            $input = $this->input_handler->get_body('post');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                return $this->response($data, REST_ERR_BAD_REQ);
            }

            if($input['body']['is_ldap'] === false) {
                $this->input_handler->set_fields('username', 'password');
                $this->input_handler->set_required('username', 'password');
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

            if($status === 200) {

                $this->load->model("user_model");
                $success = $this->user_model->save($input['body']);
                $data = [ 'success' => $success ];
                $this->response($data, 200);

            } else {

                $this->response($data, $status);

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

    public function index_put($id)
    {
        $this->load->library('input_handler');
        $status = $this->auth_jwt->auth('admin');
        if($status === 200) {

            $this->input_handler->set_fields('username', 'password', 'nama', 'role', 'organisasi', 'divre_code', 'divre_name', 'witel_code', 'witel_name', 'is_ldap', 'email', 'no_hp', 'telegram_id', 'telegram_name', 'is_active');
            $this->input_handler->set_hashstring_fields('password');

            $input = $this->input_handler->get_body('put');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ;
            }
            
            if($input['body']['is_ldap'] === false) {
                $this->input_handler->set_fields('username', 'password');
                $this->input_handler->set_required('username', 'password');
            }
            if($input['body']['organisasi'] == 'divre') {
                $this->input_handler->set_fields('divre_code', 'divre_name');
                $this->input_handler->set_required('divre_code', 'divre_name');
            }
            if($input['body']['organisasi'] == 'witel') {
                $this->input_handler->set_fields('divre_code', 'divre_name', 'witel_code', 'witel_name');
                $this->input_handler->set_required('divre_code', 'divre_name', 'witel_code', 'witel_name');
            }

            $input = $this->input_handler->get_body('put');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ;
            }

            if($status === 200) {

                $success = $this->user_model->save($input['body'], $id);
                $data = [ 'success' => $success ];
                $this->response($data, 200);

            } else {

                $this->response($data, $status);

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

    public function index_delete($id)
	{
        $status = $this->auth_jwt->auth('admin');
        if($status === 200) {

            $this->load->model("user_model");
            $success = $this->user_model->delete($id);
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