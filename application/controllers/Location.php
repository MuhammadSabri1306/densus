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

    // --------------------------- NEWOSASE

    public function divre_newosase_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {

            $witelCode = $this->input->get('witel') ?? null;

            $this->load->model('master_non_location_model');
            $dataDivre = !$witelCode ? $this->master_non_location_model->get_divre()
                : $this->master_non_location_model->find_divre_by_witel($witelCode);

			$data = [
                'divre' => $dataDivre,
                'success' => true
            ];

        }

        $this->response($data, $status);
    }

    public function witel_by_divre_newosase_get($divreCode)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {

            $this->load->model('master_non_location_model');
			$data = [
                'witel' => $this->master_non_location_model->get_witel_by_divre($divreCode),
                'success' => true
            ];

        }

        $this->response($data, $status);
    }

    // ---------------------------------- NEWOSASE

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

    public function gepee_sto_get($divreCode = null, $witelCode = null)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {
            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode
            ];

            $this->load->model('lokasi_gepee_model');
			$dataSto = $this->lokasi_gepee_model->get_all($filter);

			$data = [ 'sto' => $dataSto, 'success' => true ];
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

    // sssssssssssssssssssssss
    public function sto_master_divre_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('master_sto_densus_model');
            $this->master_sto_densus_model->currUser = $currUser;
			$dataDivre = $this->master_sto_densus_model->get_divre();

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

    public function sto_master_witel_get($witelCode = null)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('master_sto_densus_model');
            $this->master_sto_densus_model->currUser = $currUser;
			$dataWitel = $this->master_sto_densus_model->get_witel([ 'witel' => $witelCode ]);

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

    public function sto_master_witel_by_divre_get($divreCode)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('master_sto_densus_model');
            $this->master_sto_densus_model->currUser = $currUser;
			$dataWitel = $this->master_sto_densus_model->get_witel([ 'divre' => $divreCode ]);

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

    public function sto_master_sto_get($divreCode = null, $witelCode = null)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {
            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode
            ];

            $this->load->model('master_sto_densus_model');
            $this->master_sto_densus_model->currUser = $currUser;
			$dataSto = $this->master_sto_densus_model->get($filter);

			$data = [ 'sto' => $dataSto, 'success' => true ];
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