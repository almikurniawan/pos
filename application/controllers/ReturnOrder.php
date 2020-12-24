<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ReturnOrder extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if($this->session->userdata('kasir_log')){
			if($this->session->userdata('return_id')){
				redirect('returnOrder/update');
			}
			$pos_master_id = (!$this->input->get('order_id') ? 0:$this->input->get('order_id'));
			$data['form'] = $this->form();
			$data['grid'] = $this->grid();
			$data['pos_master']	= $this->db->get_where("pos_master", "pos_id=".$pos_master_id)->row_array();
			$this->render('return', $data);
		}else{
			$data['kasir'] = $this->kasir();
			$this->render('buka_kasir', $data);
		}
    }

    private function kasir()
	{
		$data = $this->db->get("kasir")->result_array();
		return $data;
    }
    
    public function form()
	{
		$this->load->library('smart_form');		
        $this->smart_form->set_submit_label('CEK');
        $this->smart_form->set_form_method('GET');
		$this->smart_form->add('order_id', 'Order ID', 'text', true, '', 'style="width:100%;" autofocus');

		if ($this->smart_form->formVerified()) {
			return $this->smart_form->output();
		} else {
			return $this->smart_form->output();
		}
    }
    
    public function grid()
	{
		$pos_id = (!$this->input->get('order_id') ? 0:$this->input->get('order_id'));
		
		$btn = "concat('<input type=\"checkbox\" name=\"pos_det_id[',pos_det_id,']\" style=\"width:40px; height:40px;\"/>')";

		$data_pos_master = $this->db->get_where('pos_master', array('pos_id'=> $pos_id))->row_array();
		if($data_pos_master['pos_return_is']=="1"){
			$btn = "''";
			$alert = '<div class="alert alert-danger" role="alert">Data pembelian ini sudah pernah di return pada '.date_format(date_create($data_pos_master['pos_return_at']),'d F Y H:m').'</div>';
		}
		$SQL = "select *, pos_det_id as id, ".$btn." as action from pos_detail left join ref_produk on ref_produk_id = pos_det_produk_id where pos_det_master_id=".$pos_id;

		$this->load->library('smart_grid_kendo');
		
        $action['delete']     = array(
            'jsf'          => 'delete_pos_detail'
        );
		$this->smart_grid_kendo->configure(
			array(
				'datasouce_url' => base_url("returnOrder/grid?datasource&" . get_query_string()),
				'grid_columns'  => array(
					array(
						'field' => 'action',
						'title' => 'Act',
						'encoded'=> false,
						'width'	=> 110
					),
					array(
						'field' => 'ref_produk_nama_produk',
						'title' => 'Item'
					),
					array(
						'field' => 'pos_det_produk_harga_jual',
						'title' => 'Harga',
						
					),
					array(
						'field' => 'pos_det_jumlah_item',
						'title' => 'Jumlah',
						'width'	=> 20
					),
					array(
						'field' => 'pos_det_sub_total',
						'title' => 'Total',
						'format' => "number",
						'align' => 'right'
					)
                )
			)
		);
		$this->smart_grid_kendo->set_query($SQL, array());
		$grid = $this->smart_grid_kendo->output();
		return $alert . $grid;
	}

	public function process()
	{
		// print_r($_GET['pos_det_id']);
		// die();
		if(empty($_GET['pos_det_id']) && !isset($_GET['pos_det_id'])){
			$this->session->set_flashdata('warning', 'Silahkan pilih item yang di return dengan menandai item.');
			redirect('returnOrder?order_id='.$_GET['order_id']);
		}else{
			$data_return_master = array(
				'return_pos_master_id' 	=> $_GET['order_id'],
				'return_jumlah_item'	=> count($_GET['pos_det_id']),
				'return_kasir_log_id'	=> $this->kasir_log['kasir_log_id'],
				'return_created_at'		=> date('Y-m-d H:i:s')
			);
			$this->db->insert("return_master", $data_return_master);
			$return_master_id = $this->db->insert_id();
			$total = 0;
			foreach($_GET['pos_det_id'] as $pos_det_id=>$value){
				$data_pos_detail = $this->db->get_where("pos_detail", array('pos_det_id'=>$pos_det_id))->row_array();
				$data_return_detail = array(
					'return_det_master_id'	=> $return_master_id,
					'return_det_pos_det_id'	=> $pos_det_id,
					'return_det_produk_id'	=> $data_pos_detail['pos_det_produk_id'],
					'return_det_jumlah'		=> $data_pos_detail['pos_det_jumlah_item'],
					'return_det_sub_total'	=> $data_pos_detail['pos_det_sub_total'],
					'return_det_produk_harga_jual'=> $data_pos_detail['pos_det_produk_harga_jual']
				);
				$this->db->insert("return_detail", $data_return_detail);
				$total += (float)$data_pos_detail['pos_det_sub_total'];
			}
			$this->db->update("return_master", array(
				'return_total'	=> $total
			),'return_id='.$return_master_id);
			$this->session->set_userdata('return_id', $return_master_id);
			redirect('returnOrder/update');
		}
	}

	public function update()
	{
		$data['form']	= $this->form_return();
		$data['grid']	= $this->grid_return_detail();
		$this->render('return_order_update', $data);
	}

	public function grid_return_detail()
	{
		$return_id = (!$this->session->userdata('return_id') ? 0:$this->session->userdata('return_id'));
		$btn = "concat('<button type=\"button\" onclick=\"tambah(',return_det_id,')\" class=\"btn btn-sm btn-success\"><i class=\"k-icon k-i-plus\"></i></button> <button type=\"button\" onclick=\"kurang(',return_det_id,')\" class=\"btn btn-sm btn-warning\"><i class=\"k-icon k-i-minus\"></i></button>')";
		$SQL = "select *, return_det_id as id, ".$btn." as action from return_detail left join ref_produk on ref_produk_id = return_det_produk_id where return_det_master_id=".$return_id;

		$this->load->library('smart_grid_kendo');
		
        $action['delete']     = array(
            'jsf'          => 'delete_pos_detail'
        );
		$this->smart_grid_kendo->configure(
			array(
				'datasouce_url' => base_url("returnOrder/grid_return_detail?datasource&" . get_query_string()),
				'grid_columns'  => array(
					array(
						'field' => 'ref_produk_nama_produk',
						'title' => 'Item'
					),
					array(
						'field' => 'return_det_produk_harga_jual',
						'title' => 'Harga',
						'align' => 'right',
						'format' => "number",
					),
					array(
						'field' => 'return_det_jumlah',
						'title' => 'Jumlah',
						'width'	=> 20
					),
					array(
						'field' => 'action',
						'title' => 'Act',
						'encoded'=> false,
						'width'	=> 110
					),
					array(
						'field' => 'return_det_sub_total',
						'title' => 'Total',
						'format' => "number",
						'align' => 'right'
					)
                )
			)
		);
		$this->smart_grid_kendo->set_query($SQL, array());
		$grid = $this->smart_grid_kendo->output();
		return $grid;
	}

	public function form_return()
	{
		$this->load->library('smart_form');		
        $this->smart_form->set_submit_label('Simpan');
        $data = $this->db->get_where("return_master", array('return_id'=>$this->session->userdata('return_id')))->row_array();
		$this->smart_form->add('nama_pelanggan', 'Nama Pelanggan', 'text', true, $data['return_nama_pelanggan'], 'style="width:100%;"');
		$this->smart_form->add('alasan', 'Alasan Pengembalian', 'textArea', true, $data['return_alasan'], 'style="width:100%;"');

		if ($this->smart_form->formVerified()) {
			$this->db->update("return_master", array(
				'return_nama_pelanggan' => $this->input->post('nama_pelanggan'),
				'return_alasan' => $this->input->post('alasan'),
			),'return_id='.$this->session->userdata('return_id'));
			redirect('returnOrder/update');
		} else {
			return $this->smart_form->output();
		}
	}

	public function update_jumlah()
	{
		$return_id 		= $this->session->userdata('return_id');
		$return_det_id 	= $this->input->post('return_det_id');
		$jumlah 		= $this->input->post('jumlah');

		$return_detail 		= $this->db->get_where("return_detail", array('return_det_id'=>$return_det_id))->row_array();
		$data_pos_detail 	= $this->db->get_where("pos_detail",array('pos_det_id'=>$return_detail['return_det_pos_det_id']))->row_array();

		$new_jumlah = ((int)$return_detail['return_det_jumlah'] + $jumlah);
		if($new_jumlah<=0){
			return $this->response_json(array(
				'status'	=> false,
				'message'	=> 'Jumlah return item tidak boleh 0!',
				'jumlah'	=> $new_jumlah,
				'pos_jumlah'	=> $data_pos_detail['pos_det_jumlah_item'],
			));
		}
		if($new_jumlah>$data_pos_detail['pos_det_jumlah_item']){
			return $this->response_json(array(
				'status'	=> false,
				'message'	=> 'Jumlah return item melebihi pembelian!',
				'jumlah'	=> $new_jumlah,
				'pos_jumlah'	=> $data_pos_detail['pos_det_jumlah_item'],
			));
		}else{
			//lakukan transaksi
			$sub_total = $new_jumlah * $return_detail['return_det_produk_harga_jual'];
			$this->db->update('return_detail', array(
				'return_det_jumlah' => $new_jumlah,
				'return_det_sub_total'	=> $sub_total
			),'return_det_id='.$return_det_id);

			$sum = $this->db->query("select sum(return_det_sub_total) as total from return_detail where return_det_master_id=".$return_id)->row_array();
			$this->db->update("return_master", array(
				'return_total'=> $sum['total']
			),'return_id='.$return_id);

			return $this->response_json(array(
				'status'	=> true,
				'message'	=> 'Sukses melakukan perubahan',
				'jumlah'	=> $new_jumlah,
				'pos_jumlah'	=> $data_pos_detail['pos_det_jumlah_item'],
			));
		}
	}

	public function get_total()
	{
		$return_id = $this->session->userdata('return_id');
		$data = $this->db->get_where("return_master", array('return_id'=>$return_id))->row_array();

		return $this->response_json($data);
	}

	public function return_lock()
	{
		$return_id = $this->session->userdata('return_id');
		$return_master = $this->db->get_where("return_master", array('return_id'=>$return_id))->row_array();
		$this->db->update("return_master", array(
			'return_lock_is'=> '1',
			'return_lock_at'=> date('Y-m-d H:i:s')
		), 'return_id='.$return_id);
		$this->db->query("update kasir_log set kasir_log_total_return = coalesce(kasir_log_total_return,0) + ".$return_master['return_total']." where kasir_log_id=".$this->kasir_log['kasir_log_id']);
		$this->db->update("pos_master", array(
			'pos_return_is'=> '1',
			'pos_return_at'=> date('Y-m-d H:i:s')
		),'pos_id='.$return_master['return_pos_master_id']);

		$this->load->model('transaction');
		$this->transaction->mutasi($return_id, 'return');

		$this->session->unset_userdata('return_id');
		redirect('returnOrder/finishReturn/'.$return_id);
	}

	public function finishReturn($return_id)
	{
		$data = $this->db->get_where("return_master", array('return_id'=> $return_id))->row_array();
		return $this->render('finish_return', $data);
	}
}
