<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Gepee_report extends RestController
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
            $month = $this->input->get('month') ?? date('m');

            $this->load->library('datetime_range');
            $datetime = $this->datetime_range->get_by_month($month, $year);
            $datetimeYear = $this->datetime_range->get_by_year($year);

            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode,
                'datetime' => $datetime,
                'datetimeYear' => $datetimeYear,
                'year' => (int) $year,
                'month' => (int) $month,
            ];

            $this->load->model('gepee_management_model');
            $this->load->model('activity_category_model');

            $currUser = $this->auth_jwt->get_payload();
            $this->gepee_management_model->currUser = $currUser;

            $dataReport = $this->gepee_management_model->get_report_v3($filter, true);
            $data = [
                'pue_max_target' => $this->gepee_management_model->pueMaxTarget,
                ...$dataReport,
                'category' => $this->activity_category_model->get(),
                'timestamp' => date('Y-m-d H:i:s'),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function nasional_get()
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

            if(!$year) $year = date('Y');
            if(!$month) $month = date('m');

            $this->load->library('datetime_range');
            $datetime = $this->datetime_range->get_by_month($month, $year);
            $datetimeYear = $this->datetime_range->get_by_year($year);

            $filter = [
                'datetime' => $datetime,
                'datetimeYear' => $datetimeYear
            ];

            $this->load->model('gepee_management_model');
            $this->load->model('activity_category_model');

            $currUser = $this->auth_jwt->get_payload();
            $this->gepee_management_model->currUser = $currUser;

            $pueLowLimit = 1.8;
            $data = [
                'gepee_nasional' => $this->gepee_management_model->get_report_sum_nasional($filter, $pueLowLimit),
                'timestamp' => date('Y-m-d H:i:s'),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function test_amc_get()
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
            if(!$month) $month = date('m');

            $this->load->library('datetime_range');
            $datetime = $this->datetime_range->get_by_month($month, $year);
            $datetimeYear = $this->datetime_range->get_by_year($year);

            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode,
                'datetime' => $datetime,
                'datetimeYear' => $datetimeYear,
                'year' => (int) ($this->input->get('year') ?? date('Y')),
                'month' => (int) ($this->input->get('month') ?? date('n')),
            ];
            dd_json([
                'filter' => $filter
            ]);

            $this->load->model('gepee_management_model');
            $this->load->model('activity_category_model');

            $currUser = $this->auth_jwt->get_payload();
            $this->gepee_management_model->currUser = $currUser;

            $pueLowLimit = 1.8;
            $dataReport = $this->gepee_management_model->test_amc($filter);
            $data = [
                'data_report' => $dataReport,
                'timestamp' => date('Y-m-d H:i:s'),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }
    
}