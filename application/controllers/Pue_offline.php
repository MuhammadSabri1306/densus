<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

/* NOTE
 * Endpoint location dihapus
 */

class Pue_offline extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    public function location_post()
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('pue_location_model');

            $currUser = $this->auth_jwt->get_payload();
            $fields = $this->pue_location_model->get_fields();
            
            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $this->pue_location_model->currUser = $currUser;
            $success = $this->pue_location_model->save($input['body']);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('input new pue offline location')
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }

    public function location_put($idLocation)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('pue_location_model');

            $currUser = $this->auth_jwt->get_payload();
            $fields = $this->pue_location_model->get_fields();
            
            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $this->pue_location_model->currUser = $currUser;
            $success = $this->pue_location_model->save($input['body'], $idLocation);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('update pue offline location on id:'.$idLocation)
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }

    public function location_delete($idLocation)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->model('pue_location_model');
            $currUser = $this->auth_jwt->get_payload();

            $this->pue_location_model->currUser = $currUser;
            $success = $this->pue_location_model->delete($idLocation);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('delete pue offline location on id:'.$idLocation)
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }

    public function location_data_get()
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
            $month = $this->input->get('month');

            $this->load->library('datetime_range');
            if(!$year) $year = date('Y');
            if($month) {
                $datetime = $this->datetime_range->get_by_month($month, $year);
            } else {
                $datetime = $this->datetime_range->get_by_year($year);
            }

            $filter = compact('divre', 'witel', 'datetime');
            $currUser = $this->auth_jwt->get_payload();            
            $this->load->model('pue_offline_model');

            $this->pue_offline_model->currUser = $currUser;
            $data = [
                'location_data' => $this->pue_offline_model->get_location_data($filter),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function index_get($idLocation, $id = null)
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
            
            $filter = [
                'datetime' => null,
                'idLocation' => $idLocation
            ];

            if(!$year) $year = date('Y');
            if($id) $filter['id'] = $id;
            
            $this->load->library('datetime_range');
            if($month) {
                $filter['datetime'] = $this->datetime_range->get_by_month($month, $year);
            } else {
                $filter['datetime'] = $this->datetime_range->get_by_year($year);
            }
            
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pue_offline_model');
            $this->pue_offline_model->currUser = $currUser;
            
            $data = [
                'pue' => $this->pue_offline_model->get($filter),
                'location' => $this->pue_offline_model->get_location($filter),
                'success' => true
            ];
        }

        $this->response($data, $status);
    }

    public function index_post()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('pue_offline_model');

            $month = $this->input->get('month');
            if(!$month) $month = date('n');

            $year = $this->input->get('year');
            if(!$year) $year = date('Y');

            if($year == date('Y') && $month == date('n')) {
                $timestamp = date('Y-m-d H:i:s');
            } else {
                $day = '01';
                $time = '00:00:00';
                $timestamp = date('Y-m-d H:i:s', strtotime("$year-$month-$day $time"));
            }

            $currUser = $this->auth_jwt->get_payload();
            $fields = [
                'id_location' => ['int', 'required'],
                'daya_sdp_a' => ['string', 'required'],
                'daya_sdp_b' => ['string', 'required'],
                'daya_sdp_c' => ['string', 'required'],
                'power_factor_sdp' => ['string', 'nullable'],
                'daya_eq_a' => ['string', 'required'],
                'daya_eq_b' => ['string', 'required'],
                'daya_eq_c' => ['string', 'nullable'],
                'evidence' => ['string', 'required']
            ];
            
            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $body = $input['body'];
            $body['created_at'] = $timestamp;
            $body['updated_at'] = $timestamp;
            
            $getIntStr = fn($str) => (int) preg_replace('/(^(0+)|[^0-9])/', '', $str);
            $getDecStr = fn($str) => (double) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $str));
            
            $body['daya_sdp_a'] = $getIntStr($body['daya_sdp_a']);
            $body['daya_sdp_b'] = $getIntStr($body['daya_sdp_b']);
            $body['daya_sdp_c'] = $getIntStr($body['daya_sdp_c']);
            $body['daya_eq_a'] = $getIntStr($body['daya_eq_a']);
            $body['daya_eq_b'] = $getIntStr($body['daya_eq_b']);
            if(isset($body['daya_eq_c'])) $body['daya_eq_c'] = $getIntStr($body['daya_eq_c']);
            if(isset($body['power_factor_sdp'])) $body['power_factor_sdp'] = $getDecStr($body['power_factor_sdp']);
            
            $this->pue_offline_model->currUser = $currUser;
            $success = $this->pue_offline_model->save($body);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('input new offline pue')
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }

    public function index_put($id)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $this->load->model('pue_offline_model');

            $currUser = $this->auth_jwt->get_payload();
            $fields = $this->pue_offline_model->get_updatable_fields();
            
            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $body = $input['body'];
            $body['updated_at'] = date('Y-m-d H:i:s');

            $getIntStr = fn($str) => (int) preg_replace('/(^(0+)|[^0-9])/', '', $str);
            $getDecStr = fn($str) => (double) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $str));
            
            $body['daya_sdp_a'] = $getIntStr($body['daya_sdp_a']);
            $body['daya_sdp_b'] = $getIntStr($body['daya_sdp_b']);
            $body['daya_sdp_c'] = $getIntStr($body['daya_sdp_c']);
            $body['daya_eq_a'] = $getIntStr($body['daya_eq_a']);
            $body['daya_eq_b'] = $getIntStr($body['daya_eq_b']);
            $body['daya_eq_c'] = isset($body['daya_eq_c']) ? $getIntStr($body['daya_eq_c']) : null;
            $body['power_factor_sdp'] = isset($body['power_factor_sdp']) ? $getDecStr($body['power_factor_sdp']) : null;

            $this->pue_offline_model->currUser = $currUser;
            $success = $this->pue_offline_model->save($body, $id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('update offline pue on id:'.$id)
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }

    public function index_delete($id)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->model('pue_offline_model');
            $currUser = $this->auth_jwt->get_payload();

            $this->pue_offline_model->currUser = $currUser;
            $success = $this->pue_offline_model->delete($id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('delete pue offline location on id:'.$id)
                    ->log();

            } else {
                $data = REST_ERR_BAD_REQ_STATUS;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        $this->response($data, $status);
    }
}