<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Monitoring_rtu_old extends RestController {

    private $bbmprice = 28440;
    private $biayalwbp = 1091;
    private $biayawbp = 1609;

    function __construct()
    {
        parent::__construct();
    }

    public function costbbm_get($sto)
    {
        $this->load->model('rtu_chart_model');
        $lokasidata = $this->rtu_chart_model->get_lokasidata($sto);
        
        $rtu = $lokasidata['rtu_kode'];
        $port_genset = $lokasidata['port_genset'];
        $kva_genset = $lokasidata['kva_genset'];

        $data['bbm_cost'] = $this->rtu_chart_model->get_bbmcost($rtu, $kva_genset, $port_genset, $this->bbmprice);

        $this->response($data, 200);
    }

    public function performance_get($sto)
    {
        $this->load->model('rtu_chart_model');
        
        $lokasidata = $this->rtu_chart_model->get_lokasidata($sto);
        $rtu = $lokasidata['rtu_kode'];
        $data['performance'] = $this->rtu_chart_model->get_kwhdata($rtu);

        $this->response($data, 200);
    }

    public function kwhreal_get($sto)
    {
        $this->load->model('rtu_chart_model');
        $lokasidata = $this->rtu_chart_model->get_lokasidata($sto);
        
        $rtu = $lokasidata['rtu_kode'];
        $port = $lokasidata['port_kwh'];

        $data['kwh_real'] = $this->rtu_chart_model->get_realkwh($rtu, $port);
        $this->response($data, 200);
    }
    
    public function savingpercent_get($sto)
    {
        $this->load->model('rtu_chart_model');

        $lokasidata = $this->rtu_chart_model->get_lokasidata($sto);
        $rtu = $lokasidata['rtu_kode'];
        $data['savingpercent'] = $this->rtu_chart_model->get_savingpercent($rtu);

        $this->response($data, 200);
    }
    
    public function kwhtotal_get($sto)
    {
        $this->load->model('rtu_chart_model');

        $lokasidata = $this->rtu_chart_model->get_lokasidata($sto);
        $rtu = $lokasidata['rtu_kode'];
        $data['kwh_total']=$this->rtu_chart_model->get_kwhtotal($rtu);

        $this->response($data, 200);
    }
    
    public function kwhtoday_get($sto)
    {
        $this->load->model('rtu_chart_model');

        $lokasidata = $this->rtu_chart_model->get_lokasidata($sto);
        $rtu = $lokasidata['rtu_kode'];
        $data['kwh_today']=$this->rtu_chart_model->get_kwhtoday($rtu);

        $this->response($data, 200);
    }

    public function totalalarm_get($sto)
    {
        $this->load->model('rtu_chart_model');

        $lokasidata = $this->rtu_chart_model->get_lokasidata($sto);
        $rtu = $lokasidata['rtu_kode'];
        $data['total_alarm']=$this->rtu_chart_model->get_totalAlarm($rtu);

        $this->response($data, 200);
    }
    
    public function tabledata_get($sto)
    {
        $this->load->model('rtu_chart_model');

        $lokasidata = $this->rtu_chart_model->get_lokasidata($sto);
        $rtu = $lokasidata['rtu_kode'];
        $data['tabledata']=$this->rtu_chart_model->get_tabledata($rtu, $this->biayalwbp, $this->biayawbp);

        $this->response($data, 200);
    }
    
    public function degtabledata_get($sto)
    {
        $this->load->model('rtu_chart_model');

        $lokasidata = $this->rtu_chart_model->get_lokasidata($sto);
        $port_genset = $lokasidata['port_genset'];
        $kva_genset = $lokasidata['kva_genset'];

        $rtu = $lokasidata['rtu_kode'];
        $data['degtabledata'] = $this->rtu_chart_model->get_deg_tabledata($rtu, $kva_genset, $port_genset, $this->bbmprice);

        $this->response($data, 200);
    }
    
    public function chartdatadaily_get($sto)
    {
        $this->load->model('rtu_chart_model');

        $lokasidata = $this->rtu_chart_model->get_lokasidata($sto);
        $rtu = $lokasidata['rtu_kode'];
        $data['chartdata_daily'] = $this->rtu_chart_model->get_chartdata($rtu, "daily");

        $this->response($data, 200);
    }
}