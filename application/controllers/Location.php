<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Location extends RestController
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
            $this->load->model('rtu_list_model');
			$dataDivre = $this->rtu_list_model->get_divre(null, $currUser);

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
            $this->load->model('rtu_list_model');
			$dataWitel = $this->rtu_list_model->get_witel(null, $witelCode, $currUser);

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
            $this->load->model('rtu_list_model');
			$dataWitel = $this->rtu_list_model->get_witel($divreCode, null, $currUser);

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

    public function gepee_divre_get()
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

    public function gepee_witel_get($witelCode = null)
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

    public function gepee_witel_by_divre_get($divreCode)
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
}