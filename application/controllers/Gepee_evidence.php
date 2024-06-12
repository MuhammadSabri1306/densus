<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Gepee_evidence extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    public function location_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }     

        if($status === 200) {
            $divreCode = $this->input->get('divre');
            $witelCode = $this->input->get('witel');
            $idLocation = $this->input->get('idLocation');
            $year = $this->input->get('year');
            $semester = $this->input->get('semester');

            if(!$year) $year = date('Y');
            if(!$semester) $semester = 1;

            $this->load->library('datetime_range');
            list($startDate, $endDate) = $this->datetime_range->get_by_semester($semester, $year);
            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode,
                'idLocation' => $idLocation,
                'datetime' => [$startDate, $endDate]
            ];

            $currUser = $this->auth_jwt->get_payload();            
            $this->load->model('gepee_evidence_model');

            $this->gepee_evidence_model->currUser = $currUser;
            $location = $this->gepee_evidence_model->get_location_list($filter);

            $data = [
                'location' => $location,
                'success' => true
            ];

            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get gepee evidence location list')
                ->log();
        }
        
        $this->response($data, $status);
    }

    public function location_info_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }     

        if($status === 200) {
            $divreCode = $this->input->get('divre');
            $witelCode = $this->input->get('witel');
            $idLocation = $this->input->get('idLocation');
            $year = $this->input->get('year');
            $semester = $this->input->get('semester');

            if(!$year) $year = date('Y');
            if(!$semester) $semester = 1;

            list($startDate, $endDate) = datetime_range_semester($semester, $year);
            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode,
                'idLocation' => $idLocation,
                'datetime' => [$startDate, $endDate]
            ];

            $currUser = $this->auth_jwt->get_payload();            
            $this->load->model('gepee_evidence_model');

            $this->gepee_evidence_model->currUser = $currUser;
            $count = $this->gepee_evidence_model->get_location_detail($filter);
            $location = $this->gepee_evidence_model->get_request_level($filter);

            if($witelCode) {
                $score = $this->gepee_evidence_model->get_location_score($filter);
            } else {
                $score = $this->gepee_evidence_model->get_avg_score($filter);
            }

            $data = [
                'count' => $count,
                'location' => $location,
                'score' => $score,
                'success' => true
            ];
        }
        
        $this->response($data, $status);
    }

    public function category_get($code = null)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $this->load->model('gepee_evidence_model');
            $data = [
                'category' => $this->gepee_evidence_model->get_category($code),
                'success' => true
            ];
        }
        
        $this->response($data, $status);
    }

    public function category_data_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }     

        if($status === 200) {
            $divreCode = $this->input->get('divre');
            $witelCode = $this->input->get('witel');
            $idLocation = $this->input->get('idLocation');
            $year = $this->input->get('year');
            $semester = $this->input->get('semester');

            if(!$year) $year = date('Y');
            if(!$semester) $semester = 1;

            list($startDate, $endDate) = datetime_range_semester($semester, $year);
            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode,
                'idLocation' => $idLocation,
                'datetime' => [$startDate, $endDate]
            ];

            $currUser = $this->auth_jwt->get_payload();            
            $this->load->model('gepee_evidence_model');

            $this->gepee_evidence_model->currUser = $currUser;
            $data = [
                'category_data' => $this->gepee_evidence_model->get_category_data($filter),
                'success' => true
            ];
        }
        
        $this->response($data, $status);
    }

    public function evidence_list_get($idCategory)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }     

        if($status === 200) {
            $divreCode = $this->input->get('divre');
            $witelCode = $this->input->get('witel');
            $idLocation = $this->input->get('idLocation');
            $year = $this->input->get('year');
            $semester = $this->input->get('semester');

            if(!$year) $year = date('Y');
            if(!$semester) $semester = 1;

            list($startDate, $endDate) = datetime_range_semester($semester, $year);
            $filter = [
                'divre' => $divreCode,
                'witel' => $witelCode,
                'idLocation' => $idLocation,
                'datetime' => [$startDate, $endDate]
            ];

            $currUser = $this->auth_jwt->get_payload();            
            $this->load->model('gepee_evidence_model');

            $this->gepee_evidence_model->currUser = $currUser;
            $data = [
                'evidence' => $this->gepee_evidence_model->get_evidence_list($idCategory, $filter),
                'location' => $this->gepee_evidence_model->get_request_level($filter),
                'category' => $this->gepee_evidence_model->get_category_by_id($idCategory),
                'success' => true
            ];
        }
        
        $this->response($data, $status);
    }

    public function evidence_get($id)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }     

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();            
            $this->load->model('gepee_evidence_model');
            $this->gepee_evidence_model->currUser = $currUser;

            $data = [
                'evidence' => $this->gepee_evidence_model->get_evidence_by_id($id),
                'success' => true
            ];
        }
        
        $this->response($data, $status);
    }

    public function evidence_post()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $this->load->library('input_custom');
            $fields = [
                'id_location' => ['int', 'required'],
                'id_category' => ['int', 'required'],
                'deskripsi' => ['string', 'required'],
                'file' => ['string', 'required']
            ];

            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('post');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            } else {
                $body = $input['body'];
                $body['created_at'] = date('Y-m-d H:i:s');
                $body['updated_at'] = $body['created_at'];
            }
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('gepee_evidence_model');
            $this->gepee_evidence_model->currUser = $currUser;
            
            $success = $this->gepee_evidence_model->save($body);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('input new gepee evidence')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }
        
        $this->response($data, $status);
    }

    public function evidence_put($id)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        
        if($status === 200) {
            $this->load->library('input_custom');
            $fields = [
                'deskripsi' => ['string', 'required'],
                'file' => ['string', 'required']
            ];

            $this->input_custom->set_fields($fields);
			$input = $this->input_custom->get_body('put');

            if(!$input['valid']) {
                $data = [ 'success' => false, 'message' => $input['msg'] ];
                $status = REST_ERR_BAD_REQ_STATUS;
            } else {
                $body = $input['body'];
                $body['updated_at'] = date('Y-m-d H:i:s');
            }
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('gepee_evidence_model');
            $this->gepee_evidence_model->currUser = $currUser;

            $success = $this->gepee_evidence_model->save($body, $id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('update gepee evidence')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }
        
        $this->response($data, $status);
    }

    public function evidence_delete($id)
	{
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('gepee_evidence_model');
            $this->gepee_evidence_model->currUser = $currUser;
            
            $success = $this->gepee_evidence_model->delete($id);
            if($success) {

                $data = [ 'success' => true ];
                $this->user_log
                    ->userId($currUser['id'])
                    ->username($currUser['username'])
                    ->name($currUser['name'])
                    ->activity('delete gepee evidence')
                    ->log();

            } else {

                $status = REST_ERR_BAD_REQ_STATUS;
                $data = REST_ERR_BAD_REQ_DATA;

            }
        }

        $this->response($data, $status);
	}
}