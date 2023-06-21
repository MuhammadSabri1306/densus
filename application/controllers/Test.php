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

    public function list_uploaded_file()
    {
        $this->load->database('densus');
        $tableList = [
            [ 'title' => 'Activity Execution', 'tableName' => 'activity_execution', 'tableField' => 'evidence', 'uploadPath' => UPLOAD_ACTIVITY_EVIDENCE_PATH ],
            [ 'title' => 'Gepee Evidence', 'tableName' => 'gepee_evidence', 'tableField' => 'file', 'uploadPath' => UPLOAD_GEPEE_EVIDENCE_PATH ],
            [ 'title' => 'Pue Offline', 'tableName' => 'pue_offline', 'tableField' => 'evidence', 'uploadPath' => UPLOAD_PUE_EVIDENCE_PATH ],
            [ 'title' => 'OXISP', 'tableName' => 'oxisp_activity', 'tableField' => 'evidence', 'uploadPath' => UPLOAD_OXISP_EVIDENCE_PATH ],
        ];

        $data = [];
        foreach($tableList as $tableItem) {
            $this->db
                ->select($tableItem['tableField'].' AS file, updated_at')
                ->from($tableItem['tableName']);
            $result = $this->db->get()->result_array();
            
            foreach($result as $item) {
                $row = [
                    'title' => $tableItem['title'],
                    'dir' => $tableItem['uploadPath'],
                    'path' => $tableItem['uploadPath'].$item['file'],
                    'fullpath' => FCPATH.$tableItem['uploadPath'].$item['file'],
                    'filename' => $item['file'],
                    'updated_at' => $item['updated_at']
                ];
                $row['isExists'] = file_exists($row['fullpath']);
                array_push($data, $row);
            }

        }

        $no = 1;
        ?><style>
            .table-responsive { width: 100%; height: 90vh; overflow: auto; border: 1px solid black; }
            .table-responsive table { min-width:100% }
            .table-responsive tr:first-child th { position: sticky; top: 0; }
            .table-responsive tr *:nth-child(2) { position: sticky; left: 0; z-index: 2; }
            .table-responsive tr:first-child *:nth-child(2) { z-index: 3; }
            table { border: none; border-collapse: separate; border-spacing: 0; }
            td, th { border: 1px solid #000; background: #fff; }
        </style>
        <div class="table-responsive"><table>
            <tr>
                <th>No</th>
                <th>Tipe</th>
                <th>Direktori</th>
                <th>File Path</th>
                <th>Full Path</th>
                <th>Filename (raw DB)</th>
                <th>Tgl. Update</th>
                <th>Exists</th>
            </tr><?php

            foreach($data as $row) {

                ?><tr>
                    <td><?=$no?></td>
                    <td><?=$row['title']?></td>
                    <td><?=$row['dir']?></td>
                    <td><?=$row['path']?></td>
                    <td><?=$row['fullpath']?></td>
                    <td><?=$row['filename']?></td>
                    <td><?=$row['updated_at']?></td>
                    <td><?=$row['isExists'] ? 'Ada' : 'Tidak Ada'?></td>
                </tr><?php

                $no++;
            }

        ?></table></div><?php
    }
}
// MySQL Table1 name = activity_execution
// MySQL Table1 file field = evidence
// PHP path Constant1 = UPLOAD_ACTIVITY_EVIDENCE_PATH

// MySQL Table2 name = gepee_evidence
// MySQL Table2 file field = file
// PHP path Constant2 = UPLOAD_GEPEE_EVIDENCE_PATH

// MySQL Table3 name = pue_offline
// MySQL Table3 file field = evidence
// PHP path Constant3 = UPLOAD_PUE_EVIDENCE_PATH

// MySQL Table4 name = oxisp_activity
// MySQL Table4 file field = evidence
// PHP path Constant4 = UPLOAD_OXISP_EVIDENCE_PATH