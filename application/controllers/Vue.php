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
}