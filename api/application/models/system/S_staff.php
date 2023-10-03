<?php

class S_staff extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "s_staff";
        $this->table_key = "S_StaffID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $pos = isset($d['position'])?"AND S_PositionCode = '{$d['position']}'":'';

        $r = $this->db->query(
                "SELECT S_StaffID staff_id, S_StaffCode staff_code, S_StaffName staff_name,
                    IFNULL(S_PositionID, 0) position_id, IFNULL(S_PositionName, '') position_name
                FROM `{$this->table_name}`
                LEFT JOIN s_position ON S_StaffS_PositionID = S_PositionID
                WHERE (`S_StaffName` LIKE ?)
                AND `S_StaffIsActive` = 'Y'
                {$pos}
                LIMIT {$limit} OFFSET {$offset}", [$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            LEFT JOIN s_position ON S_StaffS_PositionID = S_PositionID
            WHERE (`S_StaffName` LIKE ?)
            AND `S_StaffIsActive` = 'Y'
            {$pos}", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function get( $id )
    {
        $r = $this->db->query("SELECT S_StaffID staff_id, S_StaffName staff_name
                    FROM `{$this->table_name}`
                    WHERE `{$this->table_key}` = ?", [$id])
                ->row();

        return (array) $r;
    }

    // function get ($id)
    // {
    //     $r = $dbiv->query(
    //         "SELECT S_StaffID customerstaff_id, S_StaffName customerstaff_name, IFNULL(M_CityName, '') city_name
    //         FROM `{$this->table_name}`
    //         LEFT JOIN m_city ON S_StaffM_CityID = M_CityID
    //         WHERE `S_StaffID` = ?", [$id])->row();
    //     return $r;
    // }

    function search_autocomplete( $d )
    {
        $d['limit'] = 999;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function save ( $d )
    {
        $r = $this->db->set('S_StaffName', $d['staff_name'])
                    ->set('S_StaffCode', $d['staff_code']);
        if (isset($d['staff_id']))
        {
            $this->db->where('S_StaffID', $d['staff_id'])
                ->update( $this->table_name );
            $id = $d['staff_id'];
        }
        else
        {
            $this->db->insert( $this->table_name );
            $id = $this->db->insert_id();
        }

        if ($r)
        {
            return (object) ["status"=>"OK", "data"=>$id];
        }

        return (object) ["status"=>"ERR"];
    }
}

?>