<?php

class M_unit extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_unit";
        $this->table_key = "M_UnitID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT M_UnitID unit_id, M_UnitName unit_name, M_UnitCode unit_code
                FROM `{$this->table_name}`
                WHERE `M_UnitName` LIKE ?
                AND `M_UnitIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_UnitName` LIKE ?
            AND `M_UnitIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_UnitName', $d['unit_name'])
                    ->set('M_UnitCode', $d['unit_code']);
                    // ->set('M_UnitUserID', $d['user_id']);
        if (isset($d['unit_id']))
        {
            $this->db->where('M_UnitID', $d['unit_id'])
                ->update( $this->table_name );
            $id = $d['unit_id'];
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
        $this->db->set('M_UnitIsActive', 'N')
                ->where('M_UnitID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>