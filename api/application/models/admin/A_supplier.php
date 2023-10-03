<?php

class A_supplier extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_vendor";
        $this->table_key = "M_VendorID";
    }

    function search( $d )
    {
        $dbiv = $this->load->database('dberp', true);
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
            "SELECT M_VendorID supplier_id, M_VendorName supplier_name, '' city_name
            FROM `m_vendor`
            WHERE `M_VendorName` LIKE ?
            LIMIT {$limit} OFFSET {$offset}", [$d['search']]);

        // $r = $dbiv->query(
        //         "SELECT pemasok_id supplier_id, pemasok_namalengkap supplier_name, IFNULL(One_CityName, '') city_name
        //         FROM `{$this->table_name}`
        //         LEFT JOIN one_city on pemasok_kabupaten = One_CityID
        //         WHERE `pemasok_namalengkap` LIKE ?
        //         LIMIT {$limit} OFFSET {$offset}", [$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`M_VendorID`) n
            FROM `m_vendor`
            WHERE `M_VendorName` LIKE ?", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function get($id)
    {
        $r = $this->db->query(
            "SELECT M_VendorID supplier_id, M_VendorName supplier_name, '' city_name
            FROM `{$this->table_name}`
            
            WHERE `M_VendorID` = ?
            AND `M_VendorIsActive` = 'Y'", [$id])
            ->row();

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