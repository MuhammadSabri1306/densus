<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Rtu extends RestController
{
    function __construct()
    {
        parent::__construct();
    }

    public function index_get($id = null)
    {
        $this->load->model("rtu_map_model");
        $dataRtu = $this->rtu_map_model->get($id);

        $data = [ 'rtu' => $dataRtu ];
        $this->response($data, 200);
    }

    public function add_post()
    {
		$this->load->model("rtu_map_model");
        $body = [
			'rtu_kode' => $this->post('rtuCode'),
			'rtu_name' => $this->post('rtuName'),
			'lokasi' => $this->post('location'),
			'sto_kode' => $this->post('stoCode'),
			'divre_kode' => $this->post('divreCode'),
			'divre_name' => $this->post('divreName'),
			'witel_kode' => $this->post('witelCode'),
			'witel_name' => $this->post('witelName'),
			'port_kwh' => $this->post('portKwh'),
			'port_genset' => $this->post('portGenset'),
			'kva_genset' => $this->post('kvaGenset'),
		];
		
		$success = $this->rtu_map_model->save($body);
        $data = [ 'success' => $success, 'data' => $data ];
        $this->response($data, 200);
    }

    public function update_put($id)
    {
		$this->load->model("rtu_map_model");
        $body = [
			'rtu_kode' => $this->put('rtuCode'),
			'rtu_name' => $this->put('rtuName'),
			'lokasi' => $this->put('location'),
			'sto_kode' => $this->put('stoCode'),
			'divre_kode' => $this->put('divreCode'),
			'divre_name' => $this->put('divreName'),
			'witel_kode' => $this->put('witelCode'),
			'witel_name' => $this->put('witelName'),
			'port_kwh' => $this->put('portKwh'),
			'port_genset' => $this->put('portGenset'),
			'kva_genset' => $this->put('kvaGenset'),
		];
        
        // $success = $this->rtu_map_model->save($body, $id);
		$success = true;
        $data = [ 'success' => $success, 'data' => $body ];
        $this->response($data, 200);
    }
}