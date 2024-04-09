<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Monitoring extends RestController
{
    private $bbmprice = 28440;
    private $biayalwbp = 1091;
    private $biayawbp = 1609;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_jwt');
    }

    public function rtulist_v2_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        if($status === 200) {

            $divreCode = $this->input->get('divre') ?? null;
            $witelCode = $this->input->get('witel') ?? null;

            $filters = [];
            if($divreCode) $filters['divre_kode'] = $divreCode;
            if($witelCode) $filters['witel_kode'] = $witelCode;

            $this->load->model("rtu_map_model");
            $this->rtu_map_model->setCurrUser( $this->auth_jwt->get_payload() );
            $rtu = [];
            try {
                $rtu = $this->rtu_map_model->get_newosase_rtus($divreCode, $witelCode);
            } catch(ModelEmptyDataException $err) {
                $status = REST_ERR_BAD_REQ_STATUS;
                $data = [
                    ...REST_ERR_BAD_REQ_DATA,
                    'message' => $err->getMessage(),
                    ...$err->getData()
                ];
            }

            $data = [
                'rtu' => $rtu,
                'success' => true
            ];

        }

        $this->response($data, $status);
    }

    public function rtu_get($rtuCode)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        switch($status) {
            case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
            case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
            default: $data = REST_ERR_DEFAULT_DATA; break;
        }
        if($status === 200) {

            $this->load->model("monitoring_rtu_model");
            $this->monitoring_rtu_model->setCurrUser( $this->auth_jwt->get_payload() );
            $data = [
                'rtu' => $this->monitoring_rtu_model->find_rtu($rtuCode),
                'success' => true
            ];

        }
        $this->response($data, $status);
    }

    public function energy_cost_get($rtuCode)
    {
        // $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        // switch($status) {
        //     case REST_ERR_EXP_TOKEN_STATUS: $data = REST_ERR_EXP_TOKEN_DATA; break;
        //     case REST_ERR_UNAUTH_STATUS: $data = REST_ERR_UNAUTH_DATA; break;
        //     default: $data = REST_ERR_DEFAULT_DATA; break;
        // }
        $status = 200;
        if($status === 200) {

            $this->load->model("monitoring_rtu_model");
            // $this->monitoring_rtu_model->setCurrUser( $this->auth_jwt->get_payload() );
            $this->monitoring_rtu_model->setCurrUser([
                'id' => 1,
                'username' => 'dev123',
                'name' => 'Developer',
                'role' => 'admin',
                'level' => 'nasional',
                'location' => 'nasional',
                'locationId' => null,
            ]);

            try {
                $data = [
                    ...$this->monitoring_rtu_model->get_energy_cost_data($rtuCode),
                    'bbm_price' => Monitoring_rtu_model::BBM_PRICE,
                    'genset_bbm_rate' => Monitoring_rtu_model::BBM_CSM_RATE,
                    'kwh_lwbp_cost' => Monitoring_rtu_model::LWBP_COST,
                    'kwh_wbp_cost' => Monitoring_rtu_model::WBP_COST,
                    'success' => true
                ];
            } catch(ModelEmptyDataException $err) {
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

    public function costbbm_get($rtu)
    {
        $this->load->model('rtu_chart_model');
        $lokasidata = $this->rtu_chart_model->get_lokasidata($rtu);
        $port_genset = $lokasidata['port_genset'];
        $kva_genset = $lokasidata['kva_genset'];

        $data = $this->rtu_chart_model->get_bbmcost($rtu, $kva_genset, $port_genset, $this->bbmprice);
        // $data [ 'status' => 200 ]
        $this->response($data, 200);
    }

    public function performance_get($rtu)
    {
        $this->load->model('rtu_chart_model');
        $data['performance'] = $this->rtu_chart_model->get_kwhdata($rtu);

        $this->response($data, 200);
    }

    public function kwhreal_get($rtu)
    {
        $this->load->model('rtu_chart_model');
        $lokasidata = $this->rtu_chart_model->get_lokasidata($rtu);
        $port = $lokasidata['port_kwh'];

        $data['kwh_real'] = $this->rtu_chart_model->get_realkwh($rtu, $port);
        $this->response($data, 200);
    }
    
    public function savingpercent_get($rtu)
    {
        $this->load->model('rtu_chart_model');
        $data['savingpercent'] = $this->rtu_chart_model->get_savingpercent($rtu);
        $this->response($data, 200);
    }
    
    public function kwhtotal_get($rtu)
    {
        $this->load->model('rtu_chart_model');
        $data['kwh_total']=$this->rtu_chart_model->get_kwhtotal($rtu);
        $this->response($data, 200);
    }
    
    public function kwhtoday_get($rtu)
    {
        $this->load->model('rtu_chart_model');
        $data['kwh_today']=$this->rtu_chart_model->get_kwhtoday($rtu);
        $this->response($data, 200);
    }

    public function totalalarm_get($rtu)
    {
        $this->load->model('rtu_chart_model');
        $data['total_alarm']=$this->rtu_chart_model->get_totalAlarm($rtu);
        $this->response($data, 200);
    }
    
    public function tabledata_get($rtu)
    {
        $this->load->model('rtu_chart_model');
        $data['tabledata'] = $this->rtu_chart_model->get_tabledata($rtu, $this->biayalwbp, $this->biayawbp);
        $this->response($data, 200);
    }
    
    public function degtabledata_get($rtu)
    {
        $this->load->model('rtu_chart_model');

        $lokasidata = $this->rtu_chart_model->get_lokasidata($rtu);
        $port_genset = $lokasidata['port_genset'];
        $kva_genset = $lokasidata['kva_genset'];
        $data['degtabledata'] = $this->rtu_chart_model->get_deg_tabledata($rtu, $kva_genset, $port_genset, $this->bbmprice);

        $this->response($data, 200);
    }
    
    public function chartdatadaily_get($rtu)
    {
        $this->load->model('rtu_chart_model');
        $data['chartdata_daily'] = $this->rtu_chart_model->get_chartdata($rtu, "daily");
        $this->response($data, 200);
    }

    public function divre_get()
    {
        $this->load->model('rtu_list_model');
        $divre = $this->rtu_list_model->get_divre();
        $data = [ 'divre' => $divre ];
        $this->response($data, 200);
    }

    public function witel_get($divreCode)
    {
        $status = $this->auth_jwt->auth('admin');
		if($status === 200) {
            
            $this->load->model('rtu_list_model');
            $data = [ 'witel' => $this->rtu_list_model->get_witel_by_divre($divreCode) ];
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

    public function location_get($witelCode)
    {
        $this->load->model('rtu_list_model');
        $location = $this->rtu_list_model->get_divre_by_witel($witelCode);
        $data = [ 'location' => $location ];
        $this->response($data, 200);
    }

    public function rtulist_get($divreCode, $witelCode)
    {
        $this->load->model('rtu_map_model');
        $rtuMap = $this->rtu_map_model->get();

        $availableRtu = array_map(function($item) {
            return $item->rtu_kode;
        }, $rtuMap);
        
        $envPattern = EnvPattern::getPattern();
        $asoseUrl = "$envPattern->api_osase&divre=$divreCode&witel=$witelCode";
        $content = json_decode(file_get_contents($asoseUrl));

        $data = [ 'rtu' => [] ];
        foreach($content as $item) {
            $temp = $item;
            $temp->AVAILABLE = in_array($item->RTU_ID, $availableRtu);
            array_push($data['rtu'], $temp);
        }

        $this->response($data, 200);
    }

    public function rtudetail_get($rtuCode)
    {
        $envPattern = EnvPattern::getPattern();
        $asoseUrl = "$envPattern->api_osase&rtuid=$rtuCode";
        $content = json_decode(file_get_contents($asoseUrl));
        
        $rtu = (count($content) > 0) ? $content[0] : null;
        $data = [ 'success' => true, 'rtu' => $rtu ];
        $this->response($data, 200);
    }

    public function rtu_list_by_pue_get()
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        $divreCode = $this->input->get('divre');
        $witelCode = $this->input->get('witel');
        if($status === 200) {
            
            $currUser = $this->auth_jwt->get_payload();
            $filter = [];
            if($divreCode) $filter['divre'] = $divreCode;
            if($witelCode) $filter['witel'] = $witelCode;

            $this->load->model('rtu_map_model');
            $rtuMap = $this->rtu_map_model->getByPue($filter, $currUser);

            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get rtu:list by pue')
                ->log();
            
            $availableRtu = array_map(function($item) {
                return $item->rtu_kode;
            }, $rtuMap);
            
            $envPattern = EnvPattern::getPattern();
            $asoseUrl = "$envPattern->api_osase&divre=$divreCode&witel=$witelCode";
            $content = json_decode(file_get_contents($asoseUrl));
            
            $data = [ 'rtu' => [], 'success' => true ];
            foreach($content as $item) {
                $temp = $item;
                $temp->AVAILABLE = in_array($item->RTU_ID, $availableRtu);
                array_push($data['rtu'], $temp);
            }
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

    public function pue_get($rtuCode)
    {
        $status = $this->auth_jwt->auth('admin', 'viewer', 'teknisi');
        if($status === 200) {
            
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pue_counter_model');

            $valueOnYear = $this->pue_counter_model->get_on_curr_year($rtuCode); // 1 & 4
            $averages = $this->pue_counter_model->get_avg_value($rtuCode); // 2
            $maxValue = $this->pue_counter_model->get_max_value($rtuCode); // 3
			$currValue = $this->pue_counter_model->get_curr_value($rtuCode); // 5
            $performances = $this->pue_counter_model->get_performance_value($rtuCode); // 6

            $this->load->library('user_log');
            $this->user_log
                ->userId($currUser['id'])
                ->username($currUser['username'])
                ->name($currUser['name'])
                ->activity('get rtu:list by pue')
                ->log();
            
			$data = [
                'pue' => compact('valueOnYear', 'averages', 'maxValue', 'currValue', 'performances'),
                'success' => true
            ];
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

    public function pue_v2_get()
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
            $rtuCode = $this->input->get('rtu');
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pue_counter2_model');

            $pue = [];
            $pueKeys = ['valueOnYear', 'avgValueOnYear', 'stoValueOnYear', 'averages', 'maxValue', 'latestValue', 'latestAvgValueOnZone', 'performances'];
            foreach($pueKeys as $key) {
                $pue[$key] = null;
            }

            if($rtuCode) {

                $zone = [ 'rtu' => $rtuCode ];
                $pue['valueOnYear'] = $this->pue_counter2_model->get_curr_year($rtuCode);
                $pue['latestValue'] = $this->pue_counter2_model->get_latest_value($rtuCode);

            } else {

                $zone = [
                    'witel' => $currUser['level'] == 'witel' ? $currUser['locationId'] : $witelCode,
                    'divre' => $currUser['level'] == 'divre' ? $currUser['locationId'] : $divreCode
                ];
                
                $pue['avgValueOnYear'] = $this->pue_counter2_model->get_zone_avg_on_curr_year($zone);
                $pue['stoValueOnYear'] = $this->pue_counter2_model->get_curr_year_avg_on_sto($zone);
                $pue['latestAvgValueOnZone'] = $this->pue_counter2_model->get_latest_avg_value_on_zone($zone);

            }

            $pue['averages'] = $this->pue_counter2_model->get_avg_value($zone);
            $pue['maxValue'] = $this->pue_counter2_model->get_max_value($zone);
            $pue['performances'] = $this->pue_counter2_model->get_performance_value($zone);

            $dataLevel = $this->pue_counter2_model->get_data_level_by_filter($zone);
            $pue['req_level'] = $dataLevel;

            $forbiddWitel = $currUser['level'] == 'witel' && $dataLevel['witel_kode'] != $currUser['locationId'];
            $forbiddDivre = $currUser['level'] == 'divre' && $dataLevel['divre_kode'] != $currUser['locationId'];
            if($forbiddWitel || $forbiddDivre) {
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
                ->activity('get pue monitoring on ' . implode(', ', array_values($dataLevel)))
                ->log();
            
			$data = [
                'pue' => $pue,
                'success' => true
            ];
        }
        
        $this->response($data, $status);
    }

    public function pue_v2_test_get()
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
            $rtuCode = $this->input->get('rtu');
            $currUser = $this->auth_jwt->get_payload();
            $this->load->model('pue_counter2_model');

            $pueAvg = $this->pue_counter2_model->get_avg_value_v2();
            $data = [ 'pueAvg' => $pueAvg, 'success' => true ];
        }

        $this->response($data, $status);
    }

}