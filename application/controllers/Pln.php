<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Pln extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    /* ------------------------------ PLN Bill Location ------------------------------ */
    public function bill_location_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $filter = [
                'witel' => $this->input->get('witel'),
                'divre' => $this->input->get('divre'),
                'id' => $this->input->get('id')
            ];
            
            $this->load->model('pln_bill_location_model');
            $this->pln_bill_location_model->currUser = $currUser;
            $data = [
                'pln_bill_location' => $this->pln_bill_location_model->get($filter),
                'success' => true
            ];

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get pln bill location')
                ->log();

        }

        $this->response($data, $status);
    }

    public function bill_location_post()
    {
		$status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pln_bill_location_model');
            $this->pln_bill_location_model->currUser = $currUser;
            
            $this->load->library('input_custom');
            $fields = $this->pln_bill_location_model->get_insert_fields();
            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

		if($status === 200) {

            $success = $this->pln_bill_location_model->save($input['body']);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('input new pln bill location')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }

        $this->response($data, $status);
    }

    public function bill_location_put($id)
    {
		$status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pln_bill_location_model');
            $this->pln_bill_location_model->currUser = $currUser;
            
            $this->load->library('input_custom');
            $fields = $this->pln_bill_location_model->get_update_fields();
            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

		if($status === 200) {

            $success = $this->pln_bill_location_model->save($input['body'], $id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('update pln bill location')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }

        $this->response($data, $status);
    }

    public function bill_location_delete($id)
	{
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pln_bill_location_model');
            $this->pln_bill_location_model->currUser = $currUser;
            
            $success = $this->pln_bill_location_model->delete($id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('delete pln bill location')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }

        $this->response($data, 200);
	}

    /* ------------------------------ PLN Bill ------------------------------ */
    public function bill_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $filter = [
                'witel' => $this->input->get('witel'),
                'divre' => $this->input->get('divre'),
                'locationId' => $this->input->get('locationId'),
                'id' => $this->input->get('id')
            ];
            
            $this->load->model('pln_bill_model');
            $this->pln_bill_model->currUser = $currUser;
            $data = ['success' => true ];
            if($filter['locationId'] || $filter['id']) {
                $data['pln_bill'] = $this->pln_bill_model->get($filter);
            } else {
                $data['pln_bill'] = $this->pln_bill_model->get_avg($filter);
            }

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get pln bill')
                ->log();

        }

        $this->response($data, $status);
    }

    public function bill_post()
    {
		$status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pln_bill_model');
            $this->pln_bill_model->currUser = $currUser;
            
            $this->load->library('input_custom');
            $fields = $this->pln_bill_model->get_insert_fields();
            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

		if($status === 200) {

            $success = $this->pln_bill_model->save($input['body']);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('input new pln bill')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }

        $this->response($data, $status);
    }

    public function bill_put($id)
    {
		$status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pln_bill_model');
            $this->pln_bill_model->currUser = $currUser;
            
            $this->load->library('input_custom');
            $fields = $this->pln_bill_model->get_update_fields();
            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

		if($status === 200) {

            $success = $this->pln_bill_model->save($input['body'], $id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('update pln bill')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }

        $this->response($data, $status);
    }

    public function bill_delete($id)
	{
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pln_bill_model');
            $this->pln_bill_model->currUser = $currUser;
            
            $success = $this->pln_bill_model->delete($id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('delete pln bill')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }

        $this->response($data, 200);
	}

    /* ------------------------------ PLN Parameters ------------------------------ */
    public function params_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $filter = [
                'witel' => $this->input->get('witel'),
                'divre' => $this->input->get('divre'),
                'id' => $this->input->get('id')
            ];
            
            $this->load->model('pln_params_model');
            $this->pln_params_model->currUser = $currUser;
            $data = [
                'pln_params' => $this->pln_params_model->get($filter),
                'success' => true
            ];

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get pln parameters')
                ->log();

        }

        $this->response($data, $status);
    }

    public function params_post()
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pln_params_model');
            $this->pln_params_model->currUser = $currUser;

            $this->load->library('input_custom');
            $fields = $this->pln_params_model->get_insert_fields();
            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {

            $success = $this->pln_params_model->save($input['body']);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('input new pln parameters')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }

        $this->response($data, $status);
    }

    public function params_put($id)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pln_params_model');
            $this->pln_params_model->currUser = $currUser;
            
            $this->load->library('input_custom');
            $fields = $this->pln_params_model->get_update_fields();
            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {

            $success = $this->pln_params_model->save($input['body'], $id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('update pln parameters')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }

        $this->response($data, $status);
    }

    public function params_delete($id)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pln_params_model');
            $this->pln_params_model->currUser = $currUser;
            
            $success = $this->pln_params_model->delete($id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('delete pln parameters')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }

        $this->response($data, 200);
    }
}