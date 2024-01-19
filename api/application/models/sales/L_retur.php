<?php

class L_retur extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "l_retur";
        $this->table_key = "L_ReturID";
    }

    function save ( $d, $id = 0, $uid )
    {
        $r = $this->db->query("CALL sp_sales_retur_save(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        // $r->data = json_encode(['q'=>$this->db->last_query()]);
        return $r;
    }

    function search( $d )
    {
        // $dbiv = $this->load->database('dbiv', true);
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $customer = isset($d['customer_id'])?$d['customer_id']:0;
        $edits = isset($d['edits']) ? "OR FIND_IN_SET(L_ReturID, '{$d['edits']}')" : "";

        $r = $this->db->query(
                "SELECT L_ReturID retur_id, L_ReturNumber retur_number, DATE_FORMAT(L_ReturDate, '%d-%m-%Y') retur_date, L_ReturTotal retur_total,
                L_ReturNote retur_note, L_ReturPPN retur_ppn, `fn_conf`('PPN') ppn, L_ReturM_ItemID retur_item, ib.M_ItemName item_name, ib.M_ItemCode item_code,
                M_CustomerID customer_id, M_CustomerName customer_name,
                L_SalesID invoice_id, L_SalesNumber invoice_number, DATE_FORMAT(L_SalesDate, '%d-%m-%Y') invoice_date,
                M_WarehouseID warehouse_id, M_WarehouseName warehouse_name, F_MemoID memo_id, F_MemoNumber memo_number,

                CONCAT('[', GROUP_CONCAT(
                    JSON_OBJECT('detail_id', L_ReturDetailID, 'item_id', L_ReturDetailM_ItemID, 'retur_qty', L_ReturDetailQty,
                    'qty', L_SalesDetailQty, 'item_name', ia.M_ItemName, 'hpp', L_ReturDetailPrice, 
                    'hpp_non_ppn', IF(L_ReturPPN = 'Y', ((L_ReturDetailPrice - `fn_conf`('PPN')) / (100+`fn_conf`('PPN'))), L_ReturDetailPrice), 
                    'retur_note', IFNULL(L_ReturDetailNote, ''),
                    'unit_name', IFNULL(M_UnitName, ''), 'pack_name', IFNULL(M_PackName, ''), 'view_pack', ia.M_ItemViewInPack)
                    ), ']') items

                FROM `{$this->table_name}`
                JOIN l_sales ON L_ReturL_SalesID = L_SalesID
                JOIN m_customer ON L_ReturM_CustomerID = M_CustomerID
                JOIN m_warehouse ON L_ReturM_WarehouseID = M_WarehouseID
                JOIN m_item ib ON L_ReturM_ItemID = ib.M_ItemID
                left JOIN f_memo ON L_ReturF_MemoID = F_MemoID
                
                LEFT JOIN l_returdetail ON L_ReturDetailL_ReturID = L_ReturID AND L_ReturDetailIsActive = 'Y'
                LEFT JOIN m_item ia ON L_ReturDetailM_ItemID = ia.M_ItemID
                LEFT JOIN l_salesdetail ON L_SalesDetailL_SalesID = L_SalesID
                    AND L_SalesDetailIsActive = 'Y'
                    AND L_SalesDetailA_ItemID = ia.M_ItemID
                LEFT JOIN m_unit ON ia.M_ItemM_UnitID = M_UnitID
                LEFT JOIN m_pack ON ia.M_ItemM_PackID = M_PackID
                WHERE `L_ReturNumber` LIKE ?
                AND `L_ReturIsActive` = 'Y'
                AND ((L_ReturM_CustomerID = ? AND ? > 0) OR ? = 0)
                GROUP BY L_ReturID
                ORDER BY L_ReturDate DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $customer, $customer, $customer]);
        if ($r)
        {
            $l['q'] = $this->db->last_query();
            $r = $r->result_array();
            foreach($r as $k => $v) 
            {
                $details = $this->db->query("SELECT fn_sales_invoice_detail(?) x", [$v['invoice_id']])->row();
                $r[$k]['invoice'] = $details;

                // GET SALES
                $sales = json_decode($this->db->query("SELECT fn_sales_invoice_sales(?) x", [$v['invoice_id']])->row()->x);
                $r[$k]['sales'] = $sales;
            }
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN l_invoice ON L_ReturL_InvoiceID = L_InvoiceID
            JOIN m_customer ON L_ReturM_CustomerID = M_CustomerID
            JOIN m_warehouse ON L_ReturM_WarehouseID = M_WarehouseID
            JOIN f_memo ON L_ReturF_MemoID = F_MemoID
            WHERE `L_ReturNumber` LIKE ?
            AND ((L_ReturM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND `L_ReturIsActive` = 'Y'", [$d['search'], $customer, $customer, $customer]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_id($id)
    {
        $r = $this->db->query(
            "SELECT L_ReturID retur_id, L_ReturNumber retur_number, DATE_FORMAT(L_ReturDate, '%d-%m-%Y') retur_date, L_ReturTotal retur_total,
            L_ReturNote retur_note, L_ReturM_ItemID retur_item,
            M_CustomerID customer_id, M_CustomerName customer_name,
            L_SalesID sales_id, L_SalesNumber sales_number, DATE_FORMAT(L_SalesDate, '%d-%m-%Y') invoice_date,
            M_WarehouseID warehouse_id, M_WarehouseName warehouse_name, IFNULL(F_MemoID, 0) memo_id, IFNULL(F_MemoUsed, 0) memo_used, IFNULL(F_MemoRefunded, 0) memo_refunded,

            CONCAT('[', GROUP_CONCAT(
                    JSON_OBJECT('retur_id', L_ReturDetailID, 'detail_id', L_SalesDetailID, 'item_id', L_ReturDetailM_ItemID, 'retur_qty', L_ReturDetailQty,
                    'qty', L_SalesDetailQty, 'item_name', M_ItemName, 'hpp', L_ReturDetailPrice, 'price', L_ReturDetailPrice, 'disc', 0,
                    'hpp_non_ppn', IF(L_ReturPPN = 'Y', (L_ReturDetailPrice / (100+`fn_conf`('PPN'))), L_ReturDetailPrice),
                    'ppn_value', IF(L_ReturPPN = 'Y', ((L_ReturDetailPrice - `fn_conf`('PPN')) / (100+`fn_conf`('PPN'))), 0),
                    'retur_note', IFNULL(L_ReturDetailNote, ''), 'ppn', L_ReturPPN,
                    'unit_name', IFNULL(M_UnitName, ''), 'pack_name', IFNULL(M_PackName, ''), 'view_pack', M_ItemViewInPack)
                    ), ']') items
                    -- item.delivery_id = d.delivery.delivery_id
                    -- item.delivery_number = d.delivery_number
                    -- item.delivery_date = d.delivery_date
                    -- item.retur_qty = 0
                    -- item.note = ''
            FROM `{$this->table_name}`
            JOIN l_sales ON L_ReturL_SalesID = L_SalesID
            JOIN m_customer ON L_ReturM_CustomerID = M_CustomerID
            JOIN m_warehouse ON L_ReturM_WarehouseID = M_WarehouseID
            LEFT JOIN l_returdetail ON L_ReturDetailL_ReturID = L_ReturID AND L_ReturDetailIsActive = 'Y'
            LEFT JOIN f_memo ON L_ReturF_MemoID = F_MemoID
            LEFT JOIN m_item ON L_ReturDetailM_ItemID = M_ItemID
            LEFT JOIN l_salesdetail ON L_SalesDetailL_SalesID = L_SalesID
                    AND L_SalesDetailIsActive = 'Y'
                    AND L_SalesDetailA_ItemID = M_ItemID
            LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
            LEFT JOIN m_pack ON M_ItemM_PackID = M_PackID
            WHERE `L_ReturID` = ?
            AND `L_ReturIsActive` = 'Y'
            GROUP BY L_ReturID", [$id]);
        if ($r)
        {
            $r = $r->row();
            $r->details = json_decode($this->db->query("SELECT fn_sales_invoice_detail(?) x", [$r->sales_id])->row()->x);
            $r->items = json_decode($r->items);

            // GET SALES
            $sales = json_decode($this->db->query("SELECT fn_sales_invoice_sales(?) x", [$r->sales_id])->row()->x);
            $r->sales = $sales;

            return $r;
        }

        return false;
    }

    function search_autocomplete( $d )
    {
        $d['limit'] = 100;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function delete ($id, $uid = 0)
    {
        $r = $this->db->query("CALL sp_sales_retur_delete(?,?)", [$id, $uid])->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
}

?>