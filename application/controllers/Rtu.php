<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Rtu extends RestController
{
    function __construct()
    {
        parent::__construct();
		$this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    public function index_get($id = null)
    {
		$status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {

            $divreCode = $this->input->get('divre') ?? null;
            $witelCode = $this->input->get('witel') ?? null;

            $filters = [];
            if($divreCode) $filters['divre_kode'] = $divreCode;
            if($witelCode) $filters['witel_kode'] = $witelCode;

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model("rtu_map_model");

            if(!$id) $this->rtu_map_model->setFilter($filters);
			$dataRtu = $this->rtu_map_model->get($id, $currUser);

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get RTU list')
                ->log();

			$data = [ 'rtu' => $dataRtu ];
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

    public function add_post()
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
                'rtu_kode' => [ 'string', 'required' ],
                'rtu_name' => [ 'string', 'required' ],
                'lokasi' => [ 'string', 'required' ],
                'sto_kode' => [ 'string', 'required' ],
                'divre_kode' => [ 'string', 'required' ],
                'divre_name' => [ 'string', 'required' ],
                'witel_kode' => [ 'string', 'required' ],
                'witel_name' => [ 'string', 'required' ],
                'port_kwh' => [ 'string', 'required' ],
                'port_genset' => [ 'string', 'required' ],
                'kva_genset' => [ 'int', 'required' ],
                'port_pue' => [ 'string' ],
                'port_pue_v2' => [ 'string' ],
                'use_gepee' => [ 'bool', 'required' ],
                'id_lokasi_gepee' => [ 'int' ]
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
                ($currUser['level'] == 'witel' && $input['body']['witel_kode'] == $currUser['locationId'])
            ];
            
            if(!in_array(true, $levelValidations)) {
                $data = [ 'success' => false ];
                $status = REST_ERR_BAD_REQ;
            } else {
                $body = $input['body'];
                if(!$body['use_gepee']) {
                    $body['id_lokasi_gepee'] = null;
                }
                unset($body['use_gepee']);
            }
        }

        if($status === 200) {
            $this->load->model("rtu_map_model");
            try {
                $success = $this->rtu_map_model->save($body);
                if($success) {
                    $this->user_log
                        ->userId($currUser['id'])
                        ->username($currUser['username'])
                        ->name($currUser['name'])
                        ->activity('input new RTU')
                        ->log();
                    $data = [ 'success' => $success ];
                } else {
                    $status = REST_ERR_BAD_REQ_STATUS;
                    $data = REST_ERR_BAD_REQ_DATA;
                }
            } catch(FormValidationException $err) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = [
                    ...REST_ERR_BAD_REQ_DATA,
                    'message' => $err->getMessage(),
                    ...$err->getData()
                ];
            }
        }
        
        $this->response($data, $status);
    }

    public function update_put($id)
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
                'rtu_kode' => [ 'string', 'required' ],
                'rtu_name' => [ 'string', 'required' ],
                'lokasi' => [ 'string', 'required' ],
                'sto_kode' => [ 'string', 'required' ],
                'divre_kode' => [ 'string', 'required' ],
                'divre_name' => [ 'string', 'required' ],
                'witel_kode' => [ 'string', 'required' ],
                'witel_name' => [ 'string', 'required' ],
                'port_kwh' => [ 'string', 'required' ],
                'port_genset' => [ 'string', 'required' ],
                'kva_genset' => [ 'int', 'required' ],
                'port_pue' => [ 'string' ],
                'port_pue_v2' => [ 'string' ],
                'use_gepee' => [ 'bool', 'required' ],
                'id_lokasi_gepee' => [ 'int' ]
            ];

            $this->input_custom->set_fields($fields);
            $input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            } else {
                $body = $input['body'];
                if(!$body['use_gepee']) {
                    $body['id_lokasi_gepee'] = null;
                }
                unset($body['use_gepee']);
            }
        }

        if($status === 200) {
            $this->load->model("rtu_map_model");
            $currUser = $this->auth_jwt->get_payload();
            try {
                $success = $this->rtu_map_model->save($body, $id, $currUser);
                if($success) {
                    $this->user_log
                        ->userId($currUser['id'])
                        ->username($currUser['username'])
                        ->name($currUser['name'])
                        ->activity('update RTU '.$body['rtu_kode'])
                        ->log();
                    $data = [ 'success' => $success ];
                } else {
                    $status = REST_ERR_BAD_REQ_STATUS;
                    $data = REST_ERR_BAD_REQ_DATA;
                }
            } catch(FormValidationException $err) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = [
                    ...REST_ERR_BAD_REQ_DATA,
                    'message' => $err->getMessage(),
                    ...$err->getData()
                ];
            }
        }
        
        $this->response($data, $status);
    }

	public function del_delete($id)
	{
		$status = $this->auth_jwt->auth('admin');
        if($status === 200) {
			
			$this->load->model("rtu_map_model");
            $currUser = $this->auth_jwt->get_payload();
			$success = $this->rtu_map_model->delete($id, $currUser);

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('delete RTU item')
                ->log();
			$data = [ 'success' => $success ];
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