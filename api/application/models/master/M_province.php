<?php

class M_province extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_province";
        $this->table_key = "M_ProvinceID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT *
                FROM `{$this->table_name}`
                WHERE `M_ProvinceName` LIKE ?
                AND `M_ProvinceIsActive` = 'Y'", [$d['province_name']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_ProvinceName` LIKE ?
            AND `M_ProvinceIsActive` = 'Y'", [$d['province_name']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }
}

?>