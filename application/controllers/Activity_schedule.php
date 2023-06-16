<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Activity_schedule extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    public function available_month_get()
    {
        $data = [ 'month' => (int) date('n'), 'success' => true ];
        $this->response($data, 200);
    }

    public function index_get()
    {
        $divreCode = $this->input->get('divre');
        $witelCode = $this->input->get('witel');
        $month = $this->input->get('month');
        $isChecked = $this->input->get('ischecked');
        
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {
            
            $currUser = $this->auth_jwt->get_payload();
            $filter = [];
            if($divreCode) $filter['divre'] = $divreCode;
            if($witelCode) $filter['witel'] = $witelCode;
            if($month) $filter['month'] = $month;
            if($isChecked !== null) $filter['isChecked'] = $isChecked;
            $this->load->model('activity_schedule_model');
			$savedSchedule = $this->activity_schedule_model->get_filtered($filter, $currUser);

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get activity:penjadwalan')
                ->log();
            
			$data = [ 'schedule' => $savedSchedule, 'success' => true ];
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
        $params = []; $filter = [];

        if($status === 200) {
            $this->input_handler->set_fields('schedule', 'divreCode', 'witelCode');
            $this->input_handler->set_required('schedule', 'divreCode');

			$input = $this->input_handler->get_body('post');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ;
            } else {

                $filter = [ 'divre' => $input['body']['divreCode'] ];
                if(isset($input['body']['witelCode'])) {
                    $filter['witel'] = $input['body']['witelCode'];
                }

                foreach($input['body']['schedule'] as $item) {
                    $temp = explode('&', $item);
                    if(count($temp) != 3) {
                        $data = [ 'success' => false, 'message' => 'Params store as wrong format.' ];
                        $status = REST_ERR_BAD_REQ;
                    } else {
                        array_push($params, [
                            'id_category' => $temp[2],
                            'month' => $temp[1],
                            'id_lokasi' => $temp[0]
                        ]);
                    }
                }

            }
        }
        
		if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('activity_schedule_model');

            $filter['month'] = date('n');
            $savedScheduleCount = $this->activity_schedule_model->get_filtered_count($filter, $currUser);
            $isUpdate = $savedScheduleCount && $savedScheduleCount['count'] > 0;
            $logActivityTitle = 'update activity:penjadwalan';
            $success = false;

            if($isUpdate) {
                $success = $this->activity_schedule_model->update($params, $filter, $currUser);
            } else {
                $success = $this->activity_schedule_model->store($params, $filter, $currUser);
                $logActivityTitle = 'input new activity:penjadwalan';
            }

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity($logActivityTitle)
                ->log();
            $data = [ 'success' => $success ];
            
            $this->response($data, $status);

        } else {

            if(!isset($data)) {
                switch($status) {
                    case REST_ERR_AUTH_CODE: $data = REST_ERR_AUTH_DATA; break;
                    case REST_ERR_EXP_CODE: $data = REST_ERR_EXP_DATA; break;
                    default: $data = null;
                }
            }

            $this->response($data, $status);

        }
    }

    public function index_v2_post()
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $filter = [
                'divre' => $this->input->get('divre'),
                'witel' => $this->input->get('witel'),
                'month' => $this->input->get('month')
            ];
            
            $this->load->library('input_custom');
            $this->input_custom->set_fields([ 'schedule' => ['required'] ]);
            $input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $schedule = $input['body']['schedule'];
            // if(count($schedule) > 0 && !is_array($schedule[0])) {
            //     $data = [ 'success' => false, 'message' => 'Endpoint ini sedang dalam perbaikan.' ];
            //     $status = REST_ERR_BAD_REQ;
            // }
        }

        if($status === 200) {
            $this->load->model('activity_schedule_model');
            
            $currUser = $this->auth_jwt->get_payload();
            $this->activity_schedule_model->currUser = $currUser;
            $success = $this->activity_schedule_model->save($schedule, $filter);
            if($success == 'successfull') {
                $data = [ 'success' => true ];
            } elseif($success == 'some_error') {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = [
                    'success' => false,
                    'message' => 'Beberapa data mungkin tidak tersimpan. Harap ulangi beberapa saat lagi'
                ];
            } else {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }

            if($success != 'error') {
                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('save activity:penjadwalan')
                    ->log();
            }
        }
        
        $this->response($data, $status);
    }

    public function index_v2_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->model('activity_schedule_model');

            $currUser = $this->auth_jwt->get_payload();
            $this->activity_schedule_model->currUser = $currUser;

            $divre = $this->input->get('divre');
            $witel = $this->input->get('witel');
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
                'divre' => $divre,
                'witel' => $witel,
                'datetime' => $datetime
            ];

            $data = [
                'schedule' => $this->activity_schedule_model->get($filter),
                'updatableTime' => EnvPattern::getUpdatableActivityTime(true),
                'updatable_time' => [
                    'schedule' => EnvPattern::getActivityScheduleTime(true),
                    'execution' => EnvPattern::getActivityExecutionTime(true)
                ],
                'success' => true
            ];

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get activity:penjadwalan')
                ->log();
        }
        
        $this->response($data, $status);
    }

    public function index_v3_get()
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
            $startMonth = $this->input->get('month');
            
            if(!$year) $year = date('Y');
            $endMonth = $startMonth ? $startMonth : date('m');
            if(!$startMonth) $startMonth = 1;
            
            $this->load->library('datetime_range');
            $datetime = $this->datetime_range->get_by_month_range($startMonth, $endMonth, $year);
    
            $filter = compact('divre', 'witel', 'datetime');
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('activity_schedule_model');

            $data = $this->activity_schedule_model->currUser = $currUser;
            $data = $this->activity_schedule_model->get_all_v2($filter);
        }

        $this->response($data, $status);
    }
}