<?php

class P_Purchasedetail extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "p_purchasedetail";
        $this->table_key = "P_PurchaseDetailID";
    }

    function search_dd( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $vendor = isset($d['vendor_id'])? $d['vendor_id'] : 0;
        	// int(11) [0]	
        // P_PurchaseDetailA_ItemID	int(11) [0]	
        
        $r = $dbiv->query(
                "SELECT P_PurchaseDetailID detail_id, P_PurchaseDetailQty detail_qty,
                P_PurchaseDetailPrice detail_price,
                P_PurchaseDetailDisc detail_disc,
                P_PurchaseDetailSubTotal detail_subtotal,
                P_PurchaseDetailPPN detail_ppn,
                P_PurchaseDetailPPNAmount detail_ppn_amount,
                P_PurchaseDetailTotal detail_total,
                P_PurchaseDetailReceived detail_received,
                P_PurchaseDetailUnReceived detail_unreceived,
                P_PurchaseDetailDone detail_done,
                M_ItemID item_id, M_ItemName item_name, M_UnitName item_unit,
                P_PurchaseDate purchase_date, P_PurchaseNumber purchase_number,
                M_VendorName vendor_name, IFNULL(P_PurchaseMemo, '') purchase_memo
                FROM `{$this->table_name}`
                JOIN p_purchase ON P_PurchaseID = P_PurchaseDetailP_PurchaseID
                JOIN m_vendor ON P_PurchaseM_VendorID = M_VendorID
                    AND ((M_VendorID = ? AND ? <> 0) OR ? = 0)
                JOIN m_item ON P_PurchaseDetailA_ItemID = M_ItemID
                JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                WHERE ((P_PurchaseDetailP_PurchaseID = ? and ? <> 0) or ? = 0)
                AND `M_ItemName` LIKE ?
                AND `P_PurchaseDetailIsActive` = 'Y'
                AND ((P_PurchaseDetailDone !='Y'))
                AND (? = 0 OR (? <> 0 AND ? = P_PurchaseDetailP_PurchaseID))
                LIMIT {$limit} OFFSET {$offset}", [$vendor, $vendor, $vendor, $d['purchase_id'], $d['purchase_id'], $d['purchase_id'], $d['search'],
                    $d['purchase_id'], $d['purchase_id'], $d['purchase_id']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN p_purchase ON P_PurchaseID = P_PurchaseDetailP_PurchaseID
            JOIN m_vendor ON P_PurchaseM_VendorID = M_VendorID
                AND ((M_VendorID = ? AND ? <> 0) OR ? = 0)
            JOIN m_item ON P_PurchaseDetailA_ItemID = M_ItemID
            WHERE ((P_PurchaseDetailP_PurchaseID = ? and ? <> 0) or ? = 0)
                AND `M_ItemName` LIKE ?
                AND `P_PurchaseDetailIsActive` = 'Y'
                AND ((P_PurchaseDetailDone != 'Y'))
                AND (? = 0 OR (? <> 0 AND ? = P_PurchaseDetailP_PurchaseID))", [$vendor, $vendor, $vendor, $d['purchase_id'], $d['purchase_id'], $d['purchase_id'], $d['search'],
                $d['purchase_id'], $d['purchase_id'], $d['purchase_id']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_autocomplete( $d )
    {
        $d['limit'] = 200;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function save ( $d, $id = 0, $uid )
    {
        $r = $this->db->query("CALL sp_purchase_save(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
}

?>