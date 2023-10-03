<?php

/**
 * undocumented class
 */
class I_opnamedetail extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "i_opnamedetail";
        $this->table_key = "I_OpnameDetailID";
    }

    function search( $d )
    {
        $limit = 99;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT m_item.*, IFNULL(I_OpnameDetailQtyBefore, 0) stock_qty, IFNULL(I_OpnameDetailQtyCurrent, 0) curr_qty, I_OpnameDetailQtyAdjust adjust_qty, M_UnitName, I_OpnameDetailNote adjust_note,
                    IFNULL(M_CategoryName, '') category_name
                FROM `{$this->table_name}`
                JOIN m_item ON I_OpnameDetailM_ItemID = M_ItemID
                JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                LEFT JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
                LEFT JOIN i_stock ON I_StockM_ItemID = M_ItemID
                WHERE `M_ItemName` LIKE ?
                AND `I_OpnameDetailIsActive` = 'Y'
                AND I_OpnameDetailI_OpnameID = ?
                LIMIT {$limit} OFFSET {$offset}", [$d['item_name'], $d['opname_id']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_item ON I_OpnameDetailM_ItemID = M_ItemID
            WHERE `M_ItemName` LIKE ?
            AND `M_ItemIsActive` = 'Y'
            AND I_OpnameDetailI_OpnameID = ?", [$d['item_name'], $d['opname_id']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_item( $d )
    {
        $limit = 99;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT m_item.*, IFNULL(I_StockQty, 0) stock_qty, IFNULL(I_StockQty, 0) curr_qty, 0 adjust_qty, M_UnitName, M_ItemImgTmbUri img_uri, '' adjust_note,
                    IFNULL(M_CategoryName, '') category_name
                FROM `m_item`
                JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                LEFT JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
                LEFT JOIN i_stock ON I_StockM_ItemID = M_ItemID
                LEFT JOIN m_itemimg ON M_ItemImgM_ItemID = M_ItemID AND M_ItemImgIsActive = 'Y'
                WHERE `M_ItemName` LIKE ?
                AND `M_ItemIsActive` = 'Y'
                LIMIT {$limit} OFFSET {$offset}", [$d['item_name']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`M_ItemID`) n
            FROM `m_item`
            WHERE `M_ItemName` LIKE ?
            AND `M_ItemIsActive` = 'Y'", [$d['item_name']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }
}

?>