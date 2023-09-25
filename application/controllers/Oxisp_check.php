<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Oxisp_check extends RestController
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
            $divreCode = $this->input->get('divre');
            $witelCode = $this->input->get('witel');
            $year = $this->input->get('year') ?? date('Y');
            $month = $this->input->get('month') ?? date('n');

            $this->load->library('datetime_range');
            $datetime = $this->datetime_range->get_by_month($month, $year);

            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode,
                'datetime' => $datetime,
                'year' => $year,
                'month' => $month,
            ];

            $this->load->model('oxisp_check_model');
            $this->oxisp_check_model->currUser = $this->auth_jwt->get_payload();

            $oxispCheck = $this->oxisp_check_model->get_list($filter);
            $data = [
                'enable_months' => $oxispCheck['enable_months'],
                'target_months' => (int) $month,
                'categories' => $oxispCheck['categories'],
                'rooms' => $oxispCheck['rooms'],
                'check_data' => $oxispCheck['check_data'],
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function category_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $this->load->model('oxisp_check_model');
            $data = [
                'categories' => $this->oxisp_check_model->get_categories(),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function index_post()
    {
        $status = $this->auth_jwt->auth('viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('oxisp_check_model');

            $currUser = $this->auth_jwt->get_payload();
            $fields = $this->oxisp_check_model->get_insertable_fields();
            $fields['is_ok'] = array_diff($fields['is_ok'], ['required', 'nullable']);
            $fields['note'] = array_diff($fields['note'], ['required', 'nullable']);
            $fields['evidence'] = array_diff($fields['evidence'], ['required', 'nullable']);

            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('post');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200 && $input['body']['is_room_exists']) {
            $fields = $this->oxisp_check_model->get_insertable_fields();
            $this->input_custom->set_fields($fields);
            
            $input = $this->input_custom->get_body('post', false);
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $body = $input['body'];
            $body['user_id'] = $currUser['id'];
            $body['user_username'] = $currUser['username'];
            $body['user_name'] = $currUser['name'];

            if(!$body['is_room_exists']) {
                $body['is_ok'] = true;
            }

            $isCurrMonth = $body['month'] == date('n') && $body['year'] == date('Y');
            if($isCurrMonth) {
                $body['created_at'] = date('Y-m-d H:i:s');
                $body['updated_at'] = $body['created_at'];
            } else {
                $body['created_at'] = date('Y-m-d H:i:s', strtotime($body['year'].'-'.$body['month'].'-01 00:00:00'));
                $body['updated_at'] = date('Y-m-d H:i:s');
            }

            unset($body['month'], $body['year']);
            
            $this->oxisp_check_model->currUser = $currUser;
            $success = $this->oxisp_check_model->save($body);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('input new ox isp checklist')
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
        $status = $this->auth_jwt->auth('viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('oxisp_check_model');

            $currUser = $this->auth_jwt->get_payload();
            $fields = $this->oxisp_check_model->get_updatable_fields();

            $fields['is_ok'] = array_diff($fields['is_ok'], ['required', 'nullable']);
            $fields['note'] = array_diff($fields['note'], ['required', 'nullable']);
            $fields['evidence'] = array_diff($fields['evidence'], ['required', 'nullable']);

            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('put');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200 && $input['body']['is_room_exists']) {
            $fields = $this->oxisp_check_model->get_updatable_fields();
            $this->input_custom->set_fields($fields);

            $input = $this->input_custom->get_body('put', false);
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $body = $input['body'];
            $body['status'] = 'submitted';
            $body['user_id'] = $currUser['id'];
            $body['user_username'] = $currUser['username'];
            $body['user_name'] = $currUser['name'];
            $body['updated_at'] = date('Y-m-d H:i:s');

            if(!$body['is_room_exists']) {
                $body['is_ok'] = true;
            }

            $this->oxisp_check_model->currUser = $currUser;
            $success = $this->oxisp_check_model->save($body, $id);
            if($success) {

                $data = [
                    'check_value' => $this->oxisp_check_model->get([ 'id' => $id ]),
                    'success' => true
                ];

                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity("update ox isp checklist where id='$id'")
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
        $status = $this->auth_jwt->auth('viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->model('oxisp_check_model');
            $currUser = $this->auth_jwt->get_payload();
            
            $this->oxisp_check_model->currUser = $currUser;
            $success = $this->oxisp_check_model->delete($id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity("delete ox isp checklist where id='$id'")
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }

    public function approve_put($id)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('oxisp_check_model');

            $currUser = $this->auth_jwt->get_payload();
            $body = [
                'status' => 'approved',
                'user_id' => $currUser['id'],
                'user_username' => $currUser['username'],
                'user_name' => $currUser['name'],
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            $this->oxisp_check_model->currUser = $currUser;
            $success = $this->oxisp_check_model->save($body, $id);
            if(!$success) {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $data = [
                'check_value' => $this->oxisp_check_model->get([ 'id' => $id ]),
                'success' => true
            ];

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity("approve ox isp checklist where id='$id'")
                ->log();
        }

        $this->response($data, $status);
    }

    public function reject_put($id)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('oxisp_check_model');

            $currUser = $this->auth_jwt->get_payload();
            $fields = $this->oxisp_check_model->get_rejectable_fields();
            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $body = $input['body'];
            $body['status'] = 'rejected';
            $body['user_id'] = $currUser['id'];
            $body['user_username'] = $currUser['username'];
            $body['user_name'] = $currUser['name'];
            $body['updated_at'] = date('Y-m-d H:i:s');
            
            $this->oxisp_check_model->currUser = $currUser;
            $success = $this->oxisp_check_model->save($body, $id);
            if(!$success) {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $data = [
                'check_value' => $this->oxisp_check_model->get([ 'id' => $id ]),
                'success' => true
            ];

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity("reject ox isp checklist where id='$id'")
                ->log();
        }

        $this->response($data, $status);
    }
}