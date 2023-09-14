<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Monitoring_kwh extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    public function daily_get()
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
            $year = $this->input->get('year') ?? date('Y');
            $month = $this->input->get('month') ?? date('m');

            $this->load->library('datetime_range');

            if($month == date('m') || $month == date('n')) {
                $endDate = date('d');
                $datetime = $this->datetime_range->get_by_daterange_month('01', $endDate, $month, $year);
            } else {
                $datetime = $this->datetime_range->get_by_month($month, $year);
            }
            $filter = compact('divre', 'witel', 'datetime');
            $currUser = $this->auth_jwt->get_payload();            
            $this->load->model('monitoring_kwh_model');

            $this->monitoring_kwh_model->currUser = $currUser;
            $kwhEntries = $this->monitoring_kwh_model->get_daily($filter);
            
            $data = [
                'kwh_data' => $kwhEntries['kwh_data'],
                'day_column' => $kwhEntries['day_column'],
                'start_date' => $this->datetime_range->startDate->format('Y-m-d H:i:s'),
                'end_date' => $this->datetime_range->endDate->format('Y-m-d H:i:s'),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function weekly_get()
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
            $year = $this->input->get('year') ?? date('Y');
            $month = $this->input->get('month') ?? date('m');

            $this->load->library('datetime_range');

            if($month == date('m') || $month == date('n')) {
                $endDate = date('d');
                $datetime = $this->datetime_range->get_by_daterange_month('01', $endDate, $month, $year);
            } else {
                $datetime = $this->datetime_range->get_by_month($month, $year);
            }
            $filter = compact('divre', 'witel', 'datetime');
            $currUser = $this->auth_jwt->get_payload();            
            $this->load->model('monitoring_kwh_model');

            $this->monitoring_kwh_model->currUser = $currUser;
            $kwhEntries = $this->monitoring_kwh_model->get_weekly($filter);
            
            $data = [
                'kwh_data' => $kwhEntries['kwh_data'],
                'week_column' => $kwhEntries['week_column'],
                'start_date' => $this->datetime_range->startDate->format('Y-m-d H:i:s'),
                'end_date' => $this->datetime_range->endDate->format('Y-m-d H:i:s'),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function monthly_get()
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
            $year = $this->input->get('year') ?? date('Y');

            $this->load->library('datetime_range');
            $datetime = $this->datetime_range->get_by_month_range('01', date('m'), $year);

            $filter = compact('divre', 'witel', 'datetime');
            $this->load->model('monitoring_kwh_model');

            $this->monitoring_kwh_model->currUser = $this->auth_jwt->get_payload();
            $kwhEntries = $this->monitoring_kwh_model->get_monthly($filter);
            
            $data = [
                'kwh_data' => $kwhEntries['kwh_data'],
                'month_column' => $kwhEntries['month_column'],
                'start_date' => $this->datetime_range->startDate->format('Y-m-d H:i:s'),
                'end_date' => $this->datetime_range->endDate->format('Y-m-d H:i:s'),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function mom_get()
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
            $year = $this->input->get('year') ?? date('Y');
            $month = $this->input->get('month') ?? date('m');

            $chosenMonth = ltrim($month, '0');
            $prevMonth = ($chosenMonth == 1) ? 12 : $chosenMonth - 1;
            $prevMonth = str_pad($prevMonth, 2, '0', STR_PAD_LEFT);

            $this->load->library('datetime_range');
            if($month == date('m') || $month == date('n')) {

                $endDate = date('d');
                $datetime = $this->datetime_range->get_by_daterange_month('01', $endDate, $month, $year);
                $prevDatetime = $this->datetime_range->get_by_daterange_month('01', $endDate, $prevMonth, $year);

            } else {

                $datetime = $this->datetime_range->get_by_month($month, $year);
                $prevDatetime = $this->datetime_range->get_by_month($prevMonth, $year);

            }

            $filter = compact('divre', 'witel', 'datetime', 'prevDatetime');
            $this->load->model('monitoring_kwh_model');

            $this->monitoring_kwh_model->currUser = $this->auth_jwt->get_payload();
            $kwhMom = $this->monitoring_kwh_model->get_mom($filter);
            
            $data = [
                'mom' => $kwhMom['mom'],
                'target_area' => $kwhMom['target_area'],
                'success' => true
            ];
        }

        $this->response($data, $status);
    }
}