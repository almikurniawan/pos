<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Scan extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	public function index($barcode = null, $update = null)
	{
		$data['grid'] = $this->search();
		if($barcode){
			$data['grid2'] = $this->formScan($barcode,$update);
		}
		$this->render('referensi/scan', $data);
	}
	public function search()
	{
		$this->load->library('smart_form');
		$this->smart_form->set_attribute_form('class="form-horizontal"')->set_form_method('GET')->set_form_type('search')->set_submit_label('Cari');
			$data = array(
				'barcode' => ''
			);
		$this->smart_form->add('barcode', 'Barcode', 'text', true, $data['barcode'], 'style="width:100%;" autofocus');

		if ($this->smart_form->formVerified()) {
			$barcode = $this->input->get('barcode');
			$cek = $this->db->get_where('ref_produk', array('ref_produk_barcode' => $barcode))->row_array();
			if($cek){
				$data_update = array(
					'ref_produk_barcode'	=> $cek['ref_produk_barcode'],
					'ref_produk_nama_produk'	=> $cek['ref_produk_nama_produk'],
					'ref_produk_is_ada'	=> 1,
				);
				$this->db->update("ref_produk", $data_update, array('ref_produk_barcode' => $barcode));
				return redirect('referensi/scan/index/'.$barcode.'/update');
			}else{
				return redirect('referensi/scan/index/'.$barcode);
			}
		} else {
			return $this->smart_form->output();
		}
	}
	public function formScan($barcode,$update)
	{
		$this->load->library('smart_form');
		$this->smart_form->set_attribute_form('class="form-horizontal"')->set_form_method('POST')->set_submit_label('Simpan');
		if ($update == 'update') {
			$data = $this->db->get_where('ref_produk', array('ref_produk_barcode' => $barcode))->row_array();
		}else{
			$data['ref_produk_barcode'] = $barcode;
			$data['ref_produk_id'] = null;
 		}
		$this->smart_form->add('ref_produk_nama_produk', 'Nama Produk', 'text', true, $data['ref_produk_nama_produk'], 'style="width:100%;"');
		// $this->smart_form->add('ref_produk_nama_produk_cetak', 'Cetak Struk', 'text', false, $data['ref_produk_nama_produk_cetak'], 'style="width:100%;"');
		$this->smart_form->add('ref_produk_harga_beli', 'Harga Beli', 'number', false, $data['ref_produk_harga_beli'], 'style="width:100%;"');
		$this->smart_form->add('ref_produk_harga_jual', 'Harga Jual', 'number', false, $data['ref_produk_harga_jual'], 'style="width:100%;"');
		// $this->smart_form->add('ref_produk_stok', 'Stok', 'number', false, $data['ref_produk_stok'], 'style="width:100%;"');
		// $this->smart_form->add('ref_produk_kategori', 'Jenis Produk', 'select', false, $data['ref_produk_kategori'], 'style="width:100%;"', array(
		// 	'table'	=> 'ref_kategori',
		// 	'id'	=> 'ref_kategori_id',
		// 	'label'	=> "ref_kategori_label",
		// 	'sort'	=> 'ref_kategori_id asc'
		// ));
		// $this->smart_form->add('ref_produk_satuan', 'Satuan', 'select', false, $data['ref_produk_satuan'], 'style="width:100%;"', array(
		// 	'table'	=> 'ref_satuan',
		// 	'id'	=> 'ref_satuan_id',
		// 	'label'	=> "ref_satuan_label",
		// 	'sort'	=> 'ref_satuan_id asc'
		// ));
		// $this->smart_form->add('ref_produk_id_suplier', 'Suplier', 'select', false, $data['ref_produk_id_suplier'], 'style="width:100%;"', array(
		// 	'table'	=> 'ref_suplier',
		// 	'id'	=> 'ref_suplier_id',
		// 	'label'	=> "concat(coalesce(ref_suplier_nama,''),' - ', coalesce(ref_suplier_alamat,'') ,' - ', coalesce(ref_suplier_telp,''))",
		// 	'sort'	=> 'ref_suplier_id asc'
		// ));
		
		// $this->smart_form->add('ref_produk_keterangan', 'Keterangan', 'textArea', false, $data['ref_produk_keterangan'], 'style="width:100%;"');
		$this->smart_form->add('ref_produk_barcode', 'Barcode', 'text', false, $data['ref_produk_barcode'], 'style="width:100%;"');

		if ($this->smart_form->formVerified()) {
			if ($data['ref_produk_id']!=null) {
				$data_update = array(
					'ref_produk_barcode'	=> $this->input->post('ref_produk_barcode'),
					'ref_produk_nama_produk'	=> $this->input->post('ref_produk_nama_produk'),
					// 'ref_produk_nama_produk_cetak'	=> $this->input->post('ref_produk_nama_produk_cetak'),
					// 'ref_produk_kategori'	=> $this->input->post('ref_produk_kategori'),
					// 'ref_produk_satuan'	=> $this->input->post('ref_produk_satuan'),
					'ref_produk_harga_beli'	=> $this->input->post('ref_produk_harga_beli'),
					'ref_produk_harga_jual'	=> $this->input->post('ref_produk_harga_jual'),
					// 'ref_produk_stok'	=> $this->input->post('ref_produk_stok'),
					// 'ref_produk_created_at'	=> time(),
					// 'ref_produk_keterangan'	=> $this->input->post('ref_produk_keterangan'),
					// 'ref_produk_id_suplier'	=> $this->input->post('ref_produk_id_suplier')
				);
				// var_dump($data_update);die();
				$this->db->update("ref_produk", $data_update, array('ref_produk_barcode' => $barcode));
				return $this->smart_form->output('success','Sukses Update Data');
				// return redirect('referensi/scan');
			} else {
				$data_insert = array(
					'ref_produk_barcode'	=> $this->input->post('ref_produk_barcode'),
					'ref_produk_nama_produk'	=> $this->input->post('ref_produk_nama_produk'),
					'ref_produk_nama_produk_cetak'	=> $this->input->post('ref_produk_nama_produk_cetak'),
					'ref_produk_kategori'	=> $this->input->post('ref_produk_kategori'),
					'ref_produk_satuan'	=> $this->input->post('ref_produk_satuan'),
					'ref_produk_harga_beli'	=> $this->input->post('ref_produk_harga_beli'),
					'ref_produk_harga_jual'	=> $this->input->post('ref_produk_harga_jual'),
					'ref_produk_stok'	=> $this->input->post('ref_produk_stok'),
					'ref_produk_created_at'	=> time(),
					'ref_produk_keterangan'	=> $this->input->post('ref_produk_keterangan'),
					'ref_produk_id_suplier'	=> $this->input->post('ref_produk_id_suplier')
				);
				// var_dump($data_insert);die();
				$this->db->insert("ref_produk", $data_insert);
				return $this->smart_form->output('success','Sukses Insert Baru');
				// return redirect('referensi/scan');
			}
		} else {
			return $this->smart_form->output();
		}
	}
}
