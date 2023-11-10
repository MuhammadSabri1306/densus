<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Pi_laten extends RestController
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
            $divre = $this->input->get('divre');
            $witel = $this->input->get('witel');
            $year = (int) ($this->input->get('year') ?? date('Y'));
            $month = (int) ($this->input->get('month') ?? date('n'));

            $filter = compact('divre', 'witel', 'year', 'month');
            $currUser = $this->auth_jwt->get_payload();         
            $this->load->model('pi_laten_model');

            $this->pi_laten_model->currUser = $currUser;
            $piGepee = $this->pi_laten_model->get_gepee($filter);

            $data = [
                ...$piGepee,
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function gepee_get()
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
            $year = (int) ($this->input->get('year') ?? date('Y'));
            $month = (int) ($this->input->get('month') ?? date('n'));

            $filter = compact('divre', 'witel', 'year', 'month');
            $currUser = $this->auth_jwt->get_payload();         
            $this->load->model('pi_laten_model');

            $this->pi_laten_model->currUser = $currUser;
            $piGepee = $this->pi_laten_model->get_gepee($filter);

            $data = [
                ...$piGepee,
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function amc_get()
    {
        $status = 200;
        // $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        // switch($status) {
        //     case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
        //     case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
        //     default: $data = REST_ERR_DEFAULT_DATA; break;
        // }

        if($status === 200) {
            $divre = $this->input->get('divre');
            $witel = $this->input->get('witel');
            $year = (int) ($this->input->get('year') ?? date('Y'));
            $month = (int) ($this->input->get('month') ?? date('n'));

            $filter = compact('divre', 'witel', 'year', 'month');
            $currUser = $this->auth_jwt->get_payload();         
            $this->load->model('pi_laten_model');

            $this->pi_laten_model->currUser = $currUser;
            $piGepee = $this->pi_laten_model->get_amc($filter);

            $data = [
                ...$piGepee,
                'success' => true
            ];
        }

        $this->response($data, $status);
    }
}