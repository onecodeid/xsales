<?php

class A_customerregion extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "a_customerregion";
        $this->table_key = "A_CustomerRegionID";
    }

    function search( $d )
    {
        $dbiv = $this->load->database('dbiv', true);
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $dbiv->query(
                "SELECT A_CustomerRegionID region_id, A_CustomerRegionCode region_code, A_CustomerRegionName region_name
                FROM `{$this->table_name}`
                WHERE (`A_CustomerRegionName` LIKE ? OR `A_CustomerRegionCode` LIKE ?)
                AND `A_CustomerRegionMetaActive` = 'Y'
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE (`A_CustomerRegionName` LIKE ? OR `A_CustomerRegionCode` LIKE ?)
            AND `A_CustomerRegionMetaActive` = 'Y'", [$d['search'], $d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    // function get ($id)
    // {
    //     $r = $dbiv->query(
    //         "SELECT A_CustomerRegionID customerregion_id, A_CustomerRegionName customerregion_name, IFNULL(M_CityName, '') city_name
    //         FROM `{$this->table_name}`
    //         LEFT JOIN m_city ON A_CustomerRegionM_CityID = M_CityID
    //         WHERE `A_CustomerRegionID` = ?", [$id])->row();
    //     return $r;
    // }

    function search_autocomplete( $d )
    {
        $d['limit'] = 999;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }
}

?>