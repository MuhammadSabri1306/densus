<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Rtu extends RestController
{
    function __construct()
    {
        parent::__construct();
		$this->load->library('auth_jwt');
    }

    public function index_get($id = null)
    {
		$status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {

            $currUser = $this->auth_jwt->get_payload();
            $this->load->model("rtu_map_model");
			$dataRtu = $this->rtu_map_model->get($id, $currUser);

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
        $this->load->library('input_handler');
		$status = $this->auth_jwt->auth('admin');
		if($status === 200) {
			$this->input_handler->set_fields('rtu_kode', 'rtu_name', 'lokasi', 'sto_kode', 'divre_kode', 'divre_name', 'witel_kode', 'witel_name', 'port_kwh', 'port_genset', 'kva_genset');
            $this->input_handler->set_required('rtu_kode', 'rtu_name', 'lokasi', 'sto_kode', 'divre_kode', 'divre_name', 'witel_kode', 'witel_name', 'port_kwh', 'port_genset', 'kva_genset');

			$input = $this->input_handler->get_body('post');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ;
            }

            $currUser = $this->auth_jwt->get_payload();
            $levelValidations = [
                $currUser['level'] == 'nasional',
                ($currUser['level'] == 'divre' && $input['body']['divre_kode'] == $currUser['locationId']),
                ($currUser['level'] == 'witre' && $input['body']['witel_kode'] == $currUser['locationId'])
            ];
            if(!in_array(true, $levelValidations)) {
                $data = [ 'success' => false ];
                $status = REST_ERR_BAD_REQ;
            }
            
            if($status == 200){
                $this->load->model("rtu_map_model");
				$success = $this->rtu_map_model->save($input['body']);
                $data = [ 'success' => $success ];
			}
            
            $this->response($data, $status);

        } else {

            switch($status) {
                case REST_ERR_AUTH_CODE: $data = REST_ERR_AUTH_DATA; break;
                case REST_ERR_EXP_CODE: $data = REST_ERR_EXP_DATA; break;
                default: $data = null;
            }
            $this->response($data, $status);

        }
    }

    public function update_put($id)
    {
        $this->load->library('input_handler');
		$status = $this->auth_jwt->auth('admin');
		if($status === 200) {
			$this->input_handler->set_fields('rtu_kode', 'rtu_name', 'lokasi', 'sto_kode', 'divre_kode', 'divre_name', 'witel_kode', 'witel_name', 'port_kwh', 'port_genset', 'kva_genset');
            $this->input_handler->set_required('rtu_kode', 'rtu_name', 'lokasi', 'sto_kode', 'divre_kode', 'divre_name', 'witel_kode', 'witel_name', 'port_kwh', 'port_genset', 'kva_genset');

			$input = $this->input_handler->get_body('put');
            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
				$this->response($data, REST_ERR_BAD_REQ);
            } else {
                $this->load->model("rtu_map_model");
                $currUser = $this->auth_jwt->get_payload();
				$success = $this->rtu_map_model->save($input['body'], $id, $currUser);
				$this->response([ 'success' => $success ], 200);
			}

        } else {

            switch($status) {
                case REST_ERR_AUTH_CODE: $data = REST_ERR_AUTH_DATA; break;
                case REST_ERR_EXP_CODE: $data = REST_ERR_EXP_DATA; break;
                default: $data = null;
            }
            $this->response($data, $status);

        }
    }

	public function del_delete($id)
	{
		$status = $this->auth_jwt->auth('admin');
        if($status === 200) {
			
			$this->load->model("rtu_map_model");
            $currUser = $this->auth_jwt->get_payload();
			$success = $this->rtu_map_model->delete($id, $currUser);
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