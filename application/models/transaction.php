<?php
class Transaction extends Base_Model{
    public function __construct()
    {
        parent::__construct();
    }

    private function insert_pos_master()
    {
        $data_insert_pos_master = array(
            'pos_kasir_id'      => $this->kasir_log['kasir_log_id'],
            'pos_created_at'    => date('Y-m-d H:i:s'),
            'pos_created_by'    => $this->user['user_id'],
            'pos_total'         => 0
        );
        $this->db->insert("pos_master", $data_insert_pos_master);
        return $this->db->insert_id();
    }

    private function insert_pos_detail($produk_id, $jumlah, $pos_master_id)
    {
        $cek_data = $this->db->get_where("pos_detail", array(
            'pos_det_master_id' => $pos_master_id,
            'pos_det_produk_id' => $produk_id,
        ))->row_array();

        $data_produk = $this->db->get_where("ref_produk", array('ref_produk_id'=>$produk_id))->row_array();
        if(empty($cek_data)){
            $data_insert_pos_detail = array(
                'pos_det_master_id' => $pos_master_id,
                'pos_det_produk_id' => $produk_id,
                'pos_det_produk_harga_beli' => $data_produk['ref_produk_harga_beli'],
                'pos_det_produk_harga_jual' => $data_produk['ref_produk_harga_jual'],
                'pos_det_jumlah_item'       => $jumlah,
                'pos_det_sub_total'         => ((int)$jumlah * (float)$data_produk['ref_produk_harga_jual'])
            );
            $this->db->insert("pos_detail", $data_insert_pos_detail);
            // $this->update_stok($produk_id, $jumlah);
            $pos_detail_id = $this->db->insert_id();
        }else{
            $jumlah     = (int)$jumlah + (int)$cek_data['pos_det_jumlah_item'];
            $sub_total  = ($jumlah * $cek_data['pos_det_produk_harga_jual']);
            if($jumlah<=0){
                $this->db->delete("pos_detail", 'pos_det_id='.$cek_data['pos_det_id']);
            }else{
                $this->db->update("pos_detail", array(
                    'pos_det_jumlah_item'   => $jumlah,
                    'pos_det_sub_total'     => $sub_total
                ),'pos_det_id='.$cek_data['pos_det_id']);
            }
            // $this->update_stok($produk_id, $jumlah);
            $pos_detail_id = $cek_data['pos_det_id'];
        }

        $sum_detail = $this->db->query("select sum(pos_det_sub_total) as total, count(*) as jumlah from pos_detail where pos_det_master_id=".$pos_master_id)->row_array();

        $this->db->update("pos_master", array(
            'pos_total' => $sum_detail['total'],
            'pos_jumlah_item' => $sum_detail['jumlah']
        ),"pos_id=".$pos_master_id);

        if($jumlah>$data_produk['ref_produk_stok']){
            $result = array(
                'status'    => false,
                'flag'      => 'warning',
                'message'   => 'Stok tidak mencukupi tetapi transaksi tetap bisa berlanjut',
                'pos_det_id'=> $pos_detail_id,
                'pos_total' => $sum_detail['total']
            );
        }else{
            $result = array(
                'status'    => true,
                'flag'      => 'success',
                'message'   => 'Sukses menambah item',
                'pos_det_id'=> $pos_detail_id,
                'pos_total' => $sum_detail['total']
            );
        }

        return $result;
    }

    public function update_stok($produk_id, $jumlah, $operator = '-')
    {
        $this->db->query("update ref_produk set ref_produk_stok = ref_produk_stok ".$operator." ".$jumlah." where ref_produk_id = ".$produk_id);
        return "update ref_produk set ref_produk_stok = ref_produk_stok ".$operator." ".$jumlah." where ref_produk_id = ".$produk_id;
    }

    public function sale($produk_id, $jumlah, $pos_master_id = null)
    {        
        if($pos_master_id==null){
            $pos_master_id = $this->insert_pos_master();
            $this->session->set_userdata('pos_master', array(
                'pos_id' => $pos_master_id
            ));
            $pos_detail = $this->insert_pos_detail($produk_id, $jumlah, $pos_master_id);
        }else{
            $pos_detail = $this->insert_pos_detail($produk_id, $jumlah, $pos_master_id);
        }

        return $pos_detail;
    }

    public function pay($pos_id, $nominal, $methode)
    {
        $data_pos = $this->db->get_where("pos_master", array('pos_id'=>$pos_id))->row_array();
        $update_pos_master = $this->db->update("pos_master", array(
            'pos_bayar_is'=> 1,
            'pos_bayar_at'=> date("Y-m-d H"),
            'pos_bayar_by'=> $this->user['user_id']
        ),'pos_id='.$pos_id);

        if($nominal<$data_pos['pos_total']){
            return array(
                'status'    => false,
                'message'   => 'Nominal Kurang Dari Total Pembelian!'
            );
        }
        $kembalian = $nominal - $data_pos['pos_total'];
        $data_insert_payment = array(
            'payment_pos_id'    => $pos_id,
            'payment_total'     => $data_pos['pos_total'],
            'payment_bayar'     => $nominal,
            'payment_kembalian' => $kembalian,
            'payment_methode'   => $methode,
            'payment_create_at' => date("Y-m-d H"),
            'payment_create_by' => $this->user['user_id']
        );
        $this->db->insert("payment", $data_insert_payment);
        $payment_id = $this->db->insert_id();

        $this->mutasi($pos_id, 'penjualan');

        if($methode==1){
            $this->db->query("update kasir_log set kasir_log_total_penjualan=coalesce(kasir_log_total_penjualan,0)+".$data_pos['pos_total']." where kasir_log_id=".$this->kasir_log['kasir_log_id']);
        }else{
            $this->db->query("update kasir_log set kasir_log_total_penjualan_non_tunai=coalesce(kasir_log_total_penjualan_non_tunai,0)+".$data_pos['pos_total']." where kasir_log_id=".$this->kasir_log['kasir_log_id']);
        }

        $message = 'Sukses pembayaran';
        if($kembalian>0){
            $message .= ', Kembalian  Rp. '.number_format($kembalian,0,',','.');
        }
        return array(
            'status'    => true,
            'message'   => $message,
            'payment_id'    => $payment_id
        );
    }

    public function mutasi($id, $operation)
    {
        if($operation=='penjualan'){
            $pos_detail = $this->db->query("SELECT
                                                pos_det_id,
                                                ref_produk_id,
                                                pos_det_jumlah_item as jumlah,
                                                ref_produk_stok AS jumlah_saat_ini,
                                                ( ref_produk_stok -  pos_det_jumlah_item) AS jumlah_updated 
                                            FROM
                                                pos_detail
                                                left join ref_produk on ref_produk_id = pos_det_produk_id
                                                where pos_det_master_id=".$id)->result_array();
            foreach ($pos_detail as $key => $value) {
                $data_insert[] = array(
                    'mutasi_produk_id'	=> $value['ref_produk_id'],
                    'mutasi_jumlah'		=> $value['jumlah'],
                    'mutasi_jumlah_sebelumnya'=> $value['jumlah_saat_ini'],
                    'mutasi_produk_balance'	=> $value['jumlah_updated'],
                    'mutasi_jenis_mutasi'	=> 1,
                    'mutasi_operator'		=> '-',
                    'mutasi_created_at'		=> date("Y-m-d H:i:s"),
                    'mutasi_created_by'		=> $this->user['user_id'],
                    'mutasi_id_pos_detail'  => $value['pos_det_id']
                );
    
                $this->db->update("ref_produk", array(
                    'ref_produk_stok'	=> $value['jumlah_updated']
                ),'ref_produk_id='.$value['ref_produk_id']);
            }
            $this->db->insert_batch('mutasi_stok', $data_insert);
        }else if($operation=='return'){
            $pos_detail = $this->db->query("SELECT
                                                return_det_produk_id,
                                                ref_produk_id,
                                                return_det_jumlah AS jumlah,
                                                ref_produk_stok AS jumlah_saat_ini,
                                                ( ref_produk_stok + return_det_jumlah ) AS jumlah_updated 
                                            FROM
                                                return_detail
                                                LEFT JOIN ref_produk ON ref_produk_id = return_det_produk_id
                                                where return_det_master_id=".$id)->result_array();
            foreach ($pos_detail as $key => $value) {
                $data_insert[] = array(
                    'mutasi_produk_id'	=> $value['ref_produk_id'],
                    'mutasi_jumlah'		=> $value['jumlah'],
                    'mutasi_jumlah_sebelumnya'=> $value['jumlah_saat_ini'],
                    'mutasi_produk_balance'	=> $value['jumlah_updated'],
                    'mutasi_jenis_mutasi'	=> 3,
                    'mutasi_operator'		=> '+',
                    'mutasi_created_at'		=> date("Y-m-d H:i:s"),
                    'mutasi_created_by'		=> $this->user['user_id'],
                    'mutasi_id_return_detail'  => $value['return_det_produk_id']
                );

                $this->db->update("ref_produk", array(
                    'ref_produk_stok'	=> $value['jumlah_updated']
                ),'ref_produk_id='.$value['ref_produk_id']);
            }
            $this->db->insert_batch('mutasi_stok', $data_insert);
        }

        return true;
    }
}
