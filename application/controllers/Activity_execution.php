<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Activity_execution extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    public function index_get($scheduleId)
    {   
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {
            
            $currUser = $this->auth_jwt->get_payload();

            $this->load->model('activity_execution_model');
			$executionList = $this->activity_execution_model->get_filtered($scheduleId, $currUser);

            $this->load->model('activity_schedule_model');
            $this->activity_schedule_model->currUser = $currUser;
            $schedule = $this->activity_schedule_model->get([ 'id' => $scheduleId ]);

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get activity:pelaksanaan')
                ->log();
            
			$data = [
                'schedule' => $schedule,
                'executionList' => $executionList,
                'success' => true
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
    
    public function index_post($scheduleId)
    {
        $status = $this->auth_jwt->auth('viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->library('input_custom');
            $fields = [
                'title' => ['string', 'required'],
                'description' => ['string', 'required'],
                'description_before' => ['string', 'required'],
                'description_after' => ['string', 'required'],
                'evidence' => ['string', 'required']
            ];

            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            } else {
                $body = $input['body'];
                $body['id_schedule'] = $scheduleId;
                $body['user_id'] = $currUser['id'];
                $body['user_username'] = $currUser['username'];
                $body['user_name'] = $currUser['name'];
                $body['created_at'] = date('Y-m-d H:i:s');
                $body['updated_at'] = $body['created_at'];
            }
        }
        
        if($status == 200){
            $this->load->model("activity_execution_model");
            $success = $this->activity_execution_model->save($body);
            $data = [ 'success' => $success ];

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('input new activity:pelaksanaan')
                ->log();
        }
        
        $this->response($data, $status);
    }
    
    public function index_put($execId)
    {
        $status = $this->auth_jwt->auth('viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->library('input_custom');
            $fields = [
                'title' => ['string', 'required'],
                'description' => ['string', 'required'],
                'description_before' => ['string', 'required'],
                'description_after' => ['string', 'required'],
                'evidence' => ['string', 'required']
            ];

            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            } else {
                $body = $input['body'];
                $body['status'] = 'submitted';
                $body['user_id'] = $currUser['id'];
                $body['user_username'] = $currUser['username'];
                $body['user_name'] = $currUser['name'];
                $body['updated_at'] = date('Y-m-d H:i:s');
            }
        }
        
        if($status == 200){
            $this->load->model("activity_execution_model");
            $success = $this->activity_execution_model->save($body, $execId, $currUser);
            $data = [ 'success' => $success ];

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('update activity:pelaksanaan')
                ->log();
        }
        
        $this->response($data, $status);
    }

    public function index_delete($execId)
    {
        $status = $this->auth_jwt->auth('viewer', 'teknisi');

		if($status === 200) {
			$currUser = $this->auth_jwt->get_payload();

            $this->load->model("activity_execution_model");
            $success = $this->activity_execution_model->delete($execId, $currUser);
            $data = [ 'success' => $success ];

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('delete activity:pelaksanaan')
                ->log();
            
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

    public function approve_put($execId)
    {
        $status = $this->auth_jwt->auth('admin');
		if($status === 200) {
			$currUser = $this->auth_jwt->get_payload();
            $body = [
                'status' => 'approved',
                'user_id' => $currUser['id'],
                'user_username' => $currUser['username'],
                'user_name' => $currUser['name'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->load->model("activity_execution_model");
            $success = $this->activity_execution_model->save($body, $execId, $currUser);
            $data = [ 'success' => $success ];

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('approve activity:pelaksanaan')
                ->log();
            
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

    public function reject_put($execId)
    {
        $this->load->library('input_handler');
        $status = $this->auth_jwt->auth('admin');
		if($status === 200) {
			$this->input_handler->set_fields('reject_description');
            $this->input_handler->set_required('reject_description');

			$input = $this->input_handler->get_body('put');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ;
            }

            $currUser = $this->auth_jwt->get_payload();
            
            if($status == 200){
                $rejectDesc = $input['body']['reject_description'];
                $body = [
                    'status' => 'rejected',
                    'reject_description' => $rejectDesc,
                    'user_id' => $currUser['id'],
                    'user_username' => $currUser['username'],
                    'user_name' => $currUser['name'],
                    'updated_at' => date('Y-m-d H:i:s')
                ];
    
                $this->load->model("activity_execution_model");
                $success = $this->activity_execution_model->save($body, $execId, $currUser);
                $data = [ 'success' => $success ];

                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('reject activity:pelaksanaan')
                    ->log();
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
}