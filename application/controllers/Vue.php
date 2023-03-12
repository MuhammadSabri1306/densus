<?php

class Vue extends CI_Controller
{
    public function __construct()
    {
      parent::__construct();
    }
  
    public function index(){
      // require $_SERVER['DOCUMENT_ROOT'].'/densus/vite-project/dist/index.html';
      $this->load->view('app.php');
    }

    // function dev($divreCode, $witelKey)
    // {
    //   $this->load->model('rtu_list_model');
    //   $data = $this->rtu_list_model->get_witel_dev($divreCode, $witelKey);
    //   $targetUrl = base_url("updatedev");

    //   echo "<form method='post' action='$targetUrl'>
    //     <div><table border='1'>
    //       <tr>
    //         <th>KODE DIVRE</th><th>KODE WITEL</th><th>NAMA WITEL</th>
    //       </tr>
    //       <tr>
    //         <td>'.$divreCode.'</td><td>'.$data->kode.'</td><td>'.$data->witel.'</td>
    //       </tr>
    //     </table></div>
    //     <input type='hidden' name='witel_key' value='$witelKey' />
    //     <input type='hidden' name='witel_code' value='$data->kode' />
    //     <input type='hidden' name='witel_name' value='$data->witel' />
    //     <button type='submit'><b>UPDATE</b></button>
    //   </form>";
    // }

    // function updatedev()
    // {
    //   $witelKey = $this->input->post('witel_key');
    //   $witelCode = $this->input->post('witel_code');
    //   $witelName = $this->input->post('witel_name');

    //   $this->load->model('lokasi_gepee_model');
    //   $status = $this->lokasi_gepee_model->test_update_witel($witelKey, $witelCode, $witelName);
    //   var_dump($status);
    //   echo '<br><br>NEXT:' . base_url('dev/TLK-r1000000/MEDAN');
    // }
}