<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Satuan extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$data['grid'] = $this->grid();
		$this->render('referensi/satuan', $data);
	}

	public function grid()
	{
		$SQL = "select *, ref_satuan_id as id from ref_satuan";

        $this->load->library('smart_grid_kendo');
        
        $action['edit']     = array(
            'link'          => 'referensi/satuan/edit/'
        );
        $action['delete']     = array(
            'jsf'          => 'delete_satuan'
        );
		$this->smart_grid_kendo->configure(
			array(
				'datasouce_url' => base_url("referensi/satuan/grid?datasource&" . get_query_string()),
				'grid_columns'  => array(
					array(
						'field' => 'id',
						'title' => 'ID',
						'width'	=> 100
					),
					array(
						'field' => 'ref_satuan_label',
						'title' => 'Label'
					)
                ),
				'action'        => $action,
				'head_left'		=> array('add'=>base_url('referensi/satuan/add'))
			)
		);
		$this->smart_grid_kendo->set_query($SQL, array());
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
		$this->db->delete('ref_satuan', array('ref_satuan_id'=>$id));
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
			$data = $this->db->get_where('ref_satuan', array('ref_satuan_id' => $id))->row_array();
		}
		$this->smart_form->add('ref_satuan_label', 'Label', 'text', true, $data['ref_satuan_label'], 'style="width:100%;"');

		if ($this->smart_form->formVerified()) {
			if ($id!=null) {
				$data_update = array(
					'ref_satuan_label'	=> $this->input->post('ref_satuan_label')
				);
				$this->db->update("ref_satuan", $data_update, array('ref_satuan_id' => $id));
				return $this->smart_form->output('success','Sukses Edit Data');
			} else {
				$data_insert = array(
					'ref_satuan_label'	=> $this->input->post('ref_satuan_label')
				);
				$this->db->insert("ref_satuan", $data_insert);
				return $this->smart_form->output('success','Sukses Insert Baru');
			}
		} else {
			return $this->smart_form->output();
		}
	}
}
