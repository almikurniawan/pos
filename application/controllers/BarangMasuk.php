<?php
defined('BASEPATH') or exit('No direct script access allowed');

class BarangMasuk extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$data['form'] = $this->form();
		$data['grid'] = $this->grid();
		$data['search'] = $this->search();
		$this->render('barang_masuk', $data);
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
	
	public function search()
	{
		$tgl_awal = $this->input->get('tgl');

		$this->load->library('smart_form');
		$this->smart_form->set_form_type('search');
		$this->smart_form->set_form_method('GET');
		$this->smart_form->set_submit_label('Cari');
		$this->smart_form->add('tgl', 'Tgl.', 'date', true, ($tgl_awal=='' ? date("Y-m-d") : $tgl_awal), 'style="width:100%;" ');
		
		return $this->smart_form->output();
	}
    
    public function grid()
	{
		
		$SQL = "select *, entered_id as id, (select count(*) from entered_item where entered_item_master_id=entered_id) as jumlah_item from entered_master left join user on entered_user_id = user_id";

		$this->load->library('smart_grid_kendo');
		$this->smart_grid_kendo->set_query($SQL, array(
			array("entered_tanggal",$this->input->get('tgl'),'='),
		));
		$this->smart_grid_kendo->set_sort(array('id','desc'));

		$action['edit']     = array(
            'link'          => 'barangMasuk/update/'
        );
		$this->smart_grid_kendo->configure(
			array(
				'datasouce_url' => base_url("barangMasuk/grid?datasource&" . get_query_string()),
				'grid_columns'  => array(
					array(
						'field' => 'entered_tanggal',
						'title' => 'Tanggal'
					),
					array(
						'field' => 'entered_keterangan',
						'title' => 'Keterangan',
						
					),
					array(
						'field' => 'user_name',
						'title' => 'User',
						'width'	=> 20
					),
					array(
						'field' => 'jumlah_item',
						'title' => 'Jumlah Barang',
						'width'	=> 20
					)
				),
				'action'=> $action
			)
		);
		$grid = $this->smart_grid_kendo->output();
		return $grid;
	}

	public function add()
	{
		$data['form'] = $this->form_add();
		return $this->render('barang_masuk_add', $data);
	}

	public function form_add()
	{
		$this->load->library('smart_form');		
        $this->smart_form->set_submit_label('Buat');
		$this->smart_form->add('entered_tanggal', 'Tanggal', 'date', true, date("Y-m-d"), 'style="width:100%;"');
		$this->smart_form->add('entered_keterangan', 'Keterangan', 'textArea', true, '', 'style="width:100%;" rows="5" cols="7" autofocus');

		if ($this->smart_form->formVerified()) {
			$data_insert = array(
				'entered_tanggal'=> $this->input->post('entered_tanggal'),
				'entered_keterangan'=> $this->input->post('entered_keterangan'),
				'entered_user_id'=> $this->user['user_id'],
				'entered_created_at'=> date("Y-m-d H:i:s"),
			);
			$this->db->insert("entered_master", $data_insert);
			$id = $this->db->insert_id();
			redirect('barangMasuk/update/'.$id);
		} else {
			return $this->smart_form->output();
		}
	}

	public function form_update($id, $resume = false)
	{
		$data = $this->db->get_where("entered_master", array('entered_id'=>$id))->row_array();

		$this->load->library('smart_form');
		$this->smart_form->set_submit_label('Simpan');
		$this->smart_form->set_resume($resume);
		$this->smart_form->add('entered_tanggal', 'Tanggal', 'date', true, $data['entered_tanggal'], 'style="width:100%;"');
		$this->smart_form->add('entered_keterangan', 'Keterangan', 'textArea', true, $data['entered_keterangan'], 'style="width:100%;" rows="5" cols="7"');

		if ($this->smart_form->formVerified()) {
			$data_update = array(
				'entered_tanggal'=> $this->input->post('entered_tanggal'),
				'entered_keterangan'=> $this->input->post('entered_keterangan')
			);
			$this->db->update("entered_master", $data_update, 'entered_id='.$id);
			redirect('barangMasuk/update/'.$id);
		} else {
			return $this->smart_form->output();
		}
	}

	public function update($id)
	{
		$cek_master = $this->db->get_where("entered_master", array('entered_id'=>$id))->row_array();
		if($cek_master['entered_lock_is']==1){
			$data['form'] = $this->form_update($id, true);
			$data['grid'] = $this->grid_detail_resume($id);
			return $this->render('barang_masuk_resume', $data);
		}else{
			$data['form'] = $this->form_update($id);
			$data['form_detail'] = $this->form_detail($id);
			$data['form_detail_manual'] = $this->form_detail_manual($id);
			$data['grid'] = $this->grid_detail($id);
			$data['id'] = $id;
			return $this->render('barang_masuk_update', $data);
		}
	}

	public function form_detail($id)
	{
		$this->load->library('smart_form');		
		$this->smart_form->set_submit_label('Masukan');
		$this->smart_form->set_template('sf_barang_masuk_otomatis');
		$this->smart_form->set_submit_name('form_detail');
		$this->smart_form->add('barcode', 'Barcode', 'text', true, '', 'style="width:100%;" autofocus');
		$this->smart_form->add('jumlah_barang', 'Jumlah', 'number', true, 1, 'style="width:100%;"');

		if ($this->smart_form->formVerified()) {
			$data_produk = $this->db->get_where('ref_produk',array('ref_produk_barcode'=> $this->input->post('barcode')))->row_array();
			if(!$data_produk){
				$this->session->set_flashdata('error','Data produk dengan barcode "'.$this->input->post('barcode').'" tidak ditemukan');
				redirect('barangMasuk/update/'.$id);
			}else{
				//cek jika sudah ada produk tsb
				$cek = $this->db->get_where('entered_item', array('entered_item_produk_id'=>$data_produk['ref_produk_id'],'entered_item_master_id'=> $id))->row_array();
				if(!$cek){
					$data_insert = array(
						'entered_item_produk_id'=> $data_produk['ref_produk_id'],
						'entered_item_jumlah'	=> $this->input->post('jumlah_barang'),
						'entered_item_master_id'=> $id
					);
					$this->db->insert("entered_item", $data_insert);
				}else{
					$new_jumlah = ((int)$this->input->post('jumlah_barang') + (int)$cek['entered_item_jumlah']);
					$data_update = array(
						'entered_item_jumlah'	=> $new_jumlah
					);
					$this->db->update("entered_item", $data_update,'entered_item_id='.$cek['entered_item_id']);
				}
			}
			return redirect('barangMasuk/update/'.$id);
		} else {
			return $this->smart_form->output();
		}
	}

	public function form_detail_manual($id)
	{
		$this->load->library('smart_form');
		$this->smart_form->set_template('sf_barang_masuk_manual');
		$this->smart_form->set_submit_label('Masukan');
		$this->smart_form->set_submit_name('form_detail_manual');
		$this->smart_form->add('nama_barang', 'Nama Barang', 'autoComplete', true, '', 'style="width:100%;" ', array(
			'url'	=> base_url('home/get_produk')
		));
		$this->smart_form->add('jumlah_barang_manual', 'Jumlah', 'number', true, '1', 'style="width:100%;" ');
		
		if ($this->smart_form->formVerified()) {
			$data_produk = $this->db->get_where('ref_produk',array('ref_produk_nama_produk'=> $this->input->post('nama_barang')))->row_array();
			if(!$data_produk){
				$this->session->set_flashdata('error', 'Data produk dengan Nama "'.$this->input->post('nama_barang').'" tidak ditemukan');
				redirect('barangMasuk/update/'.$id);
			}else{
				//cek jika sudah ada produk tsb
				$cek = $this->db->get_where('entered_item', array('entered_item_produk_id'=>$data_produk['ref_produk_id'],'entered_item_master_id'=> $id))->row_array();
				if(!$cek){
					$data_insert = array(
						'entered_item_produk_id'=> $data_produk['ref_produk_id'],
						'entered_item_jumlah'	=> $this->input->post('jumlah_barang_manual'),
						'entered_item_master_id'=> $id
					);
					$this->db->insert("entered_item", $data_insert);
				}else{
					$new_jumlah = ((int)$this->input->post('jumlah_barang_manual') + (int)$cek['entered_item_jumlah']);
					$data_update = array(
						'entered_item_jumlah'	=> $new_jumlah
					);
					$this->db->update("entered_item", $data_update,'entered_item_id='.$cek['entered_item_id']);
				}
			}
			return redirect('barangMasuk/update/'.$id);
		} else {
			return $this->smart_form->output();
		}
	}

	public function grid_detail($id)
	{
		$SQL = "SELECT
					entered_item_id as id,
					ref_produk.*,
					ref_kategori_label,
					ref_satuan_label,
					ref_produk_stok as stok_saat_ini,
					entered_item.entered_item_jumlah,
					(entered_item.entered_item_jumlah + ref_produk_stok) as new_stok
				FROM
					entered_item
					LEFT JOIN ref_produk ON ref_produk_id = entered_item_produk_id 
					left join ref_kategori on ref_kategori_id = ref_produk_kategori
					left join ref_satuan on ref_satuan_id = ref_produk_satuan
				where entered_item_master_id=".$id;

		$this->load->library('smart_grid_kendo');
		
        $action['delete']     = array(
            'jsf'          => 'delete_item'
		);
		$action['edit']     = array(
			'jsf'          => 'update_item'
        );
		$this->smart_grid_kendo->configure(
			array(
				'datasouce_url' => base_url("barangMasuk/grid_detail/".$id."?datasource&" . get_query_string()),
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
						'field' => 'stok_saat_ini',
						'title' => 'Stok Saat ini'
					),
					array(
						'field' => 'entered_item_jumlah',
						'title' => 'Penambahan Stok',
						'width'	=> 20
					),
					array(
						'field' => 'new_stok',
						'title' => 'Stok Nanti',
					)
                ),
				'action'        => $action
			)
		);
		$this->smart_grid_kendo->set_query($SQL, array());
		$grid = $this->smart_grid_kendo->output();
		return $grid;
	}

	public function grid_detail_resume($id)
	{
		$SQL = "SELECT
					entered_item_id AS id,
					ref_produk.*,
					ref_kategori_label,
					ref_satuan_label,
					mutasi_jumlah_sebelumnya AS stok_saat_ini,
					entered_item_jumlah,
					mutasi_produk_balance AS new_stok 
				FROM
					entered_item
					LEFT JOIN ref_produk ON ref_produk_id = entered_item_produk_id
					LEFT JOIN ref_kategori ON ref_kategori_id = ref_produk_kategori
					LEFT JOIN ref_satuan ON ref_satuan_id = ref_produk_satuan
					left join mutasi_stok on mutasi_id_entered_item = entered_item_id
				where entered_item_master_id=".$id;

		$this->load->library('smart_grid_kendo');
		$this->smart_grid_kendo->configure(
			array(
				'datasouce_url' => base_url("barangMasuk/grid_detail_resume/".$id."?datasource&" . get_query_string()),
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
						'field' => 'stok_saat_ini',
						'title' => 'Stok Saat ini'
					),
					array(
						'field' => 'entered_item_jumlah',
						'title' => 'Penambahan Stok',
						'width'	=> 20
					),
					array(
						'field' => 'new_stok',
						'title' => 'Stok Nanti',
					)
                ),
			)
		);
		$this->smart_grid_kendo->set_query($SQL, array());
		$grid = $this->smart_grid_kendo->output();
		return $grid;
	}

	public function update_item($id)
	{
		$data['form']	= $this->form_update_item($id);
		$this->render_popup('update_item', $data);
	}

	public function form_update_item($id)
	{
		$this->load->library('smart_form');
		$data = $this->db->get_where("entered_item", array('entered_item_id'=>$id))->row_array();
		$this->smart_form->add('entered_item_jumlah', 'Jumlah', 'number', true, $data['entered_item_jumlah'], 'style="width:100%;" autofocus');

		if ($this->smart_form->formVerified()) {
			$data_update = array(
				'entered_item_jumlah'=>$this->input->post('entered_item_jumlah')
			);
			$this->db->update('entered_item', $data_update, 'entered_item_id='.$id);
			return '<script> window.parent.gridReload(); window.parent.$("#dialog").data("kendoWindow").close();</script>';
		} else {
			return $this->smart_form->output();
		}
	}

	public function deleteItem()
	{
		$id = $this->input->post('id');
		$this->db->delete("entered_item","entered_item_id=".$id);
		return $this->response_json(array(
			'status'	=> true,
			'message'	=> 'Sukses delete item'
		));
	}

	public function proses($id)
	{
		$data = $this->db->query("SELECT
									entered_item_id,
									ref_produk_id,
									entered_item_jumlah as jumlah_penambahan,
									ref_produk_stok as jumlah_saat_ini,
									(entered_item_jumlah + ref_produk_stok) as jumlah_updated
									
								FROM
									entered_item
								LEFT JOIN ref_produk on ref_produk_id = entered_item_produk_id
								where entered_item_master_id = ".$id)->result_array();
		$data_insert = array();
		foreach ($data as $key => $value) {
			$data_insert[] = array(
				'mutasi_produk_id'	=> $value['ref_produk_id'],
				'mutasi_jumlah'		=> $value['jumlah_penambahan'],
				'mutasi_jumlah_sebelumnya'=> $value['jumlah_saat_ini'],
				'mutasi_produk_balance'	=> $value['jumlah_updated'],
				'mutasi_jenis_mutasi'	=> 2,
				'mutasi_operator'		=> '+',
				'mutasi_created_at'		=> date("Y-m-d H:i:s"),
				'mutasi_created_by'		=> $this->user['user_id'],
				'mutasi_id_entered_item'=> $value['entered_item_id']
			);

			$this->db->update("ref_produk", array(
				'ref_produk_stok'	=> $value['jumlah_updated']
			),'ref_produk_id='.$value['ref_produk_id']);
		}

		$this->db->insert_batch('mutasi_stok', $data_insert);
		$this->db->update("entered_master", array(
			'entered_lock_is' => 1,
			'entered_lock_at' => date("Y-m-d H:i:s")
		),'entered_id='.$id);
		$this->session->set_flashdata('success', 'Sukses melakukan mutasi stok');
		return redirect('barangMasuk/update/'.$id);
	}
	public function list_pdobel($barcode)
	{
		$this->render_popup('bayar', array(
			'form'=> $this->form_list_pdobel($barcode)
		));
	}
	public function form_list_pdobel($barcode){
		$tr ='';
		$data = $this->db->query("SELECT * FROM ref_produk where ref_produk_barcode = '".$barcode."'")->result_array();
		foreach ($data as $key => $value) {
			$tr .= '<tr onclick="window.parent.pilih_produk('.$value['ref_produk_id'].')">
					<td>'.$value['ref_produk_nama_produk'].'</td>
					<td>'.$value['ref_produk_harga_jual'].'</td>
				</tr>';
		}
		return '<style>
		body{
		  font-size : 25px;
			font-weight : bold;
		}
		table{
		  border-collapse: collapse; 
		  width:100%
		}
		tr{
		  border : 1px solid #9e9e9e;
		}
		td{
		  padding : 15px;
		}
		</style>
		<body>
			<table>
				<tr>
					<td>Nama</td>
					<td>Harga</td>
				</tr>
				'.$tr.'
			</table>
		</body>
		';
	}
	public function ubah_harga($id)
	{
		$this->render_popup('bayar', array(
			'form'=> $this->form_ubah_harga($id)
		));
	}
	public function form_ubah_harga($id)
	{
		$this->load->library('smart_form');
		$this->smart_form
			->set_submit_label('Simpan')
			->add('harga', 'Harga', 'number', false, '', 'style="width:100%;"');

		if ($this->smart_form->formVerified()) {
			$this->db->update("ref_produk",['ref_produk_harga_jual' => $this->input->post('harga')],"ref_produk_id=".$id);
			return '<script> window.parent.pilih_produk('.$id.');window.parent.gridReload(); window.parent.get_total(); window.parent.$("#dialog").data("kendoWindow").close();</script>';
		} else {
			return $this->smart_form->output();
		}
	}
}
