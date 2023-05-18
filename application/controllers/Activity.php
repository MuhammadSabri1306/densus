<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Activity extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
    }

    public function divre_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('lokasi_gepee_model');
			$dataDivre = $this->lokasi_gepee_model->get_divre(null, $currUser);

			$data = [ 'divre' => $dataDivre, 'success' => true ];
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

    public function witel_get($witelCode = null)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('lokasi_gepee_model');
			$dataWitel = $this->lokasi_gepee_model->get_witel(null, $witelCode, $currUser);

			$data = [ 'witel' => $dataWitel, 'success' => true ];
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

    public function witel_by_divre_get($divreCode)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('lokasi_gepee_model');
			$dataWitel = $this->lokasi_gepee_model->get_witel($divreCode, null, $currUser);

			$data = [ 'witel' => $dataWitel, 'success' => true ];
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

    public function lokasi_get($divreCode = null, $witelCode = null)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('lokasi_gepee_model');

            $filter = [ 'divre' => $divreCode, 'witel' => $witelCode ];
			$dataLokasi = $this->lokasi_gepee_model->get_by_code($filter, $currUser);

			$data = [ 'lokasi' => $dataLokasi, 'success' => true ];
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

    public function category_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {

            $this->load->model('activity_category_model');
			$dataCategory = $this->activity_category_model->get();

			$data = [ 'category' => $dataCategory, 'success' => true ];
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

    public function dashboard_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');

        $divreCode = $this->input->get('divre');
        $witelCode = $this->input->get('witel');
        $month = $this->input->get('month');
        if($status === 200) {
            
            $currUser = $this->auth_jwt->get_payload();

            $this->load->model('activity_execution_model');
            $filter = [];
            if($divreCode) $filter['divreCode'] = $divreCode;
            if($witelCode) $filter['witelCode'] = $witelCode;
            if($month) $filter['month'] = $month;

			$consistencyPercent = $this->activity_execution_model->get_consistency_percent($filter, $currUser);
			$dataOnMonth = $this->activity_execution_model->get_calc_on_month($filter, $currUser);

            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get activity:dashboard')
                ->log();
            
            $dashboard = [
                'timestamp' => date('Y-m-d h:i:s'),
                'consistency_percent' => $consistencyPercent
            ];
            $dashboard = array_merge($dashboard, $dataOnMonth);
			$data = [
                'dashboard' => $dashboard,
                'success' => true,
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

    public function chart_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {
            
            $currUser = $this->auth_jwt->get_payload();
            $currYear = date('Y');
            $this->load->model('activity_execution_model');

			$consistencyPercent = $this->activity_execution_model->get_consistency_percent_on_year($currYear, $currUser);
            $consistencyOnMonth = $this->activity_execution_model->get_consistency_on_month($currYear, $currUser);
            $statusCount = $this->activity_execution_model->get_status_count($currYear, $currUser);

            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get activity:dashboard')
                ->log();
            
            $timestamp = date('Y-m-d h:i:s');
            $chart = compact('timestamp', 'consistencyPercent', 'consistencyOnMonth', 'statusCount');
			$data = [ 'chart' => $chart, 'success' => true ];
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

    public function consistency_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');

        $divreCode = $this->input->get('divre');
        $witelCode = $this->input->get('witel');
        $month = $this->input->get('month');
        
        if($status === 200) {
            
            $currUser = $this->auth_jwt->get_payload();
            $filter = [];
            if($divreCode) $filter['divre'] = $divreCode;
            if($witelCode) $filter['witel'] = $witelCode;
            if($month) $filter['month'] = $month;
            
            $this->load->model('activity_schedule_model');
			$savedSchedule = $this->activity_schedule_model->get_filtered($filter, $currUser);
            
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

    public function performance_get()
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
            $month = $this->input->get('month');
            $year = $this->input->get('year');
            
            $this->load->library('datetime_range');
            if(!$year) $year = date('Y');
            if($month) {
                $datetime = $this->datetime_range->get_by_month($month, $year);
            } else {
                $datetime = $this->datetime_range->get_by_year($year);
            }

            $filter = compact('divre', 'witel', 'datetime');

            $this->load->model('activity_execution_model');
            $data = $this->activity_execution_model->get_performance_v3($filter);
            if(is_array($data)) {
                $data['success'] = true;
            } else {
                $data = REST_ERR_BAD_REQ_DATA;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }
        
        $this->response($data, $status);
    }
}