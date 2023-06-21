<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Pue_target extends RestController
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
        $status = 200;

        if($status === 200) {
            $divreCode = $this->input->get('divre');
            $witelCode = $this->input->get('witel');
            $year = $this->input->get('year');

            if(!$year) $year = date('Y');
            $this->load->library('datetime_range');
            $datetime = $this->datetime_range->get_by_year($year);

            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode,
                'datetime' => $datetime
            ];

            $this->load->model('pue_location_target_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->pue_location_target_model->currUser = $currUser;
            
            $data = [
                'pue_target' => $this->pue_location_target_model->get($filter),
                'timestamp' => date('Y-m-d H:i:s'),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function index_post()
    {
        $status = REST_ERR_BAD_REQ_STATUS;
        $data = [ 'success' => false, 'message' => 'Fitur sedang dalam perbaikan.' ];

        // $status = $this->auth_jwt->auth('admin');
        $status = $this->auth_jwt->auth('admin', 'viewew', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            // if($currUser['level'] != 'nasional') {
            //     $data = [ 'success' => false ];
            //     $status = REST_ERR_DEFAULT_STATUS;
            // }
        }

        if($status === 200) {
            $this->load->model('pue_location_target_model');
            $this->pue_location_target_model->currUser = $currUser;
            $fields = $this->pue_location_target_model->get_insertable_fields();

            $this->load->library('input_custom');
            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            } else {
                $body = $input['body'];
                $body['created_at'] = date('Y-m-d H:i:s');
                $body['updated_at'] = $body['created_at'];
                $body['user_id'] = $currUser['id'];
                $body['user_username'] = $currUser['username'];
                $body['user_name'] = $currUser['name'];
            }
        }

        if($status === 200) {

            $isSuccess = $this->pue_location_target_model->save($body);
            if($isSuccess) {
                $data = [ 'success' => true ];
                $logMsg = 'input new pue target on regional:'.$body['divre_name'].', witel:'.$body['witel_name'];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity($logMsg)
                    ->log();
            } else {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }

        }

        $this->response($data, $status);
    }

    public function index_put($idTarget)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            if($currUser['level'] != 'nasional') {
                $data = [ 'success' => false ];
                $status = REST_ERR_DEFAULT_STATUS;
            }
        }

        if($status === 200) {
            $this->load->model('pue_location_target_model');
            $this->pue_location_target_model->currUser = $currUser;
            $fields = $this->pue_location_target_model->get_updatable_fields();

            $this->load->library('input_custom');
            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            } else {
                $body = $input['body'];
                $body['updated_at'] = date('Y-m-d H:i:s');
                $body['user_id'] = $currUser['id'];
                $body['user_username'] = $currUser['username'];
                $body['user_name'] = $currUser['name'];
            }
        }

        if($status === 200) {

            $isSuccess = $this->pue_location_target_model->save($body, $idTarget);
            if($isSuccess) {
                $data = [ 'success' => true ];
                $logMsg = 'update pue target on regional:'.$body['divre_name'].', witel:'.$body['witel_name'];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity($logMsg)
                    ->log();
            } else {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }

        }

        $this->response($data, $status);
    }

    public function index_delete($idTarget)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            if($currUser['level'] != 'nasional') {
                $data = [ 'success' => false ];
                $status = REST_ERR_DEFAULT_STATUS;
            }
        }

        if($status === 200) {
            $this->load->model('pue_location_target_model');
            $this->pue_location_target_model->currUser = $currUser;
            $isSuccess = $this->pue_location_target_model->delete($idTarget);

            if($isSuccess) {
                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('delete pue target')
                    ->log();
            } else {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }

        }

        $this->response($data, $status);
    }

    public function report_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $divreCode = $this->input->get('divre');
            $witelCode = $this->input->get('witel');
            $year = $this->input->get('year');
            $quarter = $this->input->get('quarter');

            if(!$year) $year = date('Y');
            $this->load->library('datetime_range');

            if($quarter) {
                $datetime = $this->datetime_range->get_by_quarter($quarter, $year);
            } else {
                $datetime = $this->datetime_range->get_quarter_by_month(date('n'), $year);
            }

            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode,
                'datetime' => $datetime
            ];

            $this->load->model('gepee_management_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->gepee_management_model->currUser = $currUser;

            $data = [
                'pue_category' => $this->gepee_management_model->get_pue_category_item(),
                'pue_target' => $this->gepee_management_model->get_pue_report($filter),
                'timestamp' => date('Y-m-d H:i:s'),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function report_v2_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $divreCode = $this->input->get('divre');
            $witelCode = $this->input->get('witel');
            $year = $this->input->get('year');
            $quarter = $this->input->get('quarter');

            if(!$year) $year = date('Y');
            if(!$quarter) {
                $month = (int) date('n');
                $quarter = ceil($month / 3);
            }

            $this->load->library('datetime_range');
            $datetime = $this->datetime_range->get_by_quarter($quarter, $year);
            
            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode,
                'datetime' => $datetime,
                'quartal' => (int) $quarter
            ];

            $this->load->model('gepee_management_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->gepee_management_model->currUser = $currUser;

            $data = [
                'pue_category' => $this->gepee_management_model->get_pue_category_item(),
                'pue_target' => $this->gepee_management_model->get_pue_report_v2($filter),
                'timestamp' => date('Y-m-d H:i:s'),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function location_status_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $divreCode = $this->input->get('divre');
            $witelCode = $this->input->get('witel');

            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode
            ];

            $this->load->model('gepee_management_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->gepee_management_model->currUser = $currUser;

            $data = [
                'pue_status' => $this->gepee_management_model->get_location_status($filter),
                'timestamp' => date('Y-m-d H:i:s'),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }
}