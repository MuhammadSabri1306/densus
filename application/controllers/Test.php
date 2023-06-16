<?php

class Test extends CI_Controller
{
    public function __construct()
    {
      parent::__construct();
    }

    public function get_pue_chart_data()
    {
        $zone = [ 'divre' => 'TLK-r7000000' ];
        $stopwatchConfig = [
            'auto' => true,
            'case' => 'pue_chart_data',
            'description' => 'Pue_counter2_model/get_zone_avg_on_curr_year'
        ];

        $this->load->model('pue_counter2_model');
        $this->load->library('blackbox_stopwatch');

        $this->blackbox_stopwatch->create_case('pue_chart_data', 'Pue_counter2_model/get_zone_avg_on_curr_year');
        $this->blackbox_stopwatch->start();
        $chartData = $this->pue_counter2_model->get_zone_avg_on_curr_year($zone);
        dd($chartData);
        $this->blackbox_stopwatch->stop();
        $this->blackbox_stopwatch->print_interval(false);

        $this->blackbox_stopwatch->create_case('pue_chart_data', 'Pue_counter2_model/get_zone_avg_on_curr_year_test');
        $this->blackbox_stopwatch->start();
        $this->pue_counter2_model->get_zone_avg_on_curr_year_v2($zone);
        $this->blackbox_stopwatch->stop();
        $this->blackbox_stopwatch->print_interval();
    }

    public function cron_store_pue_counter()
    {
        $filePath = APPPATH.'modules/crons/CronStorePueConter.php';
        require $filePath;

        CronStorePueConter::run();
    }

    public function newosase_api()
    {
        $url = 'https://newosase.telkom.co.id/api/v1/dashboard-service/dashboard/rtu/port-sensors?searchRtuName=PLN_CONS';
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGlfaWQiOiJ3ckIyRWtrWjQyVUNuZGxsNDI1eCIsInRva2VuIjoicDVjVVRfeTVFeklXUzRrY2VkTFdBUHdXeWlsVkpNZzNSNkdFaEduVW5VakZaS2VlVE8iLCJpYXQiOjE2NzI5NjgzNjQsImV4cCI6MTY3MzA1NDc2NH0.CE7j2PmC9qB3D_eIBlY-Ro3tNRrXUiwl_4VRXLsX_4Y';
        
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if($err) {
            dd_json($err);
        } else {
            $response = json_decode($response);
            dd_json($response);
        }
    }
}