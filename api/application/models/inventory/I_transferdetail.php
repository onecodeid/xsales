<?php

/**
 * undocumented class
 */
class I_transferdetail extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "i_transferdetail";
        $this->table_key = "I_TransferDetailID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT I_TransferDetailID detail_id, M_ItemID item_id, M_ItemName item_name,
                    M_ItemCode item_code, M_UnitName unit_name,
                    I_TransferDetailQty item_qty, I_TransferDetailBeforeQty item_bf_qty,
                    I_TransferDetailAfterQty item_af_qty, I_TransferDetailToBeforeQty item_to_bf_qty,
                    I_TransferDetailToAfterQty item_to_af_qty
                FROM `{$this->table_name}`
                JOIN m_item ON I_TransferDetailM_ItemID = M_ItemID
                JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                WHERE I_TransferDetailI_TransferID = ?
                AND (`M_ItemName` LIKE ?)
                AND `I_TransferDetailIsActive` = 'Y'", [$d['transfer_id'], $d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_item ON I_TransferDetailM_ItemID = M_ItemID
                WHERE I_TransferDetailI_TransferID = ?
                AND (`M_ItemName` LIKE ?)
                AND `I_TransferDetailIsActive` = 'Y'", [$d['transfer_id'], $d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }
}

?>