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

    public function gepee_post()
    {
		$status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->load->library('input_custom');
            $fields = [
                'id_pel_pln' => ['string', 'required'],
                'nama_pel_pln' => ['string', 'required'],
                'tarif_pel_pln' => ['string', 'required'],
                'daya_pel_pln' => ['int', 'required'],
                'lokasi_pel_pln' => ['string', 'nullable'],
                'alamat_pel_pln' => ['string', 'nullable'],
                'gedung' => ['string', 'nullable'],
                'divre_kode' => ['string', 'required'],
                'divre_name' => ['string', 'required'],
                'witel_kode' => ['string', 'required'],
                'witel_name' => ['string', 'required'],
                'sto_kode' => ['string', 'nullable'],
                'sto_name' => ['string', 'nullable'],
                'tipe' => ['string', 'nullable'],
                'rtu_kode' => ['string', 'nullable'],
            ];

            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('post');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $levelValidations = [
                $currUser['level'] == 'nasional',
                ($currUser['level'] == 'divre' && $input['body']['divre_kode'] == $currUser['locationId']),
                ($currUser['level'] == 'witre' && $input['body']['witel_kode'] == $currUser['locationId'])
            ];
            if(!in_array(true, $levelValidations)) {
                $data = REST_ERR_BAD_REQ_DATA;
                $status = REST_ERR_BAD_REQ_STATUS;
            }
        }

		if($status === 200) {
            $this->load->model('lokasi_gepee_model');
            $success = $this->lokasi_gepee_model->save($input['body']);

            if($success) {
                $data = [ 'success' => true ];
            } else {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;
            }
        }

        
        if($status === 200) {
            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('input new pln billing')
                ->log();
        }

        $this->response($data, $status);
    }
}