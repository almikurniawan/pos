<?php
class MY_Controller extends CI_Controller
{
    public function __construct(){
        parent::__construct();
    }
}

class Base_Controller extends MY_Controller {
    protected $user;
    protected $kasir_log;
    public function __construct()
	{
        parent::__construct();
        if (!$this->session->userdata('user')) {
			redirect('/login');
        }
        
        $this->user         = $this->session->userdata('user');
        $this->kasir_log    = $this->session->userdata('kasir_log');
        $this->pos_master    = $this->session->userdata('pos_master');
	}
    
    public function render($view, $data= array())
    {
        $header_start['menu'] = $this->menu();
        $this->load->view('template/header_start', $header_start);
        $this->load->view($view, $data);
        $this->load->view('template/header_end');
    }

    public function render_popup($view, $data= array())
    {
        $this->load->view('template/header_popup', array(
            'view'  => $view,
            'data'  => $data
        ));
    }

    public function response_json($output= array())
    {
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($output));
    }

    private function menu()
    {
        $menu = '';
        $list_menu = $this->list_menu();
        foreach($list_menu as $key => $value){
            if(isset($value['child'])){
                $menu .= '<li><a href="#"><i class="'.$value['icon'].'"></i> '.$value['label'].' </a><ul class="submenu">';
                foreach($value['child'] as $k => $v){
                    $menu .= '<li><a href="'.base_url($v['controller']).'">'.$v['label'].' </a></li>';
                }
                $menu .= '</ul></li>';
            }else{
                $menu .= '<li><a href="'.base_url($value['controller']).'"><i class="'.$value['icon'].'"></i> '.$value['label'].' </a></li>';
            }
        }
        $menu .= '<li><a href="'.base_url("login/logout").'"><i class="k-icon k-i-logout"></i> Logout </a></li>';

        return $menu;
    }

    private function list_menu()
    {
        $user = $this->session->userdata('user');
        if($user['user_type']==2){
            $list_menu = array(
                array(
                    'label'         => 'Penjualan',
                    'controller'    => 'home',
                    'icon'          => ''
                ),
                array(
                    'label'         => 'Return',
                    'controller'    => 'returnOrder',
                    'icon'          => 'fa-home'
                ),
            );
        }else{
            $list_menu = array(
                array(
                    'label'         => 'Penjualan',
                    'controller'    => 'home',
                    'icon'          => ''
                ),
                array(
                    'label'         => 'Return',
                    'controller'    => 'returnOrder',
                    'icon'          => 'fa-home'
                ),
                array(
                    'label'         => 'Stok',
                    'controller'    => '#stok',
                    'icon'          => 'fa-home',
                    'child'         => array(
                        array(
                            'label'     => 'Mutasi Stok',
                            'controller'=> 'mutasiStok',
                        ),
                        array(
                            'label'     => 'Input Barang Masuk',
                            'controller'=> 'barangMasuk',
                        ),
                    )
                ),
                array(
                    'label'         => 'Rekapan',
                    'controller'    => '#rekapan',
                    'icon'          => 'fa-home',
                    'child'         => array(
                        array(
                            'label'     => 'Per Kasir',
                            'controller'=> 'rekapan/perkasir',
                        ),
                        array(
                            'label'     => 'Per Tanggal',
                            'controller'=> 'rekapan/pertanggal',
                        )
                    )
                ),
                array(
                    'label'         => 'Data Master',
                    'controller'    => '#data_master',
                    'icon'          => 'fa-home',
                    'child'         => array(
                        array(
                            'label'     => 'Satuan',
                            'controller'=> 'referensi/satuan',
                        ),
                        array(
                            'label'     => 'Kategori Produk',
                            'controller'=> 'referensi/produk_group',
                        ),
                        array(
                            'label'     => 'Suplier',
                            'controller'=> 'referensi/suplier',
                        ),
                        array(
                            'label'     => 'Produk',
                            'controller'=> 'referensi/produk',
                        ),
                        array(
                            'label'     => 'User',
                            'controller'=> 'referensi/user',
                        ),
                        array(
                            'label'     => 'Kasir',
                            'controller'=> 'referensi/kasir',
                        ),
                        array(
                            'label'     => 'Scanner',
                            'controller'=> 'referensi/scan',
                        ),
                    )
                ),
            );
        }

        return $list_menu;
    }
}