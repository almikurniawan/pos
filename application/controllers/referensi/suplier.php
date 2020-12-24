<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Suplier extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$data['grid'] = $this->grid();
		$this->render('referensi/suplier', $data);
	}

	public function grid()
	{
		$SQL = "select *, ref_suplier_id as id from ref_suplier";

        $this->load->library('smart_grid_kendo');
        
        $action['edit']     = array(
            'link'          => 'referensi/suplier/edit/'
        );
        $action['delete']     = array(
            'jsf'          => 'delete_suplier'
        );
		$this->smart_grid_kendo->configure(
			array(
				'datasouce_url' => base_url("referensi/suplier/grid?datasource&" . get_query_string()),
				'grid_columns'  => array(
					array(
						'field' => 'id',
						'title' => 'ID',
						'width'	=> 100
					),
					array(
						'field' => 'ref_suplier_nama',
						'title' => 'Nama'
					),
					array(
						'field' => 'ref_suplier_alamat',
						'title' => 'Alamat'
					),
					array(
						'field' => 'ref_suplier_telp',
						'title' => 'Telp.'
					),
					array(
						'field' => 'ref_suplier_email',
						'title' => 'Email'
					)
                ),
				'action'        => $action,
				'head_left'		=> array('add'=>base_url('referensi/suplier/add'))
			)
		);
		$this->smart_grid_kendo->set_query($SQL, array());
		$this->smart_grid_kendo->set_sort(array('id','desc'));
		$grid = $this->smart_grid_kendo->output();
		return $grid;
	}

	public function add()
	{
		$data['form'] = $this->form($id);
		$this->render('referensi/satuan_edit', $data);
	}

	public function edit($id)
	{
		$data['form'] = $this->form($id);
		$this->render('referensi/satuan_edit', $data);
	}
	
	public function delete()
	{
		$id = $this->input->post('id');
		$this->db->delete('ref_suplier', array('ref_suplier_id'=>$id));
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
			$data = $this->db->get_where('ref_suplier', array('ref_suplier_id' => $id))->row_array();
		}
		$this->smart_form->add('ref_suplier_nama', 'Nama', 'text', true, $data['ref_suplier_nama'], 'style="width:100%;"');
		$this->smart_form->add('ref_suplier_alamat', 'Alamat', 'text', true, $data['ref_suplier_alamat'], 'style="width:100%;"');
		$this->smart_form->add('ref_suplier_telp', 'Telp', 'text', true, $data['ref_suplier_telp'], 'style="width:100%;"');
		$this->smart_form->add('ref_suplier_email', 'Email', 'text', true, $data['ref_suplier_email'], 'style="width:100%;"');

		if ($this->smart_form->formVerified()) {
			if ($id!=null) {
				$data_update = array(
					'ref_suplier_nama'	=> $this->input->post('ref_suplier_nama'),
					'ref_suplier_alamat'	=> $this->input->post('ref_suplier_alamat'),
					'ref_suplier_telp'	=> $this->input->post('ref_suplier_telp'),
					'ref_suplier_email'	=> $this->input->post('ref_suplier_email'),
				);
				$this->db->update("ref_suplier", $data_update, array('ref_suplier' => $id));
				return $this->smart_form->output('success','Sukses Edit Data');
			} else {
				$data_insert = array(
					'ref_suplier_nama'	=> $this->input->post('ref_suplier_nama'),
					'ref_suplier_alamat'	=> $this->input->post('ref_suplier_alamat'),
					'ref_suplier_telp'	=> $this->input->post('ref_suplier_telp'),
					'ref_suplier_email'	=> $this->input->post('ref_suplier_email'),
				);
				$this->db->insert("ref_suplier", $data_insert);
				return $this->smart_form->output('success','Sukses Insert Baru');
			}
		} else {
			return $this->smart_form->output();
		}
	}
}
