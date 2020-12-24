<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Pagination_kendo
{
    protected $SQL          = "";
    protected $sort         = array('id', 'asc');
    protected $whereClause  = array();
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function set_sort($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    public function set_query($SQL, $whereClause=array())
    {
        $this->whereClause = $whereClause;

        //where clause
        $whereClause = array();
        foreach ($this->whereClause as $value) {
            if($value[1]!=''){
                if(!isset($value[2])){
                    array_push($whereClause, $value[0]." like '%".$value[1]."%'");
                }else{
                    array_push($whereClause, $value[0]." ".$value[2]." '".$value[1]."'");
                }
            }
        }
        if(!empty($whereClause)){
            $where = " where ".implode(" and ", $whereClause);
            $this->SQL = $SQL . $where;
        }else{
            $this->SQL = $SQL;
        }
        return $this;
    }

    public function get_query()
    {
        return $this->SQL;
    }

    public function run()
    {
        $this->CI->load->database();

        $page = (int)$_GET['page'];
		$pageSize = (int)$_GET['pageSize'];

        //limit
		$limit  = $pageSize;
        $offset = ($page - 1) * $pageSize;

        //sort
        $sort   = array(implode(" ", $this->sort));
        if(isset($_GET['sort'])){
            $sort = array();
            foreach ($_GET['sort'] as $key => $value) {
                array_push($sort, $value['field']." ".$value['dir']);
            }
        }
        $order_by = " order by ".implode(", ",$sort);
        
        $SQL    = $this->SQL . $order_by . " limit ".$limit." offset ".$offset;

        $query  = $this->CI->db->query($SQL);
        $data   = $query->result_array();

        $query = $this->CI->db->query("select count(*) as total from (".$this->SQL.") tmp");
        $total = $query->row_array();

        $this->SQL = $SQL;
        
        return array(
            'total'     => (int)$total['total'],
            'result'    => $data,
            'sql'       => $this->SQL
        );
    }
    
}
