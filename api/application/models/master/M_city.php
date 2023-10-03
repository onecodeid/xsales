<?php

class M_city extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_city";
        $this->table_key = "M_CityID";
    }

    function search_full( $d )
    {
        $l = ['records'=>[], 'total'=>0];
        $d['limit'] = 25;

        $r = $this->db->query(
                "SELECT *
                FROM `v_cities`
                WHERE full_address LIKE ?
                LIMIT ?", [$d['search'], $d['limit']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(*) n
                FROM `v_cities`
                WHERE full_address LIKE ?", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function search_full_kecamatan_reverse( $d )
    {
        $l = ['records'=>[], 'total'=>0];
        $d['limit'] = 25;

        $r = $this->db->query(
                "SELECT *
                FROM `v_cities`
                WHERE full_address_kecamatan_reverse LIKE ?
                GROUP BY full_address_kecamatan_reverse
                LIMIT ?", [$d['search'], $d['limit']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(DISTINCT kecamatan_id) n
                FROM `v_cities`
                WHERE full_address_kecamatan_reverse LIKE ?", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function search_full_reverse( $d )
    {
        $l = ['records'=>[], 'total'=>0];
        $d['limit'] = 25;

        $r = $this->db->query(
                "SELECT *
                FROM `v_cities`
                WHERE full_address_reverse LIKE ?
                LIMIT ?", [$d['search'], $d['limit']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(DISTINCT kelurahan_id) n
                FROM `v_cities`
                WHERE full_address_reverse LIKE ?", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT *
                FROM `{$this->table_name}`
                WHERE `M_CityName` LIKE ? ANd M_CityM_ProvinceID = ?
                AND `M_CityIsActive` = 'Y'", [$d['city_name'], $d['province_id']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_CityName` LIKE ? ANd M_CityM_ProvinceID = ?
            AND `M_CityIsActive` = 'Y'", [$d['city_name'], $d['province_id']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function get_from_kelurahan($id)
    {
        $r = $this->db->select('m_city.*')
                    ->join('m_district','M_KelurahanM_DistrictID=M_DistrictID')
                    ->join('m_city','M_DistrictM_CityID=M_CityID')
                    ->where('M_KelurahanID', $id)
                    ->get('m_kelurahan')
                    ->row();
        return $r;
    }
}

?>