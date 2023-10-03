<?php

class A_customer extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "ms_pelanggan";
        $this->table_key = "pelanggan_id";
    }

    function search( $d )
    {
        $dbiv = $this->load->database('dbiv', true);
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $dbiv->query(
                "SELECT pelanggan_id customer_id, pelanggan_namalengkap customer_name, '' city_name
                FROM `{$this->table_name}`
                
                WHERE `pelanggan_namalengkap` LIKE ?
                AND `pelanggan_deleted` = 0
                LIMIT {$limit} OFFSET {$offset}", [$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `pelanggan_namalengkap` LIKE ?
            AND `pelanggan_deleted` = 0", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function get ($id)
    {
        $r = $dbiv->query(
            "SELECT A_CustomerID customer_id, A_CustomerName customer_name, IFNULL(M_CityName, '') city_name
            FROM `{$this->table_name}`
            LEFT JOIN m_city ON A_CustomerM_CityID = M_CityID
            WHERE `A_CustomerID` = ?", [$id])->row();
        return $r;
    }

    function search_autocomplete( $d )
    {
        $d['limit'] = 25;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }
}

?>