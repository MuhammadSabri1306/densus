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
                'datetimeYear' => $datetimeYear
            ];

            $this->load->model('gepee_management_model');
            $this->load->model('activity_category_model');

            $currUser = $this->auth_jwt->get_payload();
            $this->gepee_management_model->currUser = $currUser;

            $pueLowLimit = 1.8;
            $data = [
                'pueLowLimit' => $pueLowLimit,
                'gepee' => $this->gepee_management_model->get_report($filter, $pueLowLimit),
                'category' => $this->activity_category_model->get(),
                'timestamp' => date('Y-m-d H:i:s'),
                'success' => true
            ];

            if(!isset($divreCode) && !isset($witelCode)) {
                $data['gepee_summary_nasional'] = $this->gepee_management_model->get_report_summary_nasional($filter, $pueLowLimit);
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
        // $status = 200;
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
                'datetimeYear' => $datetimeYear
            ];

            $this->load->model('gepee_management_model');
            $this->load->model('activity_category_model');

            $currUser = $this->auth_jwt->get_payload();
            $this->gepee_management_model->currUser = $currUser;

            $pueLowLimit = 1.8;
            $data = [
                'pueLowLimit' => $pueLowLimit,
                'gepee' => $this->gepee_management_model->get_report_v2($filter, $pueLowLimit),
                'category' => $this->activity_category_model->get(),
                'timestamp' => date('Y-m-d H:i:s'),
                'success' => true
            ];

            if(!isset($divreCode) && !isset($witelCode)) {
                $data['gepee_summary_nasional'] = $this->gepee_management_model->get_report_summary_nasional($filter, $pueLowLimit);
            }
        }

        $this->response($data, $status);
    }
    
}