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
            $month = $this->input->get('month');

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

            $this->load->model('oxisp_check_model');
            $this->oxisp_check_model->currUser = $this->auth_jwt->get_payload();

            $oxispCheck = $this->oxisp_check_model->get($filter);
            $data = [
                'enable_months' => $oxispCheck['enable_months'],
                'target_months' => $oxispCheck['target_months'],
                'categories' => $oxispCheck['categories'],
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
}