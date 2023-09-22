<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Attachment extends RestController
{
    private $inputName = 'file';
    private $targetPath;
    private $allowedTypes;
    private $maxSize = 2048;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
        $this->load->library('user_log');
    }

    private function create_filename($srcFilename)
    {
        $fileInfo = pathinfo($srcFilename);
        $filename = str_replace('.', '', $fileInfo['filename']);
        $filename = str_replace(' ', '', $filename);
        $filename .= '_' . time() . '.' . $fileInfo['extension'];
        return $filename;
    }

    private function upload_files()
    {
        $srcFilename = $_FILES[$this->inputName]['name'];
        $destFilename = $this->create_filename($srcFilename);

        $config = [
            'upload_path' => FCPATH . $this->targetPath,
            'allowed_types' => $this->allowedTypes,
            'file_name' => $destFilename,
            'max_size' => $this->maxSize
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
            $uploadedFile = $this->upload->data();
            $uploadedFile['uploaded_url'] = base_url($this->targetPath).$uploadedFile['file_name'];

            $data = [
                'success' => true,
                'uploadedFile' => $uploadedFile
            ];
        }

        return [ 'data' => $data, 'status' => $status ];
    }
    
    private function delete_uploaded($filename)
    {
        $filePath = FCPATH . $this->targetPath . '/' . $filename;
        if (file_exists($filePath)) {
            return unlink($filePath);
        } else {
            return false;
        }
    }

    private function get_uploaded_list()
    {
        $path = FCPATH . $this->targetPath;
        $files = array_diff(scandir($path), array('.', '..'));
        return $files;
    }

    public function check_activity_execution_get()
    {
        $this->targetPath = UPLOAD_ACTIVITY_EVIDENCE_PATH;
        $fromDir = $this->get_uploaded_list();

        $this->load->model('activity_execution_model');
        $fromDb = $this->activity_execution_model->get_file_list();

        $result = [
            'count_from_db' => count($fromDb),
            'count_from_dir' => count($fromDir)
        ];
        // dd($result);

        $diffFileList = array_diff($fromDir, $fromDb);
        // dd(count($diffFileList));
        dd($diffFileList);

        $result = [];
        foreach($diffFileList as $filename) {
            // $isSuccess = $this->delete_uploaded($filename);
            array_push($result, [
                'success' => $isSuccess,
                'filename' => $filename
            ]);
        }
        foreach($result as $item) {
            if(!$item['success']) var_dump($item);
        }
        dd(count($result));
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
            $this->maxSize = 4096;

            $result = $this->upload_files();
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
            $this->maxSize = 4096;

            $result = $this->upload_files();
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
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        // if($status === 200) {
        //     $currUser = $this->auth_jwt->get_payload();
        //     if($currUser['level'] != 'nasional') {
        //         $status = REST_ERR_UNAUTH_STATUS;
        //         $data = REST_ERR_UNAUTH_DATA;
        //     }
        // }

        if($status === 200) {
            $this->allowedTypes = 'jpg|jpeg|png|pdf';
            $this->targetPath = UPLOAD_PUE_EVIDENCE_PATH;
            $this->maxSize = 4096;

            $result = $this->upload_files();
            $status = $result['status'];
            $data = $result['data'];
        }

        $this->response($data, $status);
    }

    public function del_pue_evidence_delete($filename)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
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

    public function store_oxisp_evidence_post()
    {
        $status = $this->auth_jwt->auth('viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->allowedTypes = 'jpg|jpeg|png|pdf';
            $this->targetPath = UPLOAD_OXISP_EVIDENCE_PATH;
            $this->maxSize = 4096;

            $result = $this->upload_files();
            $status = $result['status'];
            $data = $result['data'];
        }

        $this->response($data, $status);
    }

    public function del_oxisp_evidence_delete($filename)
    {
        $status = $this->auth_jwt->auth('viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->targetPath = UPLOAD_OXISP_EVIDENCE_PATH;
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

    public function store_oxisp_check_evidence_post()
    {
        $status = $this->auth_jwt->auth('viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->allowedTypes = 'jpg|jpeg|png|pdf';
            $this->targetPath = UPLOAD_OXISP_CHECK_EVIDENCE_PATH;
            $this->maxSize = 4096;

            $result = $this->upload_files();
            $status = $result['status'];
            $data = $result['data'];
        }

        $this->response($data, $status);
    }

    public function del_oxisp_check_evidence_delete($filename)
    {
        $status = $this->auth_jwt->auth('viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }

        if($status === 200) {
            $currUser = $this->auth_jwt->get_payload();
            $this->targetPath = UPLOAD_OXISP_CHECK_EVIDENCE_PATH;
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