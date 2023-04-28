<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Pue_offline extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    public function location_post()
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('pue_location_model');

            $currUser = $this->auth_jwt->get_payload();
            $fields = $this->pue_location_model->get_fields();
            
            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $this->pue_location_model->currUser = $currUser;
            $success = $this->pue_location_model->save($input['body']);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('input new pue offline location')
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }

    public function location_put($idLocation)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('pue_location_model');

            $currUser = $this->auth_jwt->get_payload();
            $fields = $this->pue_location_model->get_fields();
            
            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $this->pue_location_model->currUser = $currUser;
            $success = $this->pue_location_model->save($input['body'], $idLocation);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('update pue offline location on id:'.$idLocation)
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }

    public function location_delete($idLocation)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->model('pue_location_model');
            $currUser = $this->auth_jwt->get_payload();

            $this->pue_location_model->currUser = $currUser;
            $success = $this->pue_location_model->delete($idLocation);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('delete pue offline location on id:'.$idLocation)
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }

    public function location_data_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $divre = $this->input->get('divre');
            $witel = $this->input->get('witel');
            $idLocation = $this->input->get('idLocation');
            $year = $this->input->get('year');
            $month = $this->input->get('month');

            $this->load->library('datetime_range');
            if(!$year) $year = date('Y');
            if($month) {
                $datetime = $this->datetime_range->get_by_month($month, $year);
            } else {
                $datetime = $this->datetime_range->get_by_year($year);
            }

            $filter = compact('divre', 'witel', 'idLocation', 'datetime');
            $currUser = $this->auth_jwt->get_payload();            
            $this->load->model('pue_offline_model');

            $this->pue_offline_model->currUser = $currUser;
            $data = [
                'location_data' => $this->pue_offline_model->get_location_data($filter),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }
}