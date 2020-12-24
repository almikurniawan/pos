<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$data['grid'] = $this->grid();
		$this->render('referensi/user', $data);
	}

	public function grid()
	{
		$SQL = "select *, user_id as id from user left join ref_user_type on ref_user_type_id = user_type";

        $this->load->library('smart_grid_kendo');
        
        $action['edit']     = array(
            'link'          => 'referensi/user/edit/'
        );
        $action['delete']     = array(
            'jsf'          => 'delete_user'
        );
		$this->smart_grid_kendo->configure(
			array(
				'datasouce_url' => base_url("referensi/user/grid?datasource&" . get_query_string()),
				'grid_columns'  => array(
					array(
						'field' => 'id',
						'title' => 'ID',
						'width'	=> 40
					),
					array(
						'field' => 'user_name',
						'title' => 'Username'
					),
					array(
						'field' => 'ref_user_type_label',
						'title' => 'Type User'
					)
                ),
				'action'        => $action,
				'head_left'		=> array('add'=>base_url('referensi/user/add'))
			)
		);
		$this->smart_grid_kendo->set_query($SQL, array());
		$grid = $this->smart_grid_kendo->output();
		return $grid;
	}

	public function add()
	{
		$data['form'] = $this->form();
		$this->render('referensi/produk_edit', $data);
	}

	public function edit($id)
	{
		$data['form'] = $this->form($id);
		$this->render('referensi/user_form', $data);
	}
	
	public function delete()
	{
		$id = $this->input->post('id');
		$this->db->delete('user', array('user_id'=>$id));
		return $this->response_json(array(
			'success'=> true,
			'message'=> 'Sukses delete data'
		));
	}

	public function form($id=null)
	{
		$this->load->library('smart_form');
		$this->smart_form->set_attribute_form('class="form-horizontal"');
		if ($id!=null) {
			$data = $this->db->get_where('user', array('user_id' => $id))->row_array();
		}
		$this->smart_form->add('user_name', 'Username', 'text', true, $data['user_name'], 'style="width:100%;"');
		$this->smart_form->add('user_type', 'Jenis User', 'select', false, $data['user_type'], 'style="width:100%;"', array(
			'table'	=> 'ref_user_type',
			'id'	=> 'ref_user_type_id',
			'label'	=> "ref_user_type_label",
			'sort'	=> 'ref_user_type_id asc'
		));
		$this->smart_form->add('user_password', 'Password', 'password', false, '', 'style="width:100%;"');

		if ($this->smart_form->formVerified()) {
			if ($id!=null) {
				$data_update = array(
					'user_name'	=> $this->input->post('user_name'),
					'user_type'	=> $this->input->post('user_type'),
				);
				if($this->input->post('user_password')!=''){
					$data_update['user_password'] = sha1($this->input->post('user_password'));
				}
				$this->db->update("user", $data_update, array('user_id' => $id));
				return $this->smart_form->output('success','Sukses Edit Data');
			} else {
				$data_insert = array(
					'user_name'	=> $this->input->post('user_name'),
					'user_type'	=> $this->input->post('user_type'),
				);
				if($this->input->post('user_password')!=''){
					$data_insert['user_password'] = sha1($this->input->post('user_password'));
				}
				$this->db->insert("user", $data_insert);
				return $this->smart_form->output('success','Sukses Insert Baru');
			}
		} else {
			return $this->smart_form->output();
		}
	}
}
