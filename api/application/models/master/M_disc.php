<?php

class M_Disc extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_disc";
        $this->table_key = "M_DiscID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT M_DiscID disc_id, M_DiscCode disc_code, M_DiscName disc_name, M_DiscAmount disc_amount
                FROM `{$this->table_name}`
                WHERE `M_DiscName` LIKE ?
                AND `M_DiscIsActive` = 'Y'
                ORDER BY M_DiscID", [$d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_DiscName` LIKE ?
            AND `M_DiscIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_DiscName', $d['disc_name'])
                    ->set('M_DiscCode', $d['disc_code'])
                    ->set('M_DiscAmount', $d['disc_amount']);
                    // ->set('M_DiscUserID', $d['user_id']);
        if (isset($d['disc_id']))
        {
            $this->db->where('M_DiscID', $d['disc_id'])
                ->update( $this->table_name );
            $id = $d['disc_id'];
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
        $this->db->set('M_DiscIsActive', 'N')
                ->where('M_DiscID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>