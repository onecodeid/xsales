<?php

class M_prospect extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_prospect";
        $this->table_key = "M_ProspectID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT M_ProspectID prospect_id, M_ProspectCode prospect_code, M_ProspectName prospect_name
                FROM `{$this->table_name}`
                WHERE `M_ProspectName` LIKE ?
                AND `M_ProspectIsActive` = 'Y'
                ORDER BY M_ProspectCode", [$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_ProspectName` LIKE ?
            AND `M_ProspectIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_ProspectName', $d['prospect_name'])
                    ->set('M_ProspectCode', $d['prospect_code']);
                    // ->set('M_ProspectUserID', $d['user_id']);
        if (isset($d['prospect_id']))
        {
            $this->db->where('M_ProspectID', $d['prospect_id'])
                ->update( $this->table_name );
            $id = $d['prospect_id'];
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
        $this->db->set('M_ProspectIsActive', 'N')
                ->where('M_ProspectID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>