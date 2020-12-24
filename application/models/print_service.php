<?php
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class Print_service extends Base_Model{
    public function __construct()
    {
        parent::__construct();
    }

    public function print_struk($pos_id)
    {
        $data_kasir = $this->db->get_where("kasir", array('kasir_id'=>$this->kasir_log['kasir_log_kasir_id']))->row_array();
        if($data_kasir['kasir_printer_use']==1){

            $data_pembelian = $this->db->query("SELECT
                ref_produk_nama_produk,
                ref_produk_nama_produk_cetak,
                pos_det_jumlah_item,
                pos_det_sub_total
            FROM
                pos_detail 
                LEFT JOIN ref_produk on ref_produk_id = pos_det_produk_id
            WHERE
                pos_det_master_id = ".$pos_id)->result_array();
    
            try {
                if($data_kasir['kasir_print_konektor']=='USB'){
                    $connector = new WindowsPrintConnector($data_kasir['kasir_print_name_ip']);
                }else{
                    $connector = new NetworkPrintConnector($data_kasir['kasir_print_name_ip'], 9100);
                }
                $printer = new Printer($connector);
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer -> text("Toko Mahreen\nJl. bendung Gerak Waru Turi\nTelp. 085784752779\n");
                $printer->feed(2);
                $printer -> text("No. TRX : ".$pos_id."\n");
                $printer->feed(1);
                $total = 0;
                foreach ($data_pembelian as $key => $value) {
                    $total += (float) $value['pos_det_sub_total'];
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer -> text($value['ref_produk_nama_produk']."(x".$value['pos_det_jumlah_item'].")\n");
                    $printer -> text(number_format($value['pos_det_sub_total'],0,",",".")."\n");
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer -> text("_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ \n");
                }
                $printer -> text("Total.");
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer -> text(number_format($total,0,",",".")."\n");
                $printer->feed(2);
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer -> text("Periksa barang sebelum dibeli.\n");
                $printer->feed(1);
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer -> text("Terima Kasih, Selamat Belanja Kembali.\n");
                $printer->feed(4);
                $printer -> close();
            } catch (Exception $e) {
                echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
            }
        }
    }
    
    public function print_tutup_kasir($kasir_log_id)
    {
        $data = $this->db->get_where("kasir_log", array('kasir_log_id'=> $kasir_log_id))->row_array();
        try {
            $connector = new WindowsPrintConnector("pos-58");
            $printer = new Printer($connector);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer -> text("Toko Mahreen\nJl. bendung Gerak Waru Turi\nTelp. 085784752779\n");
            $printer->feed(2);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $text = str_pad("Buka", 5, " ", STR_PAD_RIGHT) . str_pad(date_format(date_create($data['kasir_log_waktu_buka']),'H:m d-m-Y'), 25, " ", STR_PAD_LEFT);
            $printer -> text("$text\n");
            $text = str_pad("Tutup", 5, " ", STR_PAD_RIGHT) . str_pad(date_format(date_create($data['kasir_log_waktu_tutup']),'H:m d-m-Y'), 25, " ", STR_PAD_LEFT);
            $printer -> text("$text\n");
            $text = str_pad("Modal", 5, " ", STR_PAD_RIGHT) . str_pad("Rp. ".number_format($data['kasir_log_cash_in'],0,',','.'), 25, " ", STR_PAD_LEFT);
            $printer -> text("$text\n");
            $text = str_pad("Tunai", 5, " ", STR_PAD_RIGHT) . str_pad("Rp. ".number_format($data['kasir_log_total_penjualan'],0,',','.'), 25, " ", STR_PAD_LEFT);
            $printer -> text("$text\n");
            $text = str_pad("Non Tunai", 5, " ", STR_PAD_RIGHT) . str_pad("Rp. ".number_format($data['kasir_log_total_penjualan_non_tunai'],0,',','.'), 21, " ", STR_PAD_LEFT);
            $printer -> text("$text\n");
            $text = str_pad("Return", 5, " ", STR_PAD_RIGHT) . str_pad("Rp. ".number_format($data['kasir_log_total_return'],0,',','.'), 24, " ", STR_PAD_LEFT);
            $printer -> text("$text\n");
            $text = str_pad("Total Uang", 5, " ", STR_PAD_RIGHT) . str_pad("Rp. ".number_format($data['kasir_log_total_penjualan'] + $data['kasir_log_total_penjualan_non_tunai'] + $data['kasir_log_cash_in'] -$data['kasir_log_total_return'],0,',','.'), 20, " ", STR_PAD_LEFT);
            $printer -> text("$text\n");
            $text = str_pad("Total Uang Tunai", 5, " ", STR_PAD_RIGHT) . str_pad("Rp. ".number_format($data['kasir_log_total_penjualan'] + $data['kasir_log_cash_in'] -$data['kasir_log_total_return'],0,',','.'), 14, " ", STR_PAD_LEFT);
            $printer -> text("$text\n");
            $printer->feed(4);
            $printer -> close();
        } catch (Exception $e) {
            echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
        }
    }

}