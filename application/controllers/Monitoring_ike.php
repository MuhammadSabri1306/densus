<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Monitoring_ike extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    public function index_get()
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
            $year = $this->input->get('year');
            $month = $this->input->get('month');

            $this->load->library('datetime_range');
            if(!$year) $year = date('Y');
            if($month) {
                $datetime = $this->datetime_range->get_by_month($month, $year);
            } else {
                $datetime = $this->datetime_range->get_by_year($year);
            }

            $filter = compact('divre', 'witel', 'datetime');
            $currUser = $this->auth_jwt->get_payload();            
            $this->load->model('ike_model');

            $this->ike_model->currUser = $currUser;
            
            $data = [
                'ike_data' => $this->ike_model->get_list($filter),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function detail_get($idLocation, $id = null)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $year = $this->input->get('year');
            $month = $this->input->get('month');
            
            $filter = [
                'datetime' => null,
                'idLocation' => $idLocation
            ];

            if(!$year) $year = date('Y');
            if($id) $filter['id'] = $id;
            
            $this->load->library('datetime_range');
            if($month) {
                $filter['datetime'] = $this->datetime_range->get_by_month($month, $year);
            } else {
                $filter['datetime'] = $this->datetime_range->get_by_year($year);
            }
            
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('ike_model');
            $this->ike_model->currUser = $currUser;

            $ikeData = $this->ike_model->get_detail($filter);
            $data = [
                'ike' => $ikeData['ike'],
                'location' => $ikeData['location'],
                'inputable_weeks' => $ikeData['inputable_weeks'],
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function index_post()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('ike_model');

            $currUser = $this->auth_jwt->get_payload();
            $fields = $this->ike_model->get_insertable_fields();
            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $getIntStr = fn($str) => (int) preg_replace('/(^(0+)|[^0-9])/', '', $str);
            $getDecStr = fn($str) => (double) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $str));

            $body = $input['body'];
            $body['kwh_usage'] = $getIntStr($body['kwh_usage']);
            $body['area_value'] = $getDecStr($body['area_value']);
            $body['week'] = $getIntStr($body['week']);
            $body['created_at'] = date('Y-m-d H:i:s');
            $body['updated_at'] = $body['created_at'];
            
            $this->ike_model->currUser = $currUser;
            $success = $this->ike_model->save($body);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('input new ike')
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }

    public function index_put($id)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('ike_model');

            $currUser = $this->auth_jwt->get_payload();
            $fields = $this->ike_model->get_updatable_fields();
            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $getIntStr = fn($str) => (int) preg_replace('/(^(0+)|[^0-9])/', '', $str);
            $getDecStr = fn($str) => (double) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $str));

            $body = $input['body'];
            $body['kwh_usage'] = $getIntStr($body['kwh_usage']);
            $body['area_value'] = $getDecStr($body['area_value']);
            $body['updated_at'] = date('Y-m-d H:i:s');
            
            $this->ike_model->currUser = $currUser;
            $success = $this->ike_model->save($body, $id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('update data ike')
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }

    public function index_delete($id)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->model('ike_model');
            $currUser = $this->auth_jwt->get_payload();

            $this->ike_model->currUser = $currUser;
            $success = $this->ike_model->delete($id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('delete data ike on id:'.$id)
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }
}