<?php

class M_pack extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_pack";
        $this->table_key = "M_PackID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT M_PackID pack_id, M_PackName pack_name, M_PackCode pack_code, 
                M_PackConversion pack_conversion, M_PackM_UnitID pack_unit, IFNULL(M_UnitName, '') unit_name
                FROM `{$this->table_name}`
                LEFT JOIN m_unit ON M_PackM_unitID = M_UnitID
                WHERE `M_PackName` LIKE ?
                AND `M_PackIsActive` = 'Y'
                ORDER BY M_PackName", [$d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_PackName` LIKE ?
            AND `M_PackIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_PackName', $d['pack_name'])
                    ->set('M_PackCode', $d['pack_code'])
                    ->set('M_PackConversion', $d['pack_conversion'])
                    ->set('M_PackM_UnitID', $d['pack_unit']);
                    // ->set('M_PackUserID', $d['user_id']);
        if (isset($d['pack_id']))
        {
            $this->db->where('M_PackID', $d['pack_id'])
                ->update( $this->table_name );
            $id = $d['pack_id'];
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

    function del ($id)
    {
        $this->db->set('M_PackIsActive', 'N')
                ->where('M_PackID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>