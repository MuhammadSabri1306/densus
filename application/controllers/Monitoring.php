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
        $data['tabledata']=$this->rtu_chart_model->get_tabledata($rtu, $this->biayalwbp, $this->biayawbp);
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
}