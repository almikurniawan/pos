<?php
defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Produk extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$data['grid'] = $this->grid();
		$data['search'] = $this->search();
		$this->render('referensi/produk', $data);
	}

	public function search()
	{
		$produk = $this->input->get('produk');
		$barcode = $this->input->get('barcode');

		$this->load->library('smart_form');
		$this->smart_form->set_form_type('search');
		$this->smart_form->set_form_method('GET');
		$this->smart_form->set_submit_label('Cari');
		$this->smart_form->add('produk', 'Nama', 'text', false, $produk, 'style="width:100%;" ');
		$this->smart_form->add('barcode', 'Barcode', 'text', false, $barcode, 'style="width:100%;" ');
		
		return $this->smart_form->output();
	}

	public function import()
	{
		$data['form'] = $this->form_import();
		$this->render('referensi/produk_import', $data);
	}

	public function form_import()
	{
		$this->load->library('smart_form');
		$this->smart_form->set_attribute_form('class="form-horizontal" enctype="multipart/form-data"');
		$this->smart_form->set_submit_label('Import');
		$this->smart_form->set_submit_icon('k-icon k-i-upload');
		$this->smart_form->add('file', 'File Excel', 'file', true, '', 'style="width:100%;"');

		if ($this->smart_form->formVerified()) {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			$spreadsheet = $reader->load($_FILES['file']['tmp_name']);
			$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
			for($i = 4;$i < count($sheetData);$i++)
			{
				$kategori_string= trim($sheetData[$i]['A']);
				$satuan_string	= trim($sheetData[$i]['D']);
				$suplier_string	= trim($sheetData[$i]['H']);

				$data['ref_produk_nama_produk']	= trim($sheetData[$i]['B']);
				$data['ref_produk_nama_produk_cetak']	= trim($sheetData[$i]['C']);
				$data['ref_produk_harga_beli'] 			= trim($sheetData[$i]['E']);
				$data['ref_produk_harga_jual'] 			= trim($sheetData[$i]['F']);
				$data['ref_produk_stok']		= trim($sheetData[$i]['G']);
				$data['ref_produk_keterangan']	= trim($sheetData[$i]['I']);
				$data['ref_produk_barcode']		= trim($sheetData[$i]['J']);
				$id		= trim($sheetData[$i]['K']);

				if($data['ref_produk_nama_produk']==''){
					return $this->smart_form->output('success','Sukses Import Data Produk');
				}

				$kategori = $this->db->get_where('ref_kategori', array('ref_kategori_label'=> $kategori_string))->row_array();
				if(!empty($kategori)){
					$data['ref_produk_kategori'] = $kategori['ref_kategori_id'];
				}

				$satuan = $this->db->get_where('ref_satuan', array('ref_satuan_label'=> $satuan_string))->row_array();
				if(!empty($satuan)){
					$data['ref_produk_satuan'] = $satuan['ref_satuan_id'];
				}

				$suplier = $this->db->get_where('ref_suplier', array('ref_suplier_nama'=> $suplier_string))->row_array();
				if(!empty($suplier)){
					$data['ref_produk_id_suplier'] = $suplier['ref_suplier_id'];
				}

				$cek_produk = $this->db->get_where("ref_produk", array('lower(ref_produk_nama_produk)'=> strtolower($data['ref_produk_nama_produk'])))->row_array();
				if(empty($cek_produk)){
					$this->db->insert("ref_produk", $data);
				}else{
					$this->db->update("ref_produk", $data, 'ref_produk_id='.$cek_produk['ref_produk_id']);
				}
			}
			return $this->smart_form->output('success','Sukses Import Data Produk');
		}else{
			return $this->smart_form->output();
		}
	}
	
	public function download()
	{
		$styleBold = [
            'font' => [
                'bold' => true,
            ]
        ];

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$sheet->setCellValue('A1', 'List Produk');

		$header = array(
			'Kategori',
			'Nama',
			'Nama cetakan',
			'Satuan',
			'Harga beli',
			'Harga jual',
			'Stok',
			'Suplier',
			'Keterangan',
			'Barcode',
			'ID'
		);

		$sheet->getColumnDimension('A')->setWidth(12);
		$sheet->getColumnDimension('B')->setWidth(20);
		$sheet->getColumnDimension('C')->setWidth(15);
		$sheet->getColumnDimension('D')->setWidth(10);
		$sheet->getColumnDimension('E')->setWidth(15);
		$sheet->getColumnDimension('F')->setWidth(15);
		$sheet->getColumnDimension('G')->setWidth(10);
		$sheet->getColumnDimension('H')->setWidth(15);
		$sheet->getColumnDimension('I')->setWidth(25);
		$sheet->getColumnDimension('J')->setWidth(35);
		$sheet->getColumnDimension('K')->setWidth(10);

		//set first row bold
		$sheet->getStyle('A3:K3' )->applyFromArray($styleBold);
		$sheet->getStyle('A1' )->applyFromArray($styleBold);
		$sheet->getStyle('J')
		->getNumberFormat()->setFormatCode('#');

		$row = 3;
		$index = 0;
		for ($char = 'A'; $char <= 'K'; $char++) {
			$sheet->setCellValue($char.$row, $header[$index]);
			$index++;
		}

		$data_kategori = $this->db->get("ref_kategori")->result_array();
		$kategori = array();
		foreach ($data_kategori as $key => $value) {
			$kategori[] =$value['ref_kategori_label'];
		}

		$data_satuan = $this->db->get("ref_satuan")->result_array();
		$satuan = array();
		foreach ($data_satuan as $key => $value) {
			$satuan[] =$value['ref_satuan_label'];
		}

		$data_suplier = $this->db->get("ref_suplier")->result_array();
		$suplier = array();
		foreach ($data_suplier as $key => $value) {
			$suplier[] =$value['ref_suplier_nama'];
		}

		for($i=3; $i<=10000; $i++){
			$objValidation = $sheet->getCell("A".$i)->getDataValidation();
			$objValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
			$objValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
			$objValidation->setAllowBlank(true);
			$objValidation->setShowInputMessage(true);
			$objValidation->setShowDropDown(true);
			$objValidation->setPromptTitle('Pilih Kategori');
			$objValidation->setPrompt('Please pick a value from the drop-down list.');
			$objValidation->setErrorTitle('Input error');
			$objValidation->setError('Value is not in list');
			$objValidation->setFormula1('"'.implode(',', $kategori).'"');
	
	
			$objValidation = $sheet->getCell("D".$i)->getDataValidation();
			$objValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
			$objValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
			$objValidation->setAllowBlank(true);
			$objValidation->setShowInputMessage(true);
			$objValidation->setShowDropDown(true);
			$objValidation->setPromptTitle('Pilih Satuan');
			$objValidation->setPrompt('Please pick a value from the drop-down list.');
			$objValidation->setErrorTitle('Input error');
			$objValidation->setError('Value is not in list');
			$objValidation->setFormula1('"'.implode(',', $satuan).'"');
	
			$objValidation = $sheet->getCell("H".$i)->getDataValidation();
			$objValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
			$objValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
			$objValidation->setAllowBlank(true);
			$objValidation->setShowInputMessage(true);
			$objValidation->setShowDropDown(true);
			$objValidation->setPromptTitle('Pilih Suplier');
			$objValidation->setPrompt('Please pick a value from the drop-down list.');
			$objValidation->setErrorTitle('Input error');
			$objValidation->setError('Value is not in list');
			$objValidation->setFormula1('"'.implode(',', $suplier).'"');

		}

		$row++;
		$data_produk = $this->db->query("select * from ref_produk left join ref_kategori on ref_kategori_id = ref_produk_kategori left join ref_satuan on ref_satuan_id = ref_produk_satuan left join ref_suplier on ref_suplier_id = ref_produk_id_suplier")->result_array();
		foreach ($data_produk as $key => $value) {
			$sheet->setCellValue('A'.$row, $value['ref_kategori_label']);
			$sheet->setCellValue('B'.$row, $value['ref_produk_nama_produk']);
			$sheet->setCellValue('C'.$row, $value['ref_produk_nama_produk_cetak']);
			$sheet->setCellValue('D'.$row, $value['ref_satuan_label']);
			$sheet->setCellValue('E'.$row, $value['ref_produk_harga_beli']);
			$sheet->setCellValue('F'.$row, $value['ref_produk_harga_jual']);
			$sheet->setCellValue('G'.$row, $value['ref_produk_stok']);
			$sheet->setCellValue('H'.$row, $value['ref_suplier_nama']);
			$sheet->setCellValue('I'.$row, $value['ref_produk_keterangan']);
			$sheet->setCellValue('J'.$row, $value['ref_produk_barcode']);
			$sheet->setCellValue('K'.$row, $value['ref_produk_id']);
			
			$row++;
		}

		$filename = "list_produk_".time().".xlsx";
		try {
			$writer = new Xlsx($spreadsheet);
			$writer->save($filename);
			$content = file_get_contents($filename);
		} catch(Exception $e) {
			exit($e->getMessage());
		}
		
		header("Content-Disposition: attachment; filename=".$filename);
		
		unlink($filename);
		exit($content);
	}

	public function grid()
	{
		$SQL = "select *, ref_produk_id as id from ref_produk left join ref_satuan on ref_satuan_id = ref_produk_satuan left join ref_kategori on ref_produk_kategori = ref_kategori_id left join ref_suplier on ref_suplier_id = ref_produk_id_suplier";

        $this->load->library('smart_grid_kendo');
        $this->smart_grid_kendo->set_query($SQL, array(
			array("lower(ref_produk_nama_produk)",$this->input->get('produk')),
			array("ref_produk_barcode",$this->input->get('barcode')),
		));
		$this->smart_grid_kendo->set_sort(array('id','desc'));

        $action['edit']     = array(
            'link'          => 'referensi/produk/edit/'
        );
        $action['delete']     = array(
            'jsf'          => 'delete_produk'
        );
		$this->smart_grid_kendo->configure(
			array(
				'datasouce_url' => base_url("referensi/produk/grid?datasource&" . get_query_string()),
				'grid_columns'  => array(
					array(
						'field' => 'id',
						'title' => 'ID',
						'width'	=> 40
					),
					array(
						'field' => 'ref_produk_barcode',
						'title' => 'Barcode'
					),
					array(
						'field' => 'ref_produk_nama_produk',
						'title' => 'Nama'
					),
					array(
						'field' => 'ref_produk_stok',
						'title' => 'Stok'
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
						'field' => 'ref_produk_harga_jual',
						'title' => 'Harga Jual',
						'format' => "number",
						'align' => 'right'
					),
					array(
						'field' => 'ref_suplier_nama',
						'title' => 'Suplier',
					),
                ),
				'action'        => $action,
				// 'head_left'		=> array('add'=>base_url('referensi/produk/add'))
			)
		);
		$grid = $this->smart_grid_kendo->output();
		return $grid;
	}

	public function add()
	{
		$data['form'] = $this->form();
		$this->render('referensi/produk_edit', $data);
	}

	public function edit($id)
	{
		$data['form'] = $this->form($id);
		$this->render('referensi/produk_edit', $data);
	}
	
	public function delete()
	{
		$id = $this->input->post('id');
		$this->db->delete('ref_produk', array('ref_produk_id'=>$id));
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
			$data = $this->db->get_where('ref_produk', array('ref_produk_id' => $id))->row_array();
		}
		$this->smart_form->add('ref_produk_nama_produk', 'Nama Produk', 'text', true, $data['ref_produk_nama_produk'], 'style="width:100%;"');
		$this->smart_form->add('ref_produk_nama_produk_cetak', 'Cetak Struk', 'text', false, $data['ref_produk_nama_produk_cetak'], 'style="width:100%;"');
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
		$this->smart_form->add('ref_produk_barcode', 'Barcode', 'text', false, $data['ref_produk_barcode'], 'style="width:100%;" autofocus');

		if ($this->smart_form->formVerified()) {
			if ($id!=null) {
				$data_update = array(
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
					'ref_produk_id_suplier'	=> $this->input->post('ref_produk_id_suplier'),
					'ref_produk_is_ada'		=> 1
				);
				$this->db->update("ref_produk", $data_update, array('ref_produk_id' => $id));
				die('<script>window.location.href="'.base_url("referensi/produk").'";</script>');
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
					'ref_produk_id_suplier'	=> $this->input->post('ref_produk_id_suplier'),
					'ref_produk_is_ada'		=> 1
				);
				$this->db->insert("ref_produk", $data_insert);
				return $this->smart_form->output('success','Sukses Insert Baru');
			}
		} else {
			return $this->smart_form->output();
		}
	}
}
