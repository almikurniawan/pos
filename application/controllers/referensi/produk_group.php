<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Produk_group extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$data['grid'] = $this->grid();
		$this->render('referensi/produk_group', $data);
	}

	public function grid()
	{
		$SQL = "select *, ref_kategori_id as id from ref_kategori";

        $this->load->library('smart_grid_kendo');
        
        $action['edit']     = array(
            'link'          => 'referensi/produk_group/edit/'
        );
        $action['delete']     = array(
            'jsf'          => 'delete_kategori'
        );
		$this->smart_grid_kendo->configure(
			array(
				'datasouce_url' => base_url("referensi/produk_group/grid?datasource&" . get_query_string()),
				'grid_columns'  => array(
					array(
						'field' => 'id',
						'title' => 'ID',
						'width'	=> 100
					),
					array(
						'field' => 'ref_kategori_label',
						'title' => 'Label'
					)
                ),
				'action'        => $action,
				'head_left'		=> array('add'=>base_url('referensi/produk_group/add'))
			)
		);
		$this->smart_grid_kendo->set_query($SQL, array());
		$grid = $this->smart_grid_kendo->output();
		return $grid;
	}

	public function add()
	{
		$data['form'] = $this->form();
		$this->render('referensi/produk_group_edit', $data);
	}

	public function edit($id)
	{
		$data['form'] = $this->form($id);
		$this->render('referensi/produk_group_edit', $data);
	}
	
	public function delete()
	{
		$id = $this->input->post('id');
		$this->db->delete('ref_kategori', array('ref_kategori_id'=>$id));
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
			$data = $this->db->get_where('ref_kategori', array('ref_kategori_id' => $id))->row_array();
		}
		$this->smart_form->add('ref_kategori_label', 'Label', 'text', true, $data['ref_kategori_label'], 'style="width:100%;"');

		if ($this->smart_form->formVerified()) {
			if ($id!=null) {
				$data_update = array(
					'ref_kategori_label'	=> $this->input->post('ref_kategori_label')
				);
				$this->db->update("ref_kategori", $data_update, array('ref_kategori_id' => $id));
				return $this->smart_form->output('success','Sukses Edit Data');
			} else {
				$data_insert = array(
					'ref_kategori_label'	=> $this->input->post('ref_kategori_label')
				);
				$this->db->insert("ref_kategori", $data_insert);
				return $this->smart_form->output('success','Sukses Insert Baru');
			}
		} else {
			return $this->smart_form->output();
		}
	}
}
