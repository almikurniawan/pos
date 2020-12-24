<?php
class My_Model extends CI_Model{
    public function __construct(){
        parent::__construct();
    }
}

class Base_Model extends My_Model {
    protected $user;
    protected $kasir_log;
    public function __construct()
	{
        parent::__construct();
        
        $this->user         = $this->session->userdata('user');
        $this->kasir_log    = $this->session->userdata('kasir_log');
        $this->pos_master    = $this->session->userdata('pos_master');
    }
}