<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$this->load->view('user_login');
	}

	public function do_login()
	{
		$user_name = addslashes($this->input->post('user_name'));
		$user_password = $this->input->post('user_password');
		$sql = $this->db->query("select * from user where user_name='".$user_name."' and user_password='".sha1($user_password)."'");
		$data = $sql->row_array();
		if(!empty($data)){
			$this->session->set_userdata('user',$data);
			redirect('/home');
		}else{
			$this->session->set_flashdata('warning','Login Gagal !');
			redirect('/login');
		}
	}

	public function logout()
	{
		$this->session->unset_userdata('user');
		redirect('/login');
	}
}
