<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Bot_operation extends RestController
{
    public function index_get()
    {
        $privateKey = $this->input->get('key');
        $input = $this->input->get('command');
        $status = 200;

        if($privateKey !== '@SabriDevMks1237450q') {
            $status = REST_ERR_UNAUTH_STATUS;
            $data = [
                'success' => false,
                'message' => 'This endpoint isn\'t accessible'
            ];
        }

        switch($input) {
            case 'node-list':
                $output = `pgrep -a node`;
                break;
            default:
                $status = REST_ERR_DEFAULT_STATUS;
                $data = REST_ERR_DEFAULT_DATA;
                break;
        }

        if($status === 200) {
            $data = [
                'output' => $output,
                'success' => true
            ];
        }

        $this->response($data, $status);
    }
}