<?php

/**
 * undocumented class
 */
class I_assemblydetail extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "i_assemblydetail";
        $this->table_key = "I_AssemblyDetailID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT I_AssemblyDetailID detail_id, M_ItemID item_id, M_ItemName item_name,
                    M_ItemCode item_code,
                    I_AssemblyDetailQty item_qty
                FROM `{$this->table_name}`
                JOIN m_item ON I_AssemblyDetailM_ItemID = M_ItemID
                WHERE I_AssemblyDetailI_AssemblyID = ?
                AND (`M_ItemName` LIKE ?)
                AND `I_AssemblyDetailIsActive` = 'Y'", [$d['assembly_id'], $d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_item ON I_AssemblyDetailM_ItemID = M_ItemID
                WHERE I_AssemblyDetailI_AssemblyID = ?
                AND (`M_ItemName` LIKE ?)
                AND `I_AssemblyDetailIsActive` = 'Y'", [$d['assembly_id'], $d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }
}

?>