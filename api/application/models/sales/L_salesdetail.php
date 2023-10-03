<?php

class L_Salesdetail extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "l_salesdetail";
        $this->table_key = "L_SalesDetailID";
    }

    function search_dd( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $vendor = isset($d['customer_id'])? $d['customer_id'] : 0;
        	// int(11) [0]	
        // L_SalesDetailA_ItemID	int(11) [0]	
        
        $r = $dbiv->query(
                "SELECT L_SalesDetailID detail_id, L_SalesDetailQty detail_qty,
                L_SalesDetailPrice detail_price,
                L_SalesDetailDisc detail_disc,
                L_SalesDetailSubTotal detail_subtotal,
                L_SalesDetailPPN detail_ppn,
                L_SalesDetailPPNAmount detail_ppn_amount,
                L_SalesDetailTotal detail_total,
                L_SalesDetailSent detail_sent,
                L_SalesDetailUnSent detail_unsent,
                L_SalesDetailDone detail_done,
                M_ItemID item_id, M_ItemCode item_code, M_ItemName item_name, M_UnitName item_unit, 0 stock,
                L_SalesDate sales_date, L_SalesNumber sales_number,
                L_SalesM_DeliveryAddressID address_id,
                M_CustomerName customer_name, IFNULL(L_SalesMemo, '') sales_memo
                FROM `{$this->table_name}`
                JOIN l_sales ON L_SalesID = L_SalesDetailL_SalesID AND L_SalesDate >= CONCAT(?, '-01-01')
                JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID
                    AND ((M_CustomerID = ? AND ? <> 0) OR ? = 0)
                JOIN m_item ON L_SalesDetailA_ItemID = M_ItemID
                JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                WHERE ((L_SalesDetailL_SalesID = ? and ? <> 0) or ? = 0)
                AND `M_ItemName` LIKE ?
                AND `L_SalesDetailIsActive` = 'Y'
                AND ((L_SalesDetailDone !='Y'))
                LIMIT {$limit} OFFSET {$offset}", [$this->balance_year, $vendor, $vendor, $vendor, $d['sales_id'], $d['sales_id'], $d['sales_id'], $d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN l_sales ON L_SalesID = L_SalesDetailL_SalesID AND L_SalesDate >= CONCAT(?, '-01-01')
            JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID
                AND ((M_CustomerID = ? AND ? <> 0) OR ? = 0)
            JOIN m_item ON L_SalesDetailA_ItemID = M_ItemID
            WHERE ((L_SalesDetailL_SalesID = ? and ? <> 0) or ? = 0)
                AND `M_ItemName` LIKE ?
                AND `L_SalesDetailIsActive` = 'Y'
                AND ((L_SalesDetailDone != 'Y'))", [$this->balance_year, $vendor, $vendor, $vendor, $d['sales_id'], $d['sales_id'], $d['sales_id'], $d['search']]);
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
        $r = $this->db->query("CALL sl_sales_save(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function save_item_name($id, $name)
    {
        $r = $this->db->set("L_SalesDetailA_ItemName", $name)
                ->where($this->table_key, $id)
                ->update($this->table_name);

        return ["item_id"=>$id, "item_name"=>$name];
    }
}

?>