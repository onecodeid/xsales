<?php

class S_position extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "s_position";
        $this->table_key = "S_PositionID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT S_PositionID position_id, S_PositionCode position_code, S_PositionName position_name
                FROM `{$this->table_name}`
                WHERE (`S_PositionName` LIKE ? OR `S_PositionCode` LIKE ?)
                AND `S_PositionIsActive` = 'Y'
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE (`S_PositionName` LIKE ? OR `S_PositionCode` LIKE ?)
            AND `S_PositionIsActive` = 'Y'", [$d['search'], $d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function get( $id )
    {
        $r = $this->db->query("SELECT S_PositionID position_id, S_PositionCode position_code, S_PositionName position_name
                    FROM `{$this->table_name}`
                    WHERE `{$this->table_key}` = ?", [$id])
                ->row();

        return (array) $r;
    }
}

?>