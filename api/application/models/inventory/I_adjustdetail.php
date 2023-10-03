<?php

/**
 * undocumented class
 */
class I_adjustdetail extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "i_adjustdetail";
        $this->table_key = "I_AdjustDetailID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT I_AdjustDetailID detail_id, M_ItemID item_id, M_ItemName item_name,
                    M_ItemCode item_code,
                    I_AdjustDetailQty item_qty, I_AdjustDetailBeforeQty item_bf_qty,
                    I_AdjustDetailAfterQty item_af_qty
                FROM `{$this->table_name}`
                JOIN m_item ON I_AdjustDetailM_ItemID = M_ItemID
                WHERE I_AdjustDetailI_AdjustID = ?
                AND (`M_ItemName` LIKE ?)
                AND `I_AdjustDetailIsActive` = 'Y'", [$d['adjust_id'], $d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_item ON I_AdjustDetailM_ItemID = M_ItemID
                WHERE I_AdjustDetailI_AdjustID = ?
                AND (`M_ItemName` LIKE ?)
                AND `I_AdjustDetailIsActive` = 'Y'", [$d['adjust_id'], $d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }
}

?>