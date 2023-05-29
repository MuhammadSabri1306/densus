<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Oxisp_activity extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    public function performance_get()
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
            $month = $this->input->get('month');

            if(!$year) $year = date('Y');
            $this->load->library('datetime_range');
            
            if($month) {
                $datetime = $this->datetime_range->get_by_month($month, $year);
            } else {
                $datetime = $this->datetime_range->get_by_year($year);
            }

            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode,
                'datetime' => $datetime
            ];
            $this->load->model('oxisp_activity_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->oxisp_activity_model->currUser = $currUser;
            $data = $this->oxisp_activity_model->get_performance($filter);
            $data['success'] = true;
        }

        $this->response($data, $status);
    }

    public function sto_month_data_get($year, $month, $idLocation)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('datetime_range');
            $datetime = $this->datetime_range->get_by_month($month, $year);

            $filter = [
                'idLocation' => $idLocation,
                'datetime' => $datetime
            ];

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('oxisp_activity_model');
            $this->oxisp_activity_model->currUser = $currUser;
            $this->load->model('master_sto_densus_model');
            $this->master_sto_densus_model->currUser = $currUser;

            $data = [
                'activity' => $this->oxisp_activity_model->get_list($filter),
                'location' => $this->master_sto_densus_model->get($filter),
                'time' => [ 'year' => $year, 'month' => $month ],
                'success' => true
            ];

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get oxisp list by sto and month')
                ->log();
        }

        $this->response($data, $status);
    }

    public function location_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $filter = [
                'divre' => $this->input->get('divre'),
                'witel' => $this->input->get('witel'),
                'idLocation' => $this->input->get('idLocation')
            ];

            $this->load->model('master_sto_densus_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->master_sto_densus_model->currUser = $currUser;
            
            $data = [
                'location' => $this->master_sto_densus_model->get($filter),
                'success' => true
            ];
        }

        $this->response($data, $status);
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
            $this->load->model('master_sto_densus_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->master_sto_densus_model->currUser = $currUser;
            $fields = $this->master_sto_densus_model->get_fields();

            $this->load->library('input_custom');
            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $body = $input['body'];
            $locValidation = [
                $currUser['level'] == 'divre' && $currUser['locationId'] != $body['divre_kode'],
                $currUser['level'] == 'witel' && $currUser['locationId'] != $body['witel_kode']
            ];
            if(in_array(true, $locValidation)) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $isSuccess = $this->master_sto_densus_model->save($body);
            if(!$isSuccess) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $data = [ 'success' => true ];
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('input new oxisp location')
                ->log();
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
            $this->load->model('master_sto_densus_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->master_sto_densus_model->currUser = $currUser;
            $fields = $this->master_sto_densus_model->get_fields();

            $this->load->library('input_custom');
            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $body = $input['body'];
            $locValidation = [
                $currUser['level'] == 'divre' && $currUser['locationId'] != $body['divre_kode'],
                $currUser['level'] == 'witel' && $currUser['locationId'] != $body['witel_kode']
            ];
            if(in_array(true, $locValidation)) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $isSuccess = $this->master_sto_densus_model->save($body, $idLocation);
            if(!$isSuccess) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $data = [ 'success' => true ];
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('update oxisp location, id:'.$idLocation)
                ->log();
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
            $this->load->model('oxisp_activity_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->oxisp_activity_model->currUser = $currUser;
            $isSuccess = $this->oxisp_activity_model->delete_by_location($idLocation);

            if(!$isSuccess) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }
        
        if($status === 200) {
            $this->load->model('master_sto_densus_model');
            $this->master_sto_densus_model->currUser = $currUser;
            $isSuccess = $this->master_sto_densus_model->delete($idLocation);

            if(!$isSuccess) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $data = [ 'success' => true ];
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('delete oxisp location, id:'.$idLocation)
                ->log();
        }

        $this->response($data, $status);
    }

    public function index_post($year, $month)
    {
        $status = $this->auth_jwt->auth('viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->model('oxisp_activity_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->oxisp_activity_model->currUser = $currUser;
            $fields = $this->oxisp_activity_model->get_insertable_fields();

            $this->load->library('input_custom');
            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('post');

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

            $dt = new DateTime();
            $dt->setDate($year, $month, $dt->format('d'));
            $datetime = $dt->format('Y-m-d H:i:s');

            $body['updated_at'] = $datetime;
            $body['created_at'] = $datetime;
            
            $isSuccess = $this->oxisp_activity_model->save($body);
            if(!$isSuccess) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $data = [ 'success' => true ];
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('input new oxisp data')
                ->log();
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
            $this->load->model('oxisp_activity_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->oxisp_activity_model->currUser = $currUser;
            $fields = $this->oxisp_activity_model->get_updatable_fields();

            $this->load->library('input_custom');
            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('put');

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

            $isSuccess = $this->oxisp_activity_model->save($body, $id);
            if(!$isSuccess) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $data = [ 'success' => true ];
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('update oxisp data')
                ->log();
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
            $this->load->model('oxisp_activity_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->oxisp_activity_model->currUser = $currUser;
            $body = [
                'status' => 'approved',
                'user_id' => $currUser['id'],
                'user_username' => $currUser['username'],
                'user_name' => $currUser['name'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $isSuccess = $this->oxisp_activity_model->save($body, $id);
            if(!$isSuccess) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $data = [ 'success' => true ];
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('approve oxisp data')
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
            $currUser = $this->auth_jwt->get_payload();
            $this->load->library('input_custom');

            $rejectField = ['string', 'required'];
            $this->input_custom->set_fields([ 'reject_descr' => $rejectField ]);
			$input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }
        
        if($status === 200) {
            $this->load->model('oxisp_activity_model');
            $this->oxisp_activity_model->currUser = $currUser;

            $body = [
                'status' => 'rejected',
                'reject_descr' => $input['body']['reject_descr'],
                'user_id' => $currUser['id'],
                'user_username' => $currUser['username'],
                'user_name' => $currUser['name'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $isSuccess = $this->oxisp_activity_model->save($body, $id);
            if(!$isSuccess) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $data = [ 'success' => true ];
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('reject oxisp data')
                ->log();
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
            $this->load->model('oxisp_activity_model');
            $currUser = $this->auth_jwt->get_payload();
            $this->oxisp_activity_model->currUser = $currUser;
            $isSuccess = $this->oxisp_activity_model->delete($id);

            if(!$isSuccess) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $data = [ 'success' => true ];
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('delete oxisp data')
                ->log();
        }

        $this->response($data, $status);
    }
}