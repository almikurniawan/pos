<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MutasiStok extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$data['grid'] = $this->grid();
		$data['search'] = $this->search();
		$this->render('mutasi_stok', $data);
	}
   
	public function search()
	{
		$tgl_awal = $this->input->get('tgl_awal');

		$this->load->library('smart_form');
		$this->smart_form->set_form_type('search');
		$this->smart_form->set_form_method('GET');
		$this->smart_form->set_submit_label('Cari');
		$this->smart_form->add('produk', 'Produk', 'text', false, $this->input->get('produk'), 'style="width:100%;" ');
		$this->smart_form->add('tgl_awal', 'Tgl', 'date', false, ($tgl_awal=='' ? date("Y-m-d") : $tgl_awal), 'style="width:100%;" ');
		
		return $this->smart_form->output();
	}
	
    public function grid()
	{
		$SQL = "SELECT
					mutasi_id AS id,
					ref_produk.*,
					DATE_FORMAT(mutasi_created_at, '%d %M %Y %T') as waktu,
					ref_mutasi_label,
					ref_kategori_label,
					ref_satuan_label,
					mutasi_jumlah_sebelumnya AS stok_saat_ini,
					mutasi_jumlah,
					mutasi_produk_balance AS new_stok,
					mutasi_operator as operator
				FROM
					mutasi_stok
					LEFT JOIN ref_produk ON ref_produk_id = mutasi_produk_id
					LEFT JOIN ref_kategori ON ref_kategori_id = ref_produk_kategori
					LEFT JOIN ref_satuan ON ref_satuan_id = ref_produk_satuan
					left join ref_jenis_mutasi on ref_mutasi_id = mutasi_jenis_mutasi
					";

		$this->load->library('smart_grid_kendo');
		$this->smart_grid_kendo
			->set_query($SQL, array(
				array('lower(ref_produk_nama_produk)',$this->input->get('produk')),
				array("DATE_FORMAT(mutasi_created_at, '%Y-%m-%d')",$this->input->get('tgl_awal'),'=')
			))
			->set_sort(array('id','desc'))
			->configure(
				array(
					'datasouce_url' => base_url("mutasiStok/grid?datasource&" . get_query_string()),
					'grid_columns'  => array(
						array(
							'field' => 'ref_produk_nama_produk',
							'title' => 'Item'
						),
						array(
							'field' => 'ref_kategori_label',
							'title' => 'Kategori'
						),
						array(
							'field' => 'ref_satuan_label',
							'title' => 'Satuan'
						),
						array(
							'field' => 'ref_mutasi_label',
							'title' => 'Jenis Transaksi'
						),
						array(
							'field' => 'operator',
							'title' => 'Operator'
						),
						array(
							'field' => 'stok_saat_ini',
							'title' => 'Stok Lama'
						),
						array(
							'field' => 'mutasi_jumlah',
							'title' => 'Jumlah',
							'width'	=> 20
						),
						array(
							'field' => 'new_stok',
							'title' => 'Stok Baru',
						),
						array(
							'field' => 'waktu',
							'title' => 'Waktu',
						),
					)
				)
			);
		$grid = $this->smart_grid_kendo->output();
		return $grid;
	}
}
