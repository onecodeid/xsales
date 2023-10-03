<?php

class L_delivery extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "l_delivery";
        $this->table_key = "L_DeliveryID";
        $this->load->model('sales/l_sales');
    }

    function search_4_invoice( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['customer_id'])?$d['customer_id']:0;
        $warehouse = isset($d['warehouse_id'])?$d['warehouse_id']:0;
        $invoice_only = "";

        // single
        $deliveryid = isset($d['delivery_id'])?$d['delivery_id']:0;

        if (isset($d['invoice_only']))
        {
            $invoice_only = " AND L_DeliveryL_InvoiceID=0";
            if(isset($d['edits']))
            {
                $invoice_only = " AND (L_DeliveryL_InvoiceID=0 OR FIND_IN_SET(L_DeliveryID, '{$d['edits']}'))";
            }
        }

        // {"dp_id":"18","dp_number":"PDP/2202003","dp_date":"2022-02-10","dp_amount":"200000","dp_used":"0","dp_unused":"200000","dp_full":"N","dp_paymenttype":"1","dp_bankaccount":"0","dp_bank":"0","dp_giro_date":"0000-00-00","dp_giro_number":"","dp_transfer_date":"0000-00-00","dp_note":"00200/AW-PI/02/2022","customer_name":" Bpk. Benni","customer_id":"2095","paymenttype_name":"CASH","dp_acc":"Y","journal_id":"686","journal_date":"2022-02-10","journal_note":"00200/AW-PI/02/2022","journal_receipt":"PDP/2202003","journal_account":"120","account_name":"Kas Zulham","details":[{"account":{"account_id":120,"account_code":"1-1000102","account_name":"Kas Zulham","parent_code":""},"debit":200000,"credit":0,"post":"N"},{"account":{"account_id":30,"account_code":"2-20203","account_name":"Pendapatan Diterima Di Muka","parent_code":""},"debit":0,"credit":200000,"post":"N"}]}

        $r = $dbiv->query(
                "SELECT L_DeliveryID delivery_id, L_DeliveryNumber delivery_number, DATE_FORMAT(L_DeliveryDate, '%d-%m-%Y') delivery_date, L_DeliveryTotalQty delivery_total_qty,
                L_DeliveryNote delivery_note, IFNULL(L_DeliveryMemo, '') delivery_memo, M_CustomerName customer_name,
                M_WarehouseName warehouse_name, M_CustomerID customer_id, M_WarehouseID warehouse_id,
                CONCAT('[',GROUP_CONCAT(JSON_OBJECT('sales_id',L_SalesDetailID,'sales_memo',IFNULL(L_SalesMemo, ''),'item_id',M_ItemID,'item_code',
                M_ItemCode,'item_name',IFNULL(L_SalesDetailA_ItemName, M_ItemName),'item_unit',IFNULL(M_UnitName, ''),'qty',L_DeliveryDetailQty,'price',L_SalesDetailPrice,'disc',L_SalesDetailDisc,'discrp',L_SalesDetailDiscRp,'ppn',L_SalesDetailPPN,'ppn_amount',L_SalesDetailPPNAmount,'include_ppn',L_SalesIncludePPN,'note',L_DeliveryDetailNote,'term',IFNULL(L_SalesM_TermID, 0), 'ppn_value', fn_conf('ppn'))),']') items,
                L_DeliveryS_StaffID delivery_staff, sa.S_StaffName staff_name,
                IFNULL(GROUP_CONCAT(DISTINCT L_SalesMemo SEPARATOR ';'), '') sales_memos,
                IFNULL(F_PaymentDpID, 0) dp_id, IFNULL(F_PaymentDpNumber, '') dp_number, IFNULL(F_PaymentDpAmount, 0) dp_amount,
                IFNULL(L_SalesM_TermID, 0) term_id, IFNULL(M_TermDuration, 0) term_duration,
                
                JSON_OBJECT('dp_id',IFNULL(F_PaymentDpID, 0),'dp_number',IFNULL(F_PaymentDpNumber, ''),
                    'dp_date',F_PaymentDpDate,'d_amount',IFNULL(F_PaymentDpAmount, 0),'dp_used',IFNULL(F_PaymentDpAmount, 0),
                    'dp_unused',IFNULL(F_PaymentDpUnused, 0), 'dp_acc', IFNULL(F_PaymentDPAcc, 'N')) dp

                FROM `{$this->table_name}`
                JOIN m_customer ON L_DeliveryM_CustomerID = M_CustomerID
                JOIN m_warehouse ON L_DeliveryM_WarehouseID = M_WarehouseID
                    LEFT JOIN l_deliverydetail ON L_DeliveryID = L_DeliveryDetailL_DeliveryID AND L_DeliveryDetailIsactive= 'Y'
                    LEFT JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
                    LEFT JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
                    LEFT JOIN m_item ON M_ItemID = L_SalesDetailA_ItemID
                    LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                    LEFT JOIN s_staff sa ON L_DeliveryS_StaffID = sa.S_StaffID
                    LEFT JOIN m_term ON l_salesm_termid = m_termid

                    LEFT JOIN f_paymentdp ON L_SalesF_PaymentDPID = F_PaymentDpID
                WHERE (`L_DeliveryNumber` LIKE ? OR `M_CustomerName` LIKE ?)
                AND `L_DeliveryIsActive` = 'Y'
                AND ((L_DeliveryM_CustomerID = ? AND ? > 0) OR ? = 0)
                AND ((L_DeliveryM_WarehouseID = ? AND ? > 0) OR ? = 0)                
                {$invoice_only}
                AND ((L_DeliveryID = ? AND ? <> 0) OR ? = 0)
                GROUP BY L_DeliveryID
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse,
                    $deliveryid, $deliveryid, $deliveryid]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $items = (array)json_decode($v['items']);
                foreach ($items as $kk => $vv)
                {
                    $vv->subtotal = ($vv->qty * $vv->price * (100-$vv->disc) / 100) - ($vv->qty * $vv->discrp);
                    $ppnv = $vv->ppn_value / 100;

                    if ($vv->ppn=="Y"&&$vv->include_ppn=="Y")
                    {
                        $vv->price_nett = $vv->price / (1+$ppnv);
                        $y = ($vv->qty * $vv->price_nett * (100-$vv->disc) / 100) - ($vv->qty * $vv->discrp);
                        $vv->ppn_amount = $vv->subtotal - $y;
                        $vv->subtotal = $vv->subtotal - $vv->ppn_amount;
                    }
                    
                    if ($vv->ppn=="Y"&&$vv->include_ppn=="N")
                        $vv->ppn_amount = $vv->subtotal * $ppnv;

                        // $vv->subtotal = $vv->subtotal * 1.1;
                    $items[$kk] = $vv;
                }
                $r[$k]['items'] = $items;
                
                // DELIVERY ADDRESS
                $r[$k]['delivery_address'] = $this->get_address($v['delivery_id']);
            }
                
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_customer ON L_DeliveryM_CustomerID = M_CustomerID
            JOIN m_warehouse ON L_DeliveryM_WarehouseID = M_WarehouseID
            WHERE (`L_DeliveryNumber` LIKE ? OR `M_CustomerName` LIKE ?)
            AND ((L_DeliveryM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND ((L_DeliveryM_WarehouseID = ? AND ? > 0) OR ? = 0)
            AND `L_DeliveryIsActive` = 'Y'
            AND ((L_DeliveryID = ? AND ? <> 0) OR ? = 0)
            {$invoice_only}", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse,
                    $deliveryid, $deliveryid, $deliveryid]);
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

    function search_dd( $d )
    {
        $d['limit'] = 200;
        $d['page'] = 1;
        $d['invoice_only'] = true;

        $r = $this->search_4_invoice( $d );
        
        return $r['records'];
    }

    function save ( $d, $id = 0, $uid )
    {
        $hdata = json_decode($d['hdata']);
        $hdata->p_date = date("Y-m-d", strtotime($hdata->p_date));
        $d['hdata'] = json_encode($hdata);
        
        $r = $this->db->query("CALL sp_sales_delivery_save(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function save_proforma ( $d, $id = 0, $uid )
    {
        $r = $this->db->query("CALL sp_sales_delivery_proforma_save(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function delete ( $id, $uid )
    {
        $r = $this->db->query("CALL sp_sales_delivery_delete(?,?)", [
                        $id,
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function confirm ( $id, $uid )
    {
        $r = $this->db->query("CALL sp_sales_delivery_confirm(?,?)", [
                        $id,
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function invoice_create ( $d, $id, $uid = 0 )
    {
        $r = $this->db->query("CALL sp_delivery_invoice_create(?,?,?,?)", [
                        $id,
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function search( $d )
    {
        $this->load->model('sales/l_invoice');

        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['customer_id'])?$d['customer_id']:0;
        $warehouse = isset($d['warehouse_id'])?$d['warehouse_id']:0;
        $invoice_only = "";

        // single
        $deliveryid = isset($d['delivery_id'])?$d['delivery_id']:0;

        if (isset($d['invoice_only']))
        {
            $invoice_only = " AND L_DeliveryL_InvoiceID=0";
            if(isset($d['edits']))
            {
                $invoice_only = " AND (L_DeliveryL_InvoiceID=0 OR FIND_IN_SET(L_DeliveryID, '{$d['edits']}'))";
            }
        }

        $r = $dbiv->query(
                "SELECT L_DeliveryID delivery_id, L_DeliveryNumber delivery_number, L_DeliveryRefNumber delivery_ref_number, DATE_FORMAT(L_DeliveryDate, '%d-%m-%Y') delivery_date, 
                L_DeliveryTotalQty delivery_total_qty, L_DeliveryConfirm delivery_confirm, L_DeliveryM_DeliveryTypeID delivery_type, 
                L_DeliveryM_ExpeditionID delivery_expedition,
                IFNULL(L_DeliverySendNote, '') delivery_send_note, L_DeliveryProforma delivery_proforma,
                L_DeliveryNote delivery_note, L_DeliveryMemo delivery_memo, L_DeliveryL_InvoiceID delivery_invoice, M_CustomerName customer_name,
                M_WarehouseName warehouse_name, M_CustomerID customer_id, M_WarehouseID warehouse_id,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('deliverydetail_id', IFNULL(L_DeliveryDetailID, 0), 'item', JSON_OBJECT('detail_done', '',
                    'detail_id', l_salesdetailid,
                    'detail_qty', l_salesdetailqty,
                    'detail_sent', l_salesdetailsent,
                    'detail_unsent', l_salesdetailunsent,
                    'item_id', M_ItemID,
                    'item_code', M_ItemCode,
                    'item_name', IFNULL(L_SalesDetailA_ItemName, M_ItemName),
                    'item_unit', M_UnitName,
                    'stock', IFNULL(I_StockQty, 0),
                    'sales_id', L_SalesID,
                    'sales_date', DATE_FORMAT(L_SalesDate, '%d-%m-%Y'),
                    'sales_number', L_SalesNumber,
                    'sales_memo', IFNULL(L_SalesMemo, ''),
                    'customer_name', M_CustomerName,
                    'staff_name', IFNULL(stb.S_StaffName, '')), 'note', L_DeliveryDetailNote, 'qty', L_DeliveryDetailQty, 'total', 0)),']') items,

                L_DeliveryS_StaffID delivery_staff, sta.S_StaffName staff_name, stb.S_StaffShortName sales_name,
                IFNULL(GROUP_CONCAT(distinct L_SalesMemo SEPARATOR ';'), '') sales_memos,
                count(M_ItemID) item_cnt, L_DeliveryM_DeliveryAddressID address_id,

                L_SalesTotal sales_total,
                L_SalesDiscount sales_disc,
                L_SalesDiscountRp sales_discrp,
                L_SalesTotalHPP sales_hpp,
                L_SalesShipping sales_shipping,
                L_SalesPPN sales_ppn,
                L_SalesDP sales_dp,
                L_SalesGrandTotal sales_grandtotal

                FROM `{$this->table_name}`
                JOIN m_customer ON L_DeliveryM_CustomerID = M_CustomerID
                JOIN m_warehouse ON L_DeliveryM_WarehouseID = M_WarehouseID
                    LEFT JOIN l_deliverydetail ON L_DeliveryID = L_DeliveryDetailL_DeliveryID AND L_DeliveryDetailIsactive= 'Y'
                    LEFT JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
                    LEFT JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
                    LEFT JOIN s_staff stb ON L_SalesS_StaffID = stb.S_StaffID
                    LEFT JOIN m_item ON M_ItemID = L_SalesDetailA_ItemID
                    LEFT JOIN m_unit ON M_UnitID = M_ItemM_UnitID
                    LEFT JOIN s_staff sta ON L_DeliveryS_StaffID = sta.S_StaffID
                    LEFT JOIN i_stock ON I_StockIsActive = 'Y' AND I_StockM_ItemID = M_ItemID AND I_StockM_WarehouseID = L_DeliveryM_WarehouseID
                WHERE (`L_DeliveryNumber` LIKE ? OR `M_CustomerName` LIKE ?)
                AND `L_DeliveryIsActive` = 'Y'
                AND ((L_DeliveryM_CustomerID = ? AND ? > 0) OR ? = 0)
                AND ((L_DeliveryM_WarehouseID = ? AND ? > 0) OR ? = 0)
                AND L_DeliveryDate BETWEEN ? AND ?
                AND ((L_DeliveryID = ? AND ? <> 0) OR ? = 0)
                {$invoice_only}
                GROUP BY L_DeliveryID
                ORDER BY L_DeliveryDate DESC, L_DeliveryNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse, $d['sdate'], $d['edate'],
                    $deliveryid, $deliveryid, $deliveryid]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                if (round($v['item_cnt']) > 0)
                    $items = (array)json_decode($v['items']);
                else
                    $items = [];
                // foreach ($items as $kk => $vv)
                // {
                //     $vv->subtotal = $vv->qty * $vv->price * (100-$vv->disc) / 100;
                //     if ($vv->ppn=="Y"&&$vv->include_ppn=="N")
                //         $vv->subtotal = $vv->subtotal * 1.1;
                //     $items[$kk] = $vv;
                // }
                $r[$k]['items'] = $items;
                $r[$k]['delivery_address'] = $this->get_address($v['delivery_id']);
                $r[$k]['invoices'] = $this->l_invoice->search_by_do_id($v['delivery_id']);
            }
                
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_customer ON L_DeliveryM_CustomerID = M_CustomerID
            JOIN m_warehouse ON L_DeliveryM_WarehouseID = M_WarehouseID
            WHERE (`L_DeliveryNumber` LIKE ? OR `M_CustomerName` LIKE ?)
            AND ((L_DeliveryM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND ((L_DeliveryM_WarehouseID = ? AND ? > 0) OR ? = 0)
            AND `L_DeliveryIsActive` = 'Y'
            AND L_DeliveryDate BETWEEN ? AND ?
            AND ((L_DeliveryID = ? AND ? <> 0) OR ? = 0)
            {$invoice_only}", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse, $d['sdate'], $d['edate'],
                $deliveryid, $deliveryid, $deliveryid]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_proforma( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['customer_id'])?$d['customer_id']:0;
        $warehouse = isset($d['warehouse_id'])?$d['warehouse_id']:0;
        $invoice_only = "";

        $r = $dbiv->query(
                "SELECT L_DeliveryID delivery_id, L_DeliveryNumber delivery_number, L_DeliveryRefNumber delivery_ref_number, L_DeliveryDate delivery_date, L_DeliveryTotalQty delivery_total_qty, L_DeliveryConfirm delivery_confirm, L_DeliveryM_DeliveryTypeID delivery_type, IFNULL(L_DeliverySendNote, '') delivery_send_note,
                L_DeliveryM_ExpeditionID delivery_expedition,
                L_DeliveryNote delivery_note, L_DeliveryMemo delivery_memo, L_DeliveryL_InvoiceID delivery_invoice,
                L_SalesDate sales_date, L_SalesNumber sales_number, L_InvoiceDate invoice_date, L_InvoiceNumber invoice_number,
                L_InvoiceGrandTotal invoice_grandtotal, fn_terbilang(L_InvoiceGrandTotal) invoice_terbilang,
                M_CustomerName customer_name,
                M_WarehouseName warehouse_name, M_CustomerID customer_id, M_WarehouseID warehouse_id,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('item', JSON_OBJECT('detail_done', '',
                    'detail_id', l_salesdetailid,
                    'detail_qty', l_salesdetailqty,
                    'detail_sent', l_salesdetailsent,
                    'detail_unsent', l_salesdetailunsent,
                    'item_id', M_ItemID,
                    'item_name', M_ItemName,
                    'item_unit', M_UnitName,
                    'sales_date', DATE_FORMAT(L_SalesDate, '%d-%m-%Y'),
                    'sales_number', L_SalesNumber,
                    'sales_memo', IFNULL(L_SalesMemo, ''),
                    'customer_name', M_CustomerName), 'note', L_DeliveryDetailNote, 'qty', L_DeliveryDetailQty, 'total', 0)),']') items,

                L_DeliveryS_StaffID delivery_staff, S_StaffName staff_name

                FROM `{$this->table_name}`
                JOIN m_customer ON L_DeliveryM_CustomerID = M_CustomerID
                JOIN l_invoice ON L_DeliveryL_InvoiceID = L_InvoiceID
                LEFT JOIN m_warehouse ON L_DeliveryM_WarehouseID = M_WarehouseID
                    LEFT JOIN l_deliverydetail ON L_DeliveryID = L_DeliveryDetailL_DeliveryID AND L_DeliveryDetailIsactive= 'Y'
                    LEFT JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
                    LEFT JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
                    LEFT JOIN m_item ON M_ItemID = L_SalesDetailA_ItemID
                    LEFT JOIN m_unit ON M_UnitID = M_ItemM_UnitID
                    LEFT JOIN s_staff ON L_DeliveryS_StaffID = S_StaffID
                WHERE (`L_DeliveryNumber` LIKE ? OR `M_CustomerName` LIKE ?)
                AND `L_DeliveryIsActive` = 'Y'
                AND ((L_DeliveryM_CustomerID = ? AND ? > 0) OR ? = 0)
                AND ((L_DeliveryM_WarehouseID = ? AND ? > 0) OR ? = 0)
                AND L_DeliveryProforma = 'Y'
                AND L_DeliveryNumber IS NULL
                {$invoice_only}
                GROUP BY L_DeliveryID
                ORDER BY L_DeliveryDate DESC, L_DeliveryNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $items = (array)json_decode($v['items']);
                // foreach ($items as $kk => $vv)
                // {
                //     $vv->subtotal = $vv->qty * $vv->price * (100-$vv->disc) / 100;
                //     if ($vv->ppn=="Y"&&$vv->include_ppn=="N")
                //         $vv->subtotal = $vv->subtotal * 1.1;
                //     $items[$kk] = $vv;
                // }
                $r[$k]['items'] = $items;
            }
                
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_customer ON L_DeliveryM_CustomerID = M_CustomerID
            LEFT JOIN m_warehouse ON L_DeliveryM_WarehouseID = M_WarehouseID
            WHERE (`L_DeliveryNumber` LIKE ? OR `M_CustomerName` LIKE ?)
            AND ((L_DeliveryM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND ((L_DeliveryM_WarehouseID = ? AND ? > 0) OR ? = 0)
            AND `L_DeliveryIsActive` = 'Y'
            AND L_DeliveryProforma = 'Y'
            AND L_DeliveryNumber IS NULL
            {$invoice_only}", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function get_address($id)
    {
        $r = $this->db->query("SELECT L_SalesDetailL_SalesID id
            FROM l_delivery
            JOIN l_deliverydetail ON L_DeliveryDetailL_DeliveryID = L_DeliveryID AND L_DeliveryDetailIsActive = 'Y'
            JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
            WHERE L_DeliveryID = ?
            LIMIT 1", $id)->row();


        $a = json_decode(json_encode([]));
        if (!!$r)
            $a = $this->l_sales->get_address($r->id);

        return $a;
    }

    function save_item_name($id, $name)
    {
        $r = $this->db->set("L_DeliveryDetailA_ItemName", $name)
                ->where("L_DeliveryDetailID", $id)
                ->update('l_deliverydetail');

        return ["item_id"=>$id, "item_name"=>$name];
    }

    function get_parent_id($id)
    {
        $r = $this->db->where('L_DeliveryDetailID', $id)
                    ->get('l_deliverydetail')
                    ->row();
        return $r->L_DeliveryDetailL_DeliveryID;
    }
}

?>