<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Monitoring_pue extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
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
            $currUser = $this->auth_jwt->get_payload();
            $zone = [
                'witel' => $currUser['level'] == 'witel' ? $currUser['locationId'] : $this->input->get('witel'),
                'divre' => $currUser['level'] == 'divre' ? $currUser['locationId'] : $this->input->get('divre')
            ];

            $this->load->model('pue_counter2_model');
            $dataLevel = $this->pue_counter2_model->get_data_level_by_filter($zone);

            $forbiddWitel = $currUser['level'] == 'witel' && $dataLevel['witel_kode'] != $currUser['locationId'];
            $forbiddDivre = $currUser['level'] == 'divre' && $dataLevel['divre_kode'] != $currUser['locationId'];
            if($forbiddWitel || $forbiddDivre) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $pueMonitoringData = $this->pue_counter2_model->get_pue_monitoring($zone);
            $data = array_merge(
                [ 'level' => $dataLevel ],
                $pueMonitoringData,
                [ 'success' => true ]
            );
            
            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get pue monitoring data')
                ->log();
        }
        
        $this->response($data, $status);
    }

    public function detail_get($rtuCode)
    {
        // $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        // switch($status) {
        //     case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
        //     case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
        //     default: $data = REST_ERR_DEFAULT_DATA; break;
        // }
        $status = 200;

        if($status === 200) {
            // $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pue_counter2_model');

            $zone = [ 'rtu' => $rtuCode ];
            if($currUser['level'] == 'witel') $zone['witel'] = $currUser['locationId'];
            if($currUser['level'] == 'regional') $zone['witel'] = $currUser['locationId'];
            $dataLevel = $this->pue_counter2_model->get_data_level_by_filter($zone);

            if($dataLevel['level'] != 'rtu') {
                $data = REST_ERR_NOT_FOUND_DATA;
                $status = REST_ERR_NOT_FOUND_STATUS;
            } else {
                $pueData = $this->pue_counter2_model->get_rtu_pue_hourly($zone, $rtuCode);
                $data = [
                    'level' => $dataLevel,
                    ...$pueData,
                    'success' => true
                ];
            }
            
            // $this->load->library('user_log');
            // $this->user_log
            //     ->userId($currUser['id'])
            //     ->username($currUser['username'])
            //     ->name($currUser['name'])
            //     ->activity('get rtu detail pue monitoring data')
            //     ->log();
        }
        
        $this->response($data, $status);
    }

    public function chart_data_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $zone = [
                'witel' => $currUser['level'] == 'witel' ? $currUser['locationId'] : $this->input->get('witel'),
                'divre' => $currUser['level'] == 'divre' ? $currUser['locationId'] : $this->input->get('divre'),
                'rtu' => $this->input->get('rtu')
            ];

            $this->load->model('pue_counter2_model');

            $dataLevel = $this->pue_counter2_model->get_data_level_by_filter($zone);
            $data = [ 'request_location' => $dataLevel ];

            $forbiddWitel = $currUser['level'] == 'witel' && $dataLevel['witel_kode'] != $currUser['locationId'];
            $forbiddDivre = $currUser['level'] == 'divre' && $dataLevel['divre_kode'] != $currUser['locationId'];
            if($forbiddWitel || $forbiddDivre) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            if(isset($zone['rtu'])) {
                $data['chart'] = $this->pue_counter2_model->get_curr_year($zone['rtu']);
            } else {
                $data['chart'] = $this->pue_counter2_model->get_zone_avg_on_curr_year_v2($zone);
            }

            $data['timestamp'] = date('Y-m-d H:i:s');
            $data['success'] = true;
            
            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get pue chart value')
                ->log();
        }
        
        $this->response($data, $status);
    }

    public function sto_value_on_year_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $zone = [
                'witel' => $currUser['level'] == 'witel' ? $currUser['locationId'] : $this->input->get('witel'),
                'divre' => $currUser['level'] == 'divre' ? $currUser['locationId'] : $this->input->get('divre')
            ];

            $this->load->model('pue_counter2_model');

            $dataLevel = $this->pue_counter2_model->get_data_level_by_filter($zone);
            $data = [ 'request_location' => $dataLevel ];

            $forbiddWitel = $currUser['level'] == 'witel' && $dataLevel['witel_kode'] != $currUser['locationId'];
            $forbiddDivre = $currUser['level'] == 'divre' && $dataLevel['divre_kode'] != $currUser['locationId'];
            if($forbiddWitel || $forbiddDivre) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $stoValue = $this->pue_counter2_model->get_curr_year_avg_on_sto($zone);
            $data['stoValue'] = $stoValue['data'] ? $stoValue['data'] : [];
            $data['timestamp'] = $stoValue['timestamp'] ? $stoValue['timestamp'] : date('Y-m-d H:i:s');
            $data['success'] = true;
            
            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get pue value of sto on current year')
                ->log();
        }
        
        $this->response($data, $status);
    }

    public function latest_value_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $zone = [
                'witel' => $currUser['level'] == 'witel' ? $currUser['locationId'] : $this->input->get('witel'),
                'divre' => $currUser['level'] == 'divre' ? $currUser['locationId'] : $this->input->get('divre'),
                'rtu' => $this->input->get('rtu')
            ];

            $this->load->model('pue_counter2_model');

            $dataLevel = $this->pue_counter2_model->get_data_level_by_filter($zone);
            $data = [ 'request_location' => $dataLevel ];

            $forbiddWitel = $currUser['level'] == 'witel' && $dataLevel['witel_kode'] != $currUser['locationId'];
            $forbiddDivre = $currUser['level'] == 'divre' && $dataLevel['divre_kode'] != $currUser['locationId'];
            if($forbiddWitel || $forbiddDivre) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            if(isset($zone['rtu'])) {
                $pueData = $this->pue_counter2_model->get_latest_value($zone['rtu']);
                $data['latestValue'] = (double) $pueData->pue_value;
                $data['isAvg'] = false;
            } else {
                $pueData = $this->pue_counter2_model->get_latest_avg_value_on_zone($zone);
                $data['latestValue'] = (double) $pueData->avg;
                $data['isAvg'] = true;
            }
            
            $data['timestamp'] = $pueData->timestamp;
            $data['success'] = true;
            
            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get pue latest value')
                ->log();
        }
        
        $this->response($data, $status);
    }

    public function avg_value_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $zone = [
                'witel' => $currUser['level'] == 'witel' ? $currUser['locationId'] : $this->input->get('witel'),
                'divre' => $currUser['level'] == 'divre' ? $currUser['locationId'] : $this->input->get('divre'),
                'rtu' => $this->input->get('rtu')
            ];

            $this->load->model('pue_counter2_model');

            $dataLevel = $this->pue_counter2_model->get_data_level_by_filter($zone);
            $data = [ 'request_location' => $dataLevel ];

            $forbiddWitel = $currUser['level'] == 'witel' && $dataLevel['witel_kode'] != $currUser['locationId'];
            $forbiddDivre = $currUser['level'] == 'divre' && $dataLevel['divre_kode'] != $currUser['locationId'];
            if($forbiddWitel || $forbiddDivre) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $data['averages'] = $this->pue_counter2_model->get_avg_value($zone);
            $data['timestamp'] = date('Y-m-d H:i:s');
            $data['success'] = true;
            
            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get pue avg value')
                ->log();
        }
        
        $this->response($data, $status);
    }

    public function max_value_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $zone = [
                'witel' => $currUser['level'] == 'witel' ? $currUser['locationId'] : $this->input->get('witel'),
                'divre' => $currUser['level'] == 'divre' ? $currUser['locationId'] : $this->input->get('divre'),
                'rtu' => $this->input->get('rtu')
            ];

            $this->load->model('pue_counter2_model');

            $dataLevel = $this->pue_counter2_model->get_data_level_by_filter($zone);
            $data = [ 'request_location' => $dataLevel ];

            $forbiddWitel = $currUser['level'] == 'witel' && $dataLevel['witel_kode'] != $currUser['locationId'];
            $forbiddDivre = $currUser['level'] == 'divre' && $dataLevel['divre_kode'] != $currUser['locationId'];
            if($forbiddWitel || $forbiddDivre) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $data['maxValue'] = $this->pue_counter2_model->get_max_value($zone);
            $data['timestamp'] = date('Y-m-d H:i:s');
            $data['success'] = true;
            
            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get pue max value')
                ->log();
        }
        
        $this->response($data, $status);
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
            $currUser = $this->auth_jwt->get_payload();
            $zone = [
                'witel' => $currUser['level'] == 'witel' ? $currUser['locationId'] : $this->input->get('witel'),
                'divre' => $currUser['level'] == 'divre' ? $currUser['locationId'] : $this->input->get('divre'),
                'rtu' => $this->input->get('rtu')
            ];
            $this->load->model('pue_counter2_model');

            $dataLevel = $this->pue_counter2_model->get_data_level_by_filter($zone);
            $data = [ 'request_location' => $dataLevel ];
            $forbiddWitel = $currUser['level'] == 'witel' && $dataLevel['witel_kode'] != $currUser['locationId'];
            $forbiddDivre = $currUser['level'] == 'divre' && $dataLevel['divre_kode'] != $currUser['locationId'];
            if($forbiddWitel || $forbiddDivre) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        if($status === 200) {
            $perf = $this->pue_counter2_model->get_performance_value($zone);
            $data['timestamp'] = $perf['timestamp'] ? $perf['timestamp'] : date('Y-m-d H:i:s');
            unset($perf['timestamp']);
            $data['performances'] = $perf;
            $data['success'] = true;
            
            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get pue performances')
                ->log();
        }
        
        $this->response($data, $status);
    }
}