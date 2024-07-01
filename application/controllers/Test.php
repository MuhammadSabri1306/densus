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

    public function pass_hash(string $pass)
    {
        dd(password_hash($pass, PASSWORD_DEFAULT));
    }

    public function phpinfo(string $privateKey)
    {
        if($privateKey == 'densus@phpinfo983u2') {
            phpinfo();
        }
    }

    public function log_ability()
    {
        log_message('error', 'Test logging error');
        echo 'done';
    }

    private function broken_deg(bool $isUpdatable, $rtuSname = null, $closedAt = null)
    {
        $this->load->database('opnimus_new');
        if($isUpdatable) {

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

            $this->db
                ->select( implode(', ', $fields) )
                ->from('alert_stack AS alert')
                ->join('alarm_history AS hist', 'hist.alarm_history_id=alert.alarm_history_id')
                ->where('alert.alert_type', 'close-port')
                ->where('hist.port_name', 'Status DEG')
                ->order_by('rtu_sname')
                ->order_by('alarm_id')
                ->order_by('opened_at', 'DESC');

            $result = $this->db->get()->result_array();
            $tableData = array_values(array_reduce($result, function($table, $row) {
                if(substr($row['closed_at'], 0, 15) != substr($row['alert_created_at'], 0, 15)) {
                    $alarmHistoryId = $row['alarm_history_id'];
                    $table[$alarmHistoryId] = $row;
                }
                return $table;
            }, []));
            
            $this->load->view('simple_bootstrap/header', [ 'title' => 'Broken DEG Data' ]);
            $this->load->view('simple_bootstrap/table_default', [
                'tableData' => $tableData,
                'rowCount' => count($tableData)
            ]);
            $this->load->view('simple_bootstrap/footer');

        } else {

            $this->load->view('simple_bootstrap/header', [ 'title' => 'Broken DEG Data' ]);
            if($rtuSname) {

                $this->load->view('simple_bootstrap/nav_sticky', [
                    'content' => function() {
                        ?><a href="<?=base_url('test?method=broken_deg&args[]=0')?>" class="btn btn-primary">
                            Kembali
                        </a><?php
                    }
                ]);

                $fields = [
                    'hist.*',
                    // 'hist.alarm_history_id',
                    // 'hist.alarm_id',
                    // 'hist.alarm_type',
                    // 'hist.port_id',
                    // 'hist.port_no',
                    // 'hist.port_name',
                    // 'hist.rtu_id',
                    // 'hist.rtu_sname',
                    // 'hist.alert_start_time',
                    // 'hist.opened_at',
                    // 'hist.closed_at',
                    '(SELECT alert.created_at FROM alert_stack AS alert WHERE alert.alarm_history_id=hist.alarm_history_id AND alert_type=\'close-port\' LIMIT 1) AS alarm_close_alert_created_at',
                ];
    
                $result = [];
                $debugText = null;
                try {
                    if($closedAt) {
                        $this->db
                            ->select( implode(', ', $fields) )
                            ->from('alarm_history AS hist')
                            ->where('hist.port_name', 'Status DEG')
                            ->where('hist.rtu_sname', $rtuSname)
                            ->where('hist.closed_at', $closedAt)
                            ->order_by('alarm_close_alert_created_at', 'DESC')
                            ->order_by('opened_at', 'DESC');
                    } else {
                        $this->db
                            ->select( implode(', ', $fields) )
                            ->from('alarm_history AS hist')
                            ->where('hist.port_name', 'Status DEG')
                            ->where('hist.rtu_sname', $rtuSname)
                            ->where('hist.closed_at IS NULL')
                            ->order_by('alarm_close_alert_created_at', 'DESC')
                            ->order_by('opened_at', 'DESC');

                    }
                    $result = $this->db->get()->result_array();
                } catch(\Throwable $err) {
                    $debugText = strval($err);
                }

                $tableData = array_map(function($row) {
                    $isBroken = !$row['closed_at'] || !$row['alarm_close_alert_created_at'];
                    if(!$isBroken) {
                        $isBroken = substr($row['closed_at'], 0, 15) != substr($row['alarm_close_alert_created_at'], 0, 15);
                    }
                    $row['is_broken'] = $isBroken ? 'YA' : 'TIDAK';
                    return $row;
                }, $result);

                $brokenAlarmCount = count( array_filter($tableData, fn($row) => $row['is_broken']) );

                $viewData = [
                    'tableTitle' => 'Closed DEG Summary Detail',
                    'tableData' => $tableData,
                    'tableAttrs' => [
                        'Total Alarm tgl terindikasi rusak' => $brokenAlarmCount,
                    ],
                    'rowCount' => count($tableData)
                ];
                if($debugText) $viewData['debugText'] = $debugText;

                $this->load->view('simple_bootstrap/table_default', $viewData);

            } else {

                $fields = [
                    'COUNT(*) AS alarm_count',
                    'alarm_id',
                    'rtu_sname',
                    'closed_at',
                    'MAX(opened_at) AS max_opened_at',
                ];
    
                $this->db
                    ->select( implode(', ', $fields) )
                    ->from('alarm_history')
                    ->where('port_name', 'Status DEG')
                    // ->where('closed_at<=', '2024-04-09 11:52:34')
                    ->having('COUNT(*) > 1')
                    ->group_by( array_slice($fields, 1, 3) )
                    ->order_by('closed_at', 'DESC');
                $result = $this->db->get()->result_array();
    
                $closedData = [];
                $unclosedData = [];
                foreach($result as $row) {
                    if($row['closed_at']) {
                        $row['detail'] = '<a href="'.base_url('test?method=broken_deg&args[]=0&args[]='.$row['rtu_sname'].'&args[]='.$row['closed_at']).'" class="btn btn-primary">Detail</a>';
                        array_push($closedData, $row);
                    } else {
                        $row['detail'] = '<a href="'.base_url('test?method=broken_deg&args[]=0&args[]='.$row['rtu_sname']).'" class="btn btn-primary">Detail</a>';
                        array_push($unclosedData, $row);
                    }
                }
                unset($result);

                $closedAlarmCount = array_reduce($closedData, fn($sum, $row) => $sum + $row['alarm_count'], 0);
                $closedDataCount = count($closedData);
                $closedBrokenAlarmCount = $closedAlarmCount - $closedDataCount;
                $this->load->view('simple_bootstrap/table_default', [
                    'tableTitle' => 'Closed DEG Summary',
                    'tableData' => $closedData,
                    'tableAttrs' => [
                        'Total Alarm tgl terindikasi rusak' => strval($closedBrokenAlarmCount) . " ($closedAlarmCount - $closedDataCount)",
                        'Jumlah Alarm' => $closedAlarmCount
                    ],
                    'rowCount' => $closedDataCount
                ]);
    
                $this->load->view('simple_bootstrap/table_default', [
                    'tableTitle' => 'Unclosed DEG Summary',
                    'tableData' => $unclosedData,
                    'tableAttrs' => [
                        'Jumlah Alarm' => array_reduce($unclosedData, fn($sum, $row) => $sum + $row['alarm_count'], 0)
                    ],
                    'rowCount' => count($unclosedData)
                ]);

            }

            $this->load->view('simple_bootstrap/footer');

        }
    }

    private function fix_deg()
    {

        $rtuSname = 'RTU00-D1-AMK';
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

        $this->db
            ->select( implode(', ', $fields) )
            ->from('alert_stack AS alert')
            ->join('alarm_history AS hist', 'hist.alarm_history_id=alert.alarm_history_id')
            ->where('alert.alert_type', 'close-port')
            ->where('hist.port_name', 'Status DEG')
            ->where('hist.rtu_sname', $rtuSname)
            ->order_by('alarm_id')
            ->order_by('opened_at', 'DESC');

        $result = $this->db->get()->result_array();
        $tableData = array_values(array_reduce($result, function($table, $row) {

            if(!$stopLoop && substr($row['closed_at'], 0, 15) != substr($row['alert_created_at'], 0, 15)) {
                $alarmHistoryId = $row['alarm_history_id'];
                $row['new_closed_at'] = null;
                $table[$alarmHistoryId] = $row;
            }

            return $table;
        }, []));

        $debugText = null;
        try {
            foreach($tableData as &$row) {
                $this->db->where('alarm_history_id', $row['alarm_history_id']);
                $this->db->update('alarm_history', [ 'closed_at' => $row['alert_created_at'] ]);
                $row['new_closed_at'] = $row['alert_created_at'];
            }
        } catch(\Throwable $err) {
            $debugText = strval($err);
        }

        $viewData = [
            'tableData' => $tableData,
            'rowCount' => count($tableData)
        ];
        if($debugText) $viewData['debugText'] = $debugText;

        $this->load->view('simple_bootstrap/header', [ 'title' => 'Fixed Up DEG Data' ]);
        $this->load->view('simple_bootstrap/table_default', $viewData);
        $this->load->view('simple_bootstrap/footer');

    }

    public function unused_gepee_evd_attach()
    {
        $this->load->database('densus');
        $this->db->select('*')->from('gepee_evidence')->limit(1);
        $gepeeEvds = $this->db->get()->result_array();
        $gepeeEvdsAttchs = array_column($gepeeEvds, 'file');

        $attchDir = __DIR__ . '/../../upload/gepee_evidence/';
        $files = array_diff(scandir($attchDir), ['.', '..'], $gepeeEvdsAttchs);
        dd_json($files);
    }
}