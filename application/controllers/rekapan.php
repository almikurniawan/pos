<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rekapan extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function perkasir()
	{
		$data['grid'] = $this->grid();
		$data['search'] = $this->search_perkasir();
		return $this->render('rekapan/perkasir', $data);
	}
    public function search_perkasir()
	{
		$tgl_awal = $this->input->get('tgl');

		$this->load->library('smart_form');
		$this->smart_form
			->set_form_type('search')
			->set_form_method('GET')
			->set_submit_label('Cari')
			->add('tgl', 'Tgl.', 'date', true, ($tgl_awal=='' ? date("Y-m-d") : $tgl_awal), 'style="width:100%;" ');
		
		return $this->smart_form->output();
	}

    public function grid()
	{
		$SQL = "select *, kasir_log_id as id, concat( DATE_FORMAT( kasir_log_waktu_buka, '%d %M %Y %T' ), ' s/d ', coalesce(DATE_FORMAT( kasir_log_waktu_tutup, '%d %M %Y %T' ),'') ) as waktu_buka_tutup from kasir_log left join kasir on kasir_id = kasir_log_kasir_id left join user on user_id = kasir_log_user_id";

		$action['detail']     = array(
            'link'          => 'rekapan/perkasir_detail/'
		);
		$this->load->library('smart_grid_kendo');
		return $this->smart_grid_kendo
			->set_query($SQL, array(
				array("DATE_FORMAT( kasir_log_waktu_buka, '%Y-%m-%d' )",$this->input->get('tgl'),'='),
			))
			->set_sort(array('id','desc'))
			->configure(
				array(
					'datasouce_url' => base_url("rekapan/grid?datasource&" . get_query_string()),
					'grid_columns'  => array(
						array(
							'field' => 'waktu_buka_tutup',
							'title' => 'Waktu Buka',
							'width'	=> 250
						),
						array(
							'field' => 'user_nama_lengkap',
							'title' => 'User',
							'width'	=> 200
						),
						array(
							'field' => 'kasir_log_cash_in',
							'title' => 'Modal',
							'align'	=> 'right',
							'format'=> 'number',
							'width'	=> 100
						),
						array(
							'field' => 'kasir_log_total_penjualan',
							'title' => 'Tunai',
							'align'	=> 'right',
							'format'=> 'number',
							'width'	=> 100
						),
						array(
							'field' => 'kasir_log_total_penjualan_non_tunai',
							'title' => 'Non Tunai',
							'align'	=> 'right',
							'format'=> 'number',
							'width'	=> 100
						),
						array(
							'field' => 'kasir_log_total_return',
							'title' => 'Return',
							'align'	=> 'right',
							'format'=> 'number',
							'width'	=> 100
						),
					),
					'action'	=> $action
				)
			)->output();
	}

	public function perkasir_detail($kasir_log_id)
	{
		$data['grid'] = $this->grid_detail_perkasir($kasir_log_id);
		$data['return'] = $this->grid_return_detail_perkasir($kasir_log_id);
		$data['search'] = $this->search_perkasir_detail($kasir_log_id);
		return $this->render('rekapan/perkasir_detail', $data);
	}

	public function search_perkasir_detail()
	{
		$nomor = $this->input->get('nomor');

		$this->load->library('smart_form');
		return $this->smart_form
			->set_form_type('search')
			->set_form_method('GET')
			->set_submit_label('Cari')
			->add('nomor', 'No. TRX.', 'text', true, $nomor, 'style="width:100%;" ')
			->output();
	}

	public function grid_detail_perkasir($kasir_log_id)
	{
		$SQL = "select *, pos_id as id, coalesce(DATE_FORMAT( pos_created_at, '%d %M %Y %T' ),'-') as waktu_transaksi, case when pos_bayar_is=1 then 'Terbayar' else 'Belum Bayar' end as status_bayar, case when pos_return_is=1 then 'Return' else '' end status_return from pos_master";

		$action['detail']     = array(
            'jsf'          => 'detail_pos'
		);
		$this->load->library('smart_grid_kendo');
		return $this->smart_grid_kendo
			->set_query($SQL, array(
				array('pos_kasir_id',$kasir_log_id,'='),
				array('pos_id',$this->input->get('nomor'),'=')
			))
			->set_sort(array('pos_bayar_is','desc'))
			->configure(
				array(
					'datasouce_url' => base_url("rekapan/grid_detail_perkasir/".$kasir_log_id."?datasource&" . get_query_string()),
					'grid_columns'  => array(
						array(
							'field' => 'pos_id',
							'title' => 'TRX ID',
						),
						array(
							'field' => 'waktu_transaksi',
							'title' => 'Waktu TRX',
						),
						array(
							'field' => 'pos_total',
							'title' => 'Total',
							'align'	=> 'right',
							'format'=> 'number'
						),
						array(
							'field' => 'pos_jumlah_item',
							'title' => 'Jumlah Item',
							'format'=> 'number'
						),
						array(
							'field' => 'status_bayar',
							'title' => 'Status Bayar',
						),
						array(
							'field' => 'status_return',
							'title' => 'Status Return',
						),
					),
					'action'	=> $action
				)
			)->output();
	}

	public function grid_return_detail_perkasir($kasir_log_id)
	{
		$SQL = "select *, coalesce(DATE_FORMAT( return_created_at, '%d %M %Y %T' ),'-') as waktu_return, return_id as id from return_master";

		$action['detail']     = array(
            'jsf'          => 'detail_return'
		);
		$this->load->library('smart_grid_kendo');
		return $this->smart_grid_kendo
			->configure(
				array(
					'datasouce_url' => base_url("rekapan/grid_return_detail_perkasir/".$kasir_log_id."?datasource&" . get_query_string()),
					'grid_columns'  => array(
						array(
							'field' => 'return_id',
							'title' => 'TRX ID',
						),
						array(
							'field' => 'waktu_return',
							'title' => 'Waktu TRX',
						),
						array(
							'field' => 'return_total',
							'title' => 'Total',
							'align'	=> 'right',
							'format'=> 'number'
						),
						array(
							'field' => 'return_jumlah_item',
							'title' => 'Jumlah Item',
						),
						array(
							'field' => 'return_nama_pelanggan',
							'title' => 'Nama Pelanggan',
						),
						array(
							'field' => 'return_alasan',
							'title' => 'Alasan Return',
						),
					),
					'action'	=> $action
				)
			)
			->set_query($SQL, array(
				array('return_kasir_log_id',$kasir_log_id,'=')
			))
			->set_sort(array('return_id','desc'))->output();
	}

	public function pertanggal()
	{
		$tgl_awal = $this->input->get('tgl_awal');
		$tgl_akhir = $this->input->get('tgl_akhir');
		$data['grid'] = $this->grid_pejualan_pertanggal($tgl_awal, $tgl_akhir);
		$data['return'] = $this->grid_return_pertanggal($tgl_awal, $tgl_akhir);
		$data['search'] = $this->search();
		return $this->render('rekapan/pertanggal', $data);
	}

	public function search()
	{
		$tgl_awal = $this->input->get('tgl_awal');
		$tgl_akhir = $this->input->get('tgl_akhir');
		$nomor = $this->input->get('nomor');

		$this->load->library('smart_form');
		return $this->smart_form
			->set_form_type('search')
			->set_form_method('GET')
			->set_submit_label('Cari')
			->add('nomor', 'No. TRX.', 'text', true, $nomor, 'style="width:100%;" ')
			->add('tgl_awal', 'Tgl. Awal', 'date', true, ($tgl_awal=='' ? date("Y-m-d") : $tgl_awal), 'style="width:100%;" ')
			->add('tgl_akhir', 'Tgl. Akhir', 'date', true, ($tgl_akhir=='' ? date("Y-m-d") : $tgl_akhir), 'style="width:100%;" ')->output();
	}

	public function grid_pejualan_pertanggal()
	{
		$SQL = "select *, pos_id as id, case when pos_bayar_is=1 then 'Terbayar' else 'Belum Bayar' end as status_bayar, case when pos_return_is=1 then 'Return' else '' end status_return from pos_master";
		
		$action['detail']     = array(
            'jsf'          => 'detail_pos'
		);
		$this->load->library('smart_grid_kendo');
		return $this->smart_grid_kendo
			->configure(
				array(
					'datasouce_url' => base_url("rekapan/grid_pejualan_pertanggal?datasource&" . get_query_string()),
					'grid_columns'  => array(
						array(
							'field' => 'pos_id',
							'title' => 'TRX ID',
						),
						array(
							'field' => 'pos_created_at',
							'title' => 'Waktu TRX',
							'format'=> 'datetime'
						),
						array(
							'field' => 'pos_total',
							'title' => 'Total',
							'align'	=> 'right',
							'format'=> 'number'
						),
						array(
							'field' => 'pos_jumlah_item',
							'title' => 'Jumlah Item',
							'format'=> 'number'
						),
						array(
							'field' => 'status_bayar',
							'title' => 'Status Bayar',
						),
						array(
							'field' => 'status_return',
							'title' => 'Status Return',
						),
					),
					'action'	=> $action
				)
			)
			->set_query($SQL, array(
				array("date_format(pos_created_at,'%Y-%m-%d' )",$this->input->get('tgl_awal'),'>='),
				array("date_format(pos_created_at,'%Y-%m-%d' )",$this->input->get('tgl_akhir'),'<='),
				array("pos_id",$this->input->get('nomor'),'=')
			))
			->set_sort(array('pos_bayar_is','desc'))->output();
	}

	public function grid_return_pertanggal()
	{
		$SQL = "select *, coalesce(DATE_FORMAT( return_created_at, '%d %M %Y %T' ),'-') as waktu_return, return_id as id from return_master";

		$action['detail']     = array(
            'jsf'          => 'detail_return'
		);
		$this->load->library('smart_grid_kendo');
		return $this->smart_grid_kendo
			->configure(
				array(
					'datasouce_url' => base_url("rekapan/grid_return_pertanggal?datasource&" . get_query_string()),
					'grid_columns'  => array(
						array(
							'field' => 'return_id',
							'title' => 'TRX ID',
						),
						array(
							'field' => 'waktu_return',
							'title' => 'Waktu TRX',
						),
						array(
							'field' => 'return_total',
							'title' => 'Total',
							'align'	=> 'right',
							'format'=> 'number'
						),
						array(
							'field' => 'return_jumlah_item',
							'title' => 'Jumlah Item',
						),
						array(
							'field' => 'return_nama_pelanggan',
							'title' => 'Nama Pelanggan',
						),
						array(
							'field' => 'return_alasan',
							'title' => 'Alasan Return',
						),
					),
					'action'	=> $action
				)
			)
			->set_query($SQL, array(
				array("date_format(return_created_at,'%Y-%m-%d' )",$this->input->get('tgl_awal'),'>='),
				array("date_format(return_created_at,'%Y-%m-%d' )",$this->input->get('tgl_akhir'),'<='),
				array("return_id",$this->input->get('nomor'),'=')
			))
			->set_sort(array('return_id','desc'))
			->output();
	}

	public function detail_penjualan($pos_id)
	{
		$data['grid'] = $this->grid_penjualan_detail($pos_id);
		return $this->render_popup('rekapan/detail_penjualan', $data);
	}

	public function grid_penjualan_detail($pos_id)
	{
		$SQL = "select *, pos_det_id as id from pos_detail left join ref_produk on ref_produk_id = pos_det_produk_id where pos_det_master_id=".$pos_id;

		$this->load->library('smart_grid_kendo');

		return $this->smart_grid_kendo
			->configure(
				array(
					'datasouce_url' => base_url("rekapan/grid_penjualan_detail/".$pos_id."?datasource&" . get_query_string()),
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
							'field' => 'pos_det_sub_total',
							'title' => 'Total',
							'format' => "number",
							'align' => 'right'
						)
					),
				)
			)
			->set_query($SQL, array())
			->output();
	}

	public function detail_return($return_id)
	{
		$data['grid'] = $this->grid_return_detail($return_id);
		return $this->render_popup('rekapan/detail_penjualan', $data);
	}

	public function grid_return_detail($return_id)
	{
		$SQL = "select *, return_det_id as id from return_detail left join ref_produk on ref_produk_id = return_det_produk_id where return_det_master_id=".$return_id;

		$this->load->library('smart_grid_kendo');
		return $this->smart_grid_kendo
			->configure(
				array(
					'datasouce_url' => base_url("rekapan/grid_return_detail/".$return_id."?datasource&" . get_query_string()),
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
							'field' => 'return_det_sub_total',
							'title' => 'Total',
							'format' => "number",
							'align' => 'right'
						)
					)
				)
			)
			->set_query($SQL, array())
			->output();
	}
}
