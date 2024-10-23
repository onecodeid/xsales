<?php

/**
 * undocumented class
 */
class I_stock extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "i_stock";
        $this->table_key = "I_StockID";
    }

    function search_item_w_stock( $d )
    {
        $l = ['records'=>[], 'total'=>0];
        $av = isset($d['available'])?" AND f.I_StockQty > 0 AND f.I_StockQty IS NOT NULL" : "";
        $to = isset($d['to'])?$d['to']:-1;

        $r = $this->db->query(
                "SELECT m_item.*, f.*, ifnull(t.I_StockQty, 0) to_qty,
                    M_ItemName item_name, M_ItemCode item_code, M_ItemID item_id, M_UnitID unit_id, M_UnitName unit_name,
                    M_ItemDefaultHPP item_hpp
                FROM m_item
                JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                LEFT JOIN `{$this->table_name}` f ON M_ItemID = f.I_StockM_ItemID AND f.I_StockIsActive = 'Y'
                    AND ((f.I_StockM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                LEFT JOIN `{$this->table_name}` t ON M_ItemID = t.I_StockM_ItemID AND t.I_StockIsActive = 'Y'
                    AND t.I_StockM_WarehouseID = ?
                WHERE (`M_ItemName` LIKE ?)
                AND `M_ItemIsActive` = 'Y'
                {$av}
                ", [$d['warehouse'], $d['warehouse'], $d['warehouse'], $to, $d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`M_ItemID`) n
                FROM m_item
                LEFT JOIN `{$this->table_name}` f ON M_ItemID = I_StockM_ItemID AND I_StockIsActive = 'Y'
                    AND ((I_StockM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                WHERE (`M_ItemName` LIKE ?)
                AND `M_ItemIsActive` = 'Y'
                {$av}
                ", [$d['warehouse'], $d['warehouse'], $d['warehouse'], $d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function search_by_item( $id )
    {
        $r = $this->db->query("SELECT M_WarehouseID warehouse_id, M_WarehouseName warehouse_name,
                    I_StockQty stock_qty, IFNULL(M_ITemSlowValue, 'N') item_slow
                FROM i_stock
                JOIN m_warehouse ON I_StockM_WarehouseID = M_WarehouseID
                LEFT JOIN m_itemslow ON M_ITemSlowIsActive = 'Y' AND M_ITemSlowM_WarehouseID = M_WarehouseID AND M_ItemSlowM_ItemID = I_StockM_ItemID
                WHERe I_StockIsActive = 'Y'
                AND I_StockM_ItemID = ?", [$id])
                ->result_array();
        return $r;
    }

    function search_by_items( $ids, $wid )
    {
        $r = $this->db->query("SELECT M_WarehouseID warehouse_id, M_WarehouseName warehouse_name,
                    I_StockQty stock_qty, I_StockM_ItemID item_id
                FROM i_stock
                JOIN m_warehouse ON I_StockM_WarehouseID = M_WarehouseID
                WHERe I_StockIsActive = 'Y'
                AND I_StockM_WarehouseID = ?
                AND FIND_IN_SET(I_StockM_ItemID, ?)", [$wid, $ids])
                ->result_array();
        return $r;
    }

    function histories( $id )
    {
        $l = ['records'=>[], 'total'=>0];
        $trans = ["SALES.DELIVERY", "PURCHASE.RECEIVE", "INV.ADJUSTMENT"];
        $rst = [];
        $limit = 20;

        $db = $this->load->database("dblog", true);
        foreach ($trans as $k => $v)
        {
            $rst[$v] = [];
            $a_query = ["''", "''", ''];

            if ($v == 'SALES.DELIVERY')
                $a_query = [
                    "L_SalesNumber", "L_SalesDate",
                    "LEFT JOIN {$this->db->database}.l_deliverydetail ON Log_StockRefID = L_DeliveryDetailID
                    LEFT JOIN {$this->db->database}.l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
                    LEFT JOIN {$this->db->database}.l_sales ON L_SalesDetailL_SalesID = L_SalesID"
                ];

            if ($v == 'PURCHASE.RECEIVE')
                // $a_query = [
                //     "P_PurchaseNumber", "P_PurchaseDate",
                //     "LEFT JOIN {$this->db->database}.p_receivedetail ON Log_StockRefID = P_ReceiveDetailID
                //     LEFT JOIN {$this->db->database}.p_purchasedetail ON P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
                //     LEFT JOIN {$this->db->database}.p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID"
                // ];
                $a_query = [
                    "P_PurchaseNumber", "P_PurchaseDate",
                    "LEFT JOIN {$this->db->database}.p_purchasedetail ON Log_StockRefID = P_PurchaseDetailID
                    LEFT JOIN {$this->db->database}.p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID"
                ];

            if ($v == 'INV.ADJUSTMENT')
                $a_query = [
                    "I_AdjustNumber", "I_AdjustDate",
                    "LEFT JOIN {$this->db->database}.i_adjustdetail ON Log_StockRefID = I_AdjustDetailID
                    LEFT JOIN {$this->db->database}.i_adjust ON I_AdjustDetailI_AdjustID = I_AdjustID"
                ];
            if ($v == 'INV.TRANSFER')
                $a_query = [
                    "I_TransferNumber", "I_TransferDate",
                    "LEFT JOIN {$this->db->database}.i_transferdetail ON Log_StockRefID = I_TransferDetailID
                    LEFT JOIN {$this->db->database}.i_transfer ON I_TransferDetailI_TransferID = I_TransferID"
                ];

            $query = "SELECT Log_StockID log_id, Log_StockCode log_code, date(Log_StockDate) log_date,
                Log_StockRefNumber log_ref_number, Log_StockQty log_qty,
                IFNULL(M_CustomerName, '') customer_name,
                IFNULL(M_VendorName, '') vendor_name,
                M_WarehouseShortName warehouse_name, IFNULL(M_UnitName, '') unit_name, 
                IFNULL({$a_query[0]}, '') log_trans_number, IFNULL({$a_query[1]}, '') log_trans_date
                FROM log_stock
                JOIN {$this->db->database}.m_warehouse ON Log_StockM_WarehouseID = M_WarehouseID
                JOIN {$this->db->database}.m_item ON Log_StockM_ItemID = M_ItemID
                LEFT JOIN {$this->db->database}.m_customer ON Log_StockM_CustomerID = M_CustomerID
                LEFT JOIN {$this->db->database}.m_vendor ON Log_StockM_SupplierID = M_VendorID
                LEFT JOIN {$this->db->database}.m_unit ON M_ItemM_UnitID = M_UnitID
                {$a_query[2]}
                WHERE Log_StockM_ItemID = ? AND Log_StockIsActive = 'Y' AND Log_StockCode LIKE CONCAT(?, '%')
                ORDER BY Log_StockDate DESC
                LIMIT ?";
            $r = $db->query($query, [$id, $v, $limit]);
            if ($r)
            {
                $rst[$v] = $r->result_array();
            }
        }

        $l['records'] = $rst;

        return $l;
    }

    function history_detail($logid, $type)
    {
        $db = $this->load->database("dblog", true);
        $q = "";
        $data = ["customer_name" => "", 
            "vendor_name" => "", 
            "warehouse_name" => "", 
            "trans_number" => "", 
            "items" => [],
            "currency" => "Rp"];

        if ($type == 'SALES.DELIVERY')
        {
            // $q = "CALL sp_r_ONE-SAL-002-PROFORMA(?, ?)";
            $q = "SELECT L_SalesDetailL_SalesID id
            -- L_DeliveryDetailL_DeliveryID id
                    FROM log_stock
                    JOIN {$this->db->database}.l_deliverydetail ON L_DeliveryDetailID = Log_StockRefID
                    JOIN {$this->db->database}.l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
                    WHERE Log_StockID = ?";
            $mainid = $db->query($q, [$logid])->row();

            $data = $this->db->query("CALL `sp_r_ONE-SAL-002-PROFORMA`(?, ?)", [$mainid->id, 1])->row();
            $this->clean_mysqli_connection($this->db->conn_id);

            // $this->load->model('sales/l_delivery');
            // $data = $this->l_delivery->search( ['delivery_id' => $mainid->id, 'search' => '%', 'page' => 1, 'sdate' => '1971-01-01', 'edate' => date('Y-m-d') ] );

            $data->customer_phones = json_decode($data->customer_phones);
            $data->sales_phones = json_decode($data->sales_phones);
            $data->items = json_decode($data->items);
            // $data["customer_name"] = $r['records'][0]["customer_name"];
            // $data["warehouse_name"] = $r['records'][0]["warehouse_name"];
            // $data["trans_number"] = $r['records'][0]["delivery_number"];

            // $items = [];
            // foreach ($r['records'][0]['items'] as $k => $v)
            // {
            //     $items[] = ["item_name" => $v->item->item_name, 
            //                 "item_qty" => $v->qty,
            //                 "item_unit" => $v->item->item_unit,
            //                 "item_price" => 0,
            //                 "item_disc" => 0,
            //                 "item_ppn" => 0,
            //                 "item_subtotal" => 0];
            // }
            // $data["items"] = $items;

            return $data;
        }

        if ($type == 'PURCHASE.RECEIVE')
        {
            $q = "SELECT P_ReceiveDetailP_ReceiveID id
                    FROM log_stock
                    JOIN {$this->db->database}.p_receivedetail ON P_ReceiveDetailID = Log_StockRefID
                    WHERE Log_StockID = ?";
            $mainid = $db->query($q, [$logid])->row();

            $this->load->model('purchase/p_receive');
            $r = $this->p_receive->search( ['receive_id' => $mainid->id, 'search' => '%', 'page' => 1, 'sdate' => '1971-01-01', 'edate' => date('Y-m-d') ] );

            return $r['records'][0];
        }
    }
}

?>