<?php
defined('BASEPATH') or exit('No direct script access allowed');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Home extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if($this->session->userdata('kasir_log')){
			$pos_master_id = ($this->pos_master ? $this->pos_master['pos_id'] : 0);			
			$data['form'] = $this->form();
			$data['form_manual'] = $this->form_manual();
			$data['grid'] = $this->grid();
			$data['pos_master']	= $this->db->get_where("pos_master", "pos_id=".$pos_master_id)->row_array();
			if(empty($data['pos_master'])){
				$data['pos_master']['pos_total'] = 0;
			}
			$this->render('penjualan', $data);
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

	public function buka_kasir()
	{
		$kasir_id = $this->input->post('kasir_id');
		$nominal = $this->input->post('nominal');

		$now = date('Y-m-d H:i:s');
		$this->db->insert("kasir_log", array(
			'kasir_log_user_id'=> $this->user['user_id'],
			'kasir_log_kasir_id'=> $kasir_id,
			'kasir_log_cash_in'=> $nominal,
			'kasir_log_cash_out'=> 0,
			'kasir_log_waktu_buka'=> $now,

		));
		$kasir_log_id = $this->db->insert_id();
		$this->session->set_userdata('kasir_log',array(
			'kasir_log_id'			=> $kasir_log_id,
			'kasir_log_kasir_id'	=> $kasir_id,
			'kasir_log_cash_in'		=> $nominal,
			'kasir_log_waktu_buka'	=> $now
		));

		return $this->response_json(array(
			'status'	=> true,
			'message'	=> 'Sukses Buka Kasir'
		));
	}

	public function form()
	{
		$this->load->library('smart_form');
		$this->smart_form
			->set_template('sf_penjualan')
			->set_submit_label('Masukan')
			->set_attribute_form(' name="penjualan_auto"')
			->add('barcode', 'Barcode', 'text', true, '', 'style="width:100%;" autofocus')
			->add('jumlah_barang', 'Jumlah', 'number', true, '1', 'style="width:100%;" ');

		if ($this->smart_form->formVerified()) {
			
		} else {
			return $this->smart_form->output();
		}
	}

	public function get_produk()
	{
		$filter = $this->input->get('filter');
		$cari_data = $this->db->query("select ref_produk_id as id, concat( ref_produk_nama_produk, ' - ', COALESCE(CAST(FORMAT(ref_produk_harga_jual, 2 ) as CHAR),'') ) AS value from ref_produk where lower(ref_produk_nama_produk) like '%".strtolower($filter)."%'")->result_array();
		if(!$cari_data){
			$cari_data = array();
		}
		return $this->response_json($cari_data);
	}

	public function form_manual()
	{
		$this->load->library('smart_form');
		$this->smart_form
			->set_template('sf_penjualan_manual')
			->set_submit_label('Masukan')
			->set_attribute_form(' name="penjualan_manual"')
			->add('nama_barang', 'Nama Barang', 'autoComplete', true, '', 'style="width:100%;" ', array(
				'url'	=> base_url('home/get_produk')
			))
			->add('jumlah_barang_manual', 'Jumlah', 'number', true, '1', 'style="width:100%;" ');
		
		if ($this->smart_form->formVerified()) {
			
		} else {
			return $this->smart_form->output();
		}
	}

	public function add_barang()
	{
		$barcode = $this->input->post('barcode');
		$jumlah = $this->input->post('jumlah_barang');
		$data_produk = $this->db->get_where("ref_produk",array('ref_produk_barcode'=>$barcode))->result_array();
		if(empty($data_produk)){
			return $this->response_json(array(
				'status'	=> false,
				'flag'		=> 'error',
				'code'		=> -1,
				'message'	=> 'Produk dengan barcode "'.$barcode.'" tidak ditemukan. Silahkan masukan data produk terlebih dahulu'
			));
		}else{
			if(count($data_produk)>1){
				return $this->response_json(array(
					'status'	=> false,
					'flag'		=> 'warning',
					'code'		=> 2,
					'message'	=> 'Produk lebih dari 1'
				));
			}else{
				if($data_produk[0]['ref_produk_harga_jual']=='' || $data_produk[0]['ref_produk_harga_jual']==0){
					return $this->response_json(array(
						'status'	=> false,
						'flag'		=> 'warning',
						'code'		=> 3,
						'message'	=> 'Harga Belum disetting',
						'produk_id'	=> $data_produk[0]['ref_produk_id']
					));
				}else{
					$this->load->model('transaction');
					$pos_master_id = ($this->pos_master ? $this->pos_master['pos_id'] : null);
					$pos = $this->transaction->sale($data_produk[0]['ref_produk_id'], $jumlah, $pos_master_id);
					return $this->response_json($pos);
				}
			}
		}
	}

	public function add_barang_by_produk_id($produk_id)
	{
		$data_produk = $this->db->get_where("ref_produk",array('ref_produk_id'=>$produk_id))->row_array();
		if($data_produk['ref_produk_harga_jual']=='' || $data_produk['ref_produk_harga_jual']==0){
			return $this->response_json(array(
				'status'	=> false,
				'flag'		=> 'warning',
				'code'		=> 3,
				'message'	=> 'Harga Belum disetting',
				'produk_id'	=> $data_produk['ref_produk_id']
			));
		}else{
			$this->load->model('transaction');
			$pos_master_id = ($this->pos_master ? $this->pos_master['pos_id'] : null);
			$pos = $this->transaction->sale($produk_id, 1, $pos_master_id);
			return $this->response_json($pos);
		}
	}

	public function add_barang_manual()
	{
		$nama_barang = $this->input->post('nama_barang');
		$jumlah = $this->input->post('jumlah_barang_manual');
		$data_produk = $this->db->get_where("ref_produk",array("concat( ref_produk_nama_produk, ' - ', COALESCE(CAST(FORMAT(ref_produk_harga_jual, 2 ) as CHAR),'') ) = "=>$nama_barang))->result_array();
		if(empty($data_produk)){
			return $this->response_json(array(
				'status'	=> false,
				'flag'		=> 'error',
				'code'		=> -1,
				'message'	=> 'Produk dengan barcode "'.$barcode.'" tidak ditemukan. Silahkan masukan data produk terlebih dahulu'
			));
		}else{
			if(count($data_produk)>1){
				return $this->response_json(array(
					'status'	=> false,
					'flag'		=> 'warning',
					'code'		=> 2,
					'message'	=> 'Produk lebih dari 1'
				));
			}else{
				if($data_produk[0]['ref_produk_harga_jual']=='' || $data_produk[0]['ref_produk_harga_jual']==0){
					return $this->response_json(array(
						'status'	=> false,
						'flag'		=> 'warning',
						'code'		=> 3,
						'message'	=> 'Harga Belum disetting',
						'produk_id'	=> $data_produk[0]['ref_produk_id']
					));
				}else{
					$this->load->model('transaction');
					$pos_master_id = ($this->pos_master ? $this->pos_master['pos_id'] : null);
					$pos = $this->transaction->sale($data_produk[0]['ref_produk_id'], $jumlah, $pos_master_id);
					return $this->response_json($pos);
				}
			}
		}
	}

	public function update()
	{
		$pos_det_id = $this->input->post('pos_det_id');
		$jumlah 	= $this->input->post('jumlah');
		$data_pos_detail = $this->db->get_where("pos_detail",array('pos_det_id'=>$pos_det_id))->row_array();
		if(empty($data_pos_detail)){
			return $this->response_json(array(
				'status'	=> false,
				'flag'		=> 'error',
				'message'	=> 'Pos detail dengan ID tersebut tidak ditemukan'
			));
		}else{
			$this->load->model('transaction');
			$pos_master_id = $data_pos_detail['pos_det_master_id'];
			$data_produk = $this->db->get_where("ref_produk", array('ref_produk_id'=> $data_pos_detail['pos_det_produk_id']))->row_array();
			$pos = $this->transaction->sale($data_produk['ref_produk_id'], $jumlah, $pos_master_id);
			return $this->response_json($pos);
		}
	}

	public function grid()
	{
		$pos_id = (!$this->pos_master ? 0:$this->pos_master['pos_id']);
		$btn = "concat('<button onclick=\"tambah(',pos_det_id,')\" class=\"btn btn-sm btn-success\"><i class=\"k-icon k-i-plus\"></i></button> <button onclick=\"kurang(',pos_det_id,')\" class=\"btn btn-sm btn-warning\"><i class=\"k-icon k-i-minus\"></i></button>')";

		$SQL = "select *, pos_det_id as id, ".$btn." as action from pos_detail left join ref_produk on ref_produk_id = pos_det_produk_id where pos_det_master_id=".$pos_id;

		$this->load->library('smart_grid_kendo');
		
        $action['delete']     = array(
            'jsf'          => 'delete_pos_detail'
        );
		$this->smart_grid_kendo
			->configure(
				array(
					'datasouce_url' => base_url("home/grid?datasource&" . get_query_string()),
					'grid_columns'  => array(
						array(
							'field' => 'ref_produk_nama_produk',
							'title' => 'Item'
						),
						array(
							'field' => 'pos_det_produk_harga_jual',
							'title' => 'Harga'
						),
						array(
							'field' => 'pos_det_jumlah_item',
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
							'field' => 'pos_det_sub_total',
							'title' => 'Total',
							'format' => "number",
							'align' => 'right'
						)
					),
					'action'        => $action
				)
			)
			->set_query($SQL, array());
		$grid = $this->smart_grid_kendo->output();
		return $grid;
	}

	public function delete()
	{
		$id = $this->input->post('id');
		$this->load->model('transaction');
		$pos_detail = $this->db->get_where('pos_detail', array('pos_det_id'=>$id))->row_array();
		$this->db->delete('pos_detail', array('pos_det_id'=>$id));
		$update_stok = $this->transaction->update_stok($pos_detail['pos_det_produk_id'], $pos_detail['pos_det_jumlah_item'],'+');

		$sum_detail = $this->db->query("select sum(pos_det_sub_total) as total, count(*) as jumlah from pos_detail where pos_det_master_id=".$pos_detail['pos_det_master_id'])->row_array();

        $this->db->update("pos_master", array(
            'pos_total' => $sum_detail['total'],
            'pos_jumlah_item' => $sum_detail['jumlah']
		),"pos_id=".$pos_detail['pos_det_master_id']);
		
		return $this->response_json(array(
			'success'=> true,
			'message'=> 'Sukses delete data'
		));
	}

	public function get_total()
	{
		$pos_master_id = ($this->pos_master['pos_id']>0 ? $this->pos_master['pos_id'] : 0);
		$result	= $this->db->get_where("pos_master", "pos_id=".$pos_master_id)->row_array();
		if(empty($result)){
			$result['pos_total'] = 0;
		}
		return $this->response_json($result);
	}

	public function bayar()
	{
		$this->render_popup('bayar', array(
			'form'=> $this->form_bayar()
		));
	}

	public function form_bayar()
	{
		$this->load->library('smart_form');
		$this->smart_form
			->set_submit_label('Bayar')
			->add('methode', 'Methode Bayar', 'select_custom', true, '', 'style="width:100%;" autofocus', array(
				'option'=> array(
					array(
						'id'		=> 1,
						'label'		=> 'Tunai'
					),
					array(
						'id'		=> 2,
						'label'		=> 'Non Tunai'
					),
				)
			))
			->add('nominal', 'Nominal', 'number', true, '', 'style="width:100%;" autofocus')
			->add('catatan', 'Catatan', 'textArea', false, '', 'style="width:100%;"');

		if ($this->smart_form->formVerified()) {
			$this->load->model('transaction');
			$payment = $this->transaction->pay($this->pos_master['pos_id'], $this->input->post('nominal'), $this->input->post('methode'));

			if(!$payment['status']){
				$alert  = '<div class="alert alert-warning" role="alert">'.$payment['message'].'</div>';
				return $alert . $this->smart_form->output();
			}

			$this->load->model('print_service');
			$this->print_service->print_struk($this->pos_master['pos_id']);

			return $this->finish_payment($payment);
			// return '<script> window.parent.gridReload(); window.parent.get_total(); window.parent.$("#dialog").data("kendoWindow").close();</script>';
		} else {
			return $this->smart_form->output();
		}
	}

	public function finish_payment($payment)
	{
		$html = $this->load->view('finish_payment',$payment, true);
		return $html;
	}

	public function reprint()
	{
		$pos_master_id = ($this->pos_master ? $this->pos_master['pos_id'] : 0);
		$this->load->model('print_service');
		$this->print_service->print_struk($pos_master_id);
	}

	public function new_order()
	{
		$this->session->unset_userdata('pos_master');
		return $this->response_json(array(
			'status'	=> true,
			'message'	=> 'New Order'
		));
	}

	public function data_kasir()
	{
		$kasir_log = $this->kasir_log['kasir_log_id'];
		$data = $this->db->get_where("kasir_log", array('kasir_log_id'=> $kasir_log))->row_array();
		return $this->response_json($data);
	}

	public function tutup_kasir()
	{
		$this->load->model('print_service');
		$this->db->update("kasir_log", array(
			'kasir_log_waktu_tutup'=> date('Y-m-d H:i:s')
		),'kasir_log_id='.$this->kasir_log['kasir_log_id']);
		$print = $this->print_service->print_tutup_kasir($this->kasir_log['kasir_log_id']);
		$this->session->unset_userdata("kasir_log");
		return $this->response_json(array(
			'status'	=> true,
			'message'	=> 'Sukses Tutup Kasir'
		));
	}
}
