<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Smart_grid_kendo
{
    protected $CI;
    protected $template     = 'template/default_smart_grid';
    protected $config       = array();
    protected $SQL          = "";
    protected $sort         = array('id', 'asc');
    protected $whereClause  = array();  
    protected $icon         = array(
        'edit'      => 'edit',
        'delete'    => 'delete',
        'detail'    => 'eye'
    );
    public $uniq_id = null;

    public function __construct()
    {
        $this->CI   =& get_instance();
    }

    public function configure($config)
    {
        $this->uniq_id = time() . uniqid();
        $this->config = array(
            'pageSize'      => 25,
            'grid_name'     => $this->uniq_id,
            'gridReload'    => "gridReload()",
            'datasouce_name'=> $this->uniq_id,
            'grid_name'     => $this->uniq_id,
            'fields'        => array(
            ),
            'datasouce_url' => '',
            'grid_height'   => 500,
            'grid_columns'  => array(
                array(
                    'field'=>'id',
                    'title'=>'ID',
                    'width'=>50
                )
            ),

            'action'        => array()
        );
        foreach ($config as $key => $value) {
            $this->config[$key] = $value;
        }

        $fields = array();
        foreach($config['grid_columns'] as $key => $value){
            if(!isset($value['format'])){
                $fields[$value['field']] = array("type"=> "string");
            }else{
                if($value['format']=='number'){
                    $this->config['grid_columns'][$key]['format']  = "{0:n0}";
                    $fields[$value['field']] = array("type"=> $value['format']);
                }
                if($value['format']=='date'){
                    $this->config['grid_columns'][$key]['template']  = "#= kendo.toString(kendo.parseDate(".$value['field'].", 'yyyy-MM-dd'), 'dd MMMM yyyy') #";
                    $fields[$value['field']] = array("type"=> "string");
                }
                if($value['format']=='datetime'){
                    $this->config['grid_columns'][$key]['template']  = "#= kendo.toString(kendo.parseDate(".$value['field'].", 'yyyy-MM-dd HH:mm:ss'), 'dd MMMM yyyy HH:mm:ss') #";
                    $fields[$value['field']] = array("type"=> "string");
                }
            }
        }
        $this->config['fields'] = $fields;
        return $this;
    }

    public function set_sort($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    public function set_query($SQL, $whereClause = array())
    {
        $this->SQL = $SQL;
        $this->whereClause = $whereClause;
        return $this;
    }

    public function output()
    {
        if(isset($_GET['datasource'])){
            $this->get_datasource();
        }
        $columns = array();

        if(!empty($this->config['action'])){
            foreach($this->config['action'] as $key => $value){
                array_push($columns, array(
                    'field' => $key,
                    'title' => '&nbsp',
                    'encoded'=> false,
                    'width' => 40
                ));
            }
        }

        foreach ($this->config['grid_columns'] as $key => $value) {
            if(isset($value['align'])){
                $value['attributes'] = array('style'=>'text-align:'.$value['align']);
            }
            array_push($columns, $value);
        }

        $this->config['grid_columns'] = $columns;
        return $this->CI->load->view($this->template, $this->config, true);
    }

    private function get_datasource()
    {
        $this->CI->load->library('pagination_kendo');
        $this->CI->pagination_kendo->set_query($this->SQL, $this->whereClause);
        $this->CI->pagination_kendo->set_sort($this->sort);
        $data = $this->CI->pagination_kendo->run();

        if(!empty($this->config['action'])){
            foreach($this->config['action'] as $key => $value){
                foreach($data['result'] as $k => $v){
                    if(isset($value['link']) && $value['link']!=''){
                        $data['result'][$k][$key] = '<a href="'.base_url($value['link'] . $v['id']).'"><i class="k-icon k-i-'.$this->icon[$key].'"></i></a>';
                    }
                    if(isset($value['jsf']) && $value['jsf']!=''){
                        $data['result'][$k][$key] = '<a href="javascript:void(0)" onclick="'.$value['jsf'].'(\''.$v['id'].'\')"><i class="k-icon k-i-'.$this->icon[$key].'"></i></a>';
                    }
                }
            }
        }

        die(json_encode($data));
    }

}