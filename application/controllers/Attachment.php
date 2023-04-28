<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Attachment extends RestController
{
    private $inputName = 'file';
    private $targetPath;
    private $allowedTypes;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    private function create_filename()
    {
        $fileInfo = pathinfo($_FILES[$this->inputName]['name']);
        $filename = str_replace('.', '', $fileInfo['filename']);
        $filename = str_replace(' ', '', $filename);
        $filename .= '_' . time() . '.' . $fileInfo['extension'];
        return $filename;
    }

    private function upload()
    {
        $config = [
            'upload_path' => FCPATH . $this->targetPath,
            'allowed_types' => $this->allowedTypes,
            'file_name' => $this->create_filename()
        ];

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if(!$this->upload->do_upload($this->inputName)) {
            $status = REST_ERR_BAD_REQ_STATUS;
            $data = [
                'success' => false,
                'message' => $this->upload->display_errors('', '')
            ];
        } else {
            $status = 200;
            $data = [
                'success' => true,
                'uploadedFile' => $this->upload->data()
            ];
        }

        return [ 'data' => $data, 'status' => $status ];
    }
    
    private function delete_uploaded($filename)
    {
        $filePath = FCPATH . $this->targetPath . '/' . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
            return true;
        } else {
            return false;
        }
    }

    public function store_activity_execution_post()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->allowedTypes = 'jpg|jpeg|png|pdf';
            $this->targetPath = UPLOAD_ACTIVITY_EVIDENCE_PATH;
            $result = $this->upload();
            $status = $result['status'];
            $data = $result['data'];
        }

        $this->response($data, $status);
    }

    public function del_activity_execution_delete($filename)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->targetPath = UPLOAD_ACTIVITY_EVIDENCE_PATH;
            $isSuccess = $this->delete_uploaded($filename);
            if($isSuccess) {
                $data = [ 'success' => true ];
            } else {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = [ 'success' => false, 'message' => 'Gagal menghapus file:' . $filename ];
            }
        }

        $this->response($data, $status);
    }

    public function store_gepee_evidence_post()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->allowedTypes = 'jpg|jpeg|png|pdf';
            $this->targetPath = UPLOAD_GEPEE_EVIDENCE_PATH;

            $result = $this->upload();
            $status = $result['status'];
            $data = $result['data'];
        }

        $this->response($data, $status);
    }

    public function del_gepee_evidence_delete($filename)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $this->targetPath = UPLOAD_GEPEE_EVIDENCE_PATH;
            $isSuccess = $this->delete_uploaded($filename);
            if($isSuccess) {
                $data = [ 'success' => true ];
            } else {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = [ 'success' => false, 'message' => 'Gagal menghapus file:' . $filename ];
            }
        }

        $this->response($data, $status);
    }

    public function store_pue_evidence_post()
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            if($currUser['level'] != 'nasional') {
                $status = REST_ERR_UNAUTH_STATUS;
                $data = REST_ERR_UNAUTH_DATA;
            }
        }

        if($status === 200) {
            $this->allowedTypes = 'jpg|jpeg|png|pdf';
            $this->targetPath = UPLOAD_PUE_EVIDENCE_PATH;

            $result = $this->upload();
            $status = $result['status'];
            $data = $result['data'];
        }

        $this->response($data, $status);
    }

    public function del_pue_evidence_delete($filename)
    {
        $status = $this->auth_jwt->auth('admin');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            if($currUser['level'] != 'nasional') {
                $status = REST_ERR_UNAUTH_STATUS;
                $data = REST_ERR_UNAUTH_DATA;
            }
        }

        if($status === 200) {
            $this->targetPath = UPLOAD_PUE_EVIDENCE_PATH;
            $isSuccess = $this->delete_uploaded($filename);
            if($isSuccess) {
                $data = [ 'success' => true ];
            } else {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = [ 'success' => false, 'message' => 'Gagal menghapus file:' . $filename ];
            }
        }

        $this->response($data, $status);
    }
}