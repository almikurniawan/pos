<?php
defined('BASEPATH') or exit('No direct script access allowed');

class kasir extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$data['grid'] = $this->grid();
		$this->render('referensi/kasir', $data);
	}

	public function grid()
	{
		$SQL = "select *, kasir_id as id, case when kasir_printer_use = 1 then 'Ya' else 'Tidak' end as ada_printer from kasir";

        $this->load->library('smart_grid_kendo');
        
        $action['edit']     = array(
            'link'          => 'referensi/kasir/edit/'
        );
        $action['delete']     = array(
            'jsf'          => 'delete_kasir'
        );
		$this->smart_grid_kendo->configure(
			array(
				'datasouce_url' => base_url("referensi/kasir/grid?datasource&" . get_query_string()),
				'grid_columns'  => array(
					array(
						'field' => 'id',
						'title' => 'ID',
						'width'	=> 100
					),
					array(
						'field' => 'kasir_label',
						'title' => 'Label'
					),
					array(
						'field' => 'ada_printer',
						'title' => 'Ada Printer?'
					),
					array(
						'field' => 'kasir_print_konektor',
						'title' => 'Konektor'
					),
					array(
						'field' => 'kasir_print_name_ip',
						'title' => 'Nama Printer / IP Printer'
					),
					
                ),
				'action'        => $action,
				'head_left'		=> array('add'=>base_url('referensi/kasir/add'))
			)
		);
		$this->smart_grid_kendo->set_query($SQL, array());
		$grid = $this->smart_grid_kendo->output();
		return $grid;
	}

	public function add()
	{
		$data['form'] = $this->form($id);
		$this->render('referensi/kasir_form', $data);
	}

	public function edit($id)
	{
		$data['form'] = $this->form($id);
		$this->render('referensi/kasir_form', $data);
	}
	
	public function delete()
	{
		$id = $this->input->post('id');
		$this->db->delete('kasir', array('kasir_id'=>$id));
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
			$data = $this->db->get_where('kasir', array('kasir_id' => $id))->row_array();
		}
		$this->smart_form->add('kasir_label', 'Label', 'text', true, $data['kasir_label'], 'style="width:100%;"');
		$this->smart_form->add('kasir_printer_use', 'Ada Printer?', 'select_custom', true, $data['kasir_printer_use'], 'style="width:100%;"', array(
			'option'=> array(
				array(
					'id'=> 1, 'label'=> 'Ya'
				),
				array(
					'id'=> 0, 'label'=> 'Tidak'
				)
			)
		));
		$this->smart_form->add('kasir_print_konektor', 'Koneksi Printer', 'select_custom', true, $data['kasir_printer_use'], 'style="width:100%;"', array(
			'option'=> array(
				array(
					'id'=> 'USB', 'label'=> 'USB'
				),
				array(
					'id'=> 'LAN', 'label'=> 'LAN'
				)
			)
		));

		$this->smart_form->add('kasir_print_name_ip', 'Nama Printer / IP Printer', 'text', true, $data['kasir_print_name_ip'], 'style="width:100%;"');

		if ($this->smart_form->formVerified()) {
			if ($id!=null) {
				$data_update = array(
					'kasir_label'	=> $this->input->post('kasir_label'),
					'kasir_printer_use' => $this->input->post('kasir_printer_use'),
					'kasir_print_konektor' => $this->input->post('kasir_print_konektor'),
					'kasir_print_name_ip' => $this->input->post('kasir_print_name_ip'),
				);
				$this->db->update("kasir", $data_update, array('kasir_id' => $id));
				return $this->smart_form->output('success','Sukses Edit Data');
			} else {
				$data_insert = array(
					'kasir_label'	=> $this->input->post('kasir_label'),
					'kasir_printer_use' => $this->input->post('kasir_printer_use'),
					'kasir_print_konektor' => $this->input->post('kasir_print_konektor'),
					'kasir_print_name_ip' => $this->input->post('kasir_print_name_ip'),
				);
				$this->db->insert("kasir", $data_insert);
				return $this->smart_form->output('success','Sukses Insert Baru');
			}
		} else {
			return $this->smart_form->output();
		}
	}
}
