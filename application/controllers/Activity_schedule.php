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
            $this->input_handler->set_fields('schedule', 'divreCode');
            $this->input_handler->set_required('schedule', 'divreCode');

			$input = $this->input_handler->get_body('post');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ;
            } else {

                $filter = [ 'divre' => $input['body']['divreCode'] ];
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
                $success = $this->activity_schedule_model->update($params, $filter['month'], $filter['divre'], $currUser);
            } else {
                $success = $this->activity_schedule_model->store($params, $filter['month'], $filter['divre'], $currUser);
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
}