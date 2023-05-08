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
}