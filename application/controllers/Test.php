<?php

class Test extends CI_Controller
{
    // public function __construct()
    // {
    //   parent::__construct();
    // }

    public function index()
    {
        error_reporting(E_ALL);
        $method = $this->input->get('method');
        $args = $this->input->get('args');
        try {
            if($args) {

                if(!is_array($args)) {
                    throw new \Error('args params should be array, ex:\'?method=test&args[]=2&args[]=5\'');
                }

                $this->$method(...$args);

            } else {

                $this->$method();

            }
        } catch(\Throwable $err) {

            $errMessage = '<div style="margin: 12px 15px 12px 15px;"><pre>' . strval($err) . '</pre></div>';
            show_error($errMessage, 400);
        }
    }

    private function broken_deg(bool $isUpdatable)
    {
        $this->load->database('opnimus_new');

        $fields = [
            'hist.alarm_history_id',
            'hist.alarm_id',
            'hist.alarm_type',
            'hist.port_id',
            'hist.port_no',
            'hist.port_name',
            'hist.rtu_id',
            'hist.rtu_sname',
            'hist.alert_start_time',
            'hist.opened_at',
            'hist.closed_at',
            'alert.created_at AS alert_created_at',
        ];

        if($isUpdatable) {
            $this->db
                ->select( implode(', ', $fields) )
                ->from('alarm_history AS hist')
                ->join('alert_stack AS alert', 'alert.alarm_history_id=hist.alarm_history_id')
                ->where('alert.alert_type', 'close-port')
                ->where('hist.port_name', 'Status DEG')
                ->where('hist.closed_at<', '2024-04-03 12:54:00')
                ->order_by('rtu_sname')
                ->order_by('alarm_id')
                ->order_by('alarm_history_id');
            $result = $this->db->get()->result_array();
        } else {
            $this->db
                ->select( implode(', ', $fields) )
                ->from('alarm_history AS hist')
                ->join('alert_stack AS alert', 'alert.alarm_history_id=hist.alarm_history_id', 'LEFT')
                ->where('alert.alert_stack_id IS NULL')
                ->where('hist.port_name', 'Status DEG')
                ->where('hist.closed_at<', '2024-04-03 12:54:00')
                ->order_by('rtu_sname')
                ->order_by('alarm_id')
                ->order_by('alarm_history_id');
            dd($this->db->get_compiled_select());
            $result = $this->db->get()->result_array();
        }

        $this->load->view('simple_bootstrap/table_default', [
            'title' => 'Broken DEG Data',
            'tableData' => $result
        ]);
    }
}