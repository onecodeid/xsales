<?php

class M_customerdisc extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_customerdisc";
        $this->table_key = "M_CustomerDiscID";
    }

    function get( $custid, $discid )
    {
        $r = $this->db->selecT('M_CustomerDiscID id, M_CustomerDiscAmount amount', false)
                ->where('M_CustomerDiscM_CustomerID', $custid)
                ->where('M_CustomerDiscM_DiscID', $discid)
                ->where('M_CustomerDiscIsActive', 'Y')
                ->get($this->table_name)
                ->row();
        if ($r)
        {
            return (array)$r;
        }

        return null;
    }
}

?>