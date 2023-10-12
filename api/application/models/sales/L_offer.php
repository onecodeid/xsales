<?php

class L_Offer extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "l_offer";
        $this->table_key = "L_OfferID";
    }

    function search( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $customer = isset($d['customer_id'])?$d['customer_id']:0;
        // $staff = isset($d['staff_id'])?$d['staff_id']:0;
        $edits = isset($d['edits']) ? "OR FIND_IN_SET(L_OfferID, '{$d['edits']}')" : "";
        $used = isset($d['used'])?$d['used']:'';
        $lead = isset($d['lead'])?$d['lead']:0;
        $staff = isset($d['staff_id'])?$d['staff_id']:0;

        // Get One
        $id = isset($d['id'])?"AND L_OfferID = {$d['id']}":"";

        $r = $dbiv->query(
                "SELECT L_OfferID sales_id, L_OfferNumber sales_number, DATE_FORMAT(L_OfferDate, '%d-%m-%Y') sales_date, L_OfferTotal sales_total,
                0 sales_paid, 0 sales_unpaid, L_OfferUsed sales_used, L_OfferShipping sales_shipping, L_OfferGrandTotal sales_grandtotal,
                0 sales_disc, 0 sales_discrp,
                L_OfferDone sales_done, L_OfferM_LeadTypeID sales_lead, IFNULL(M_LeadTypeName, '') lead_name, IFNULL(M_LeadTypeCode, '') lead_code,
                L_OfferNote sales_note, L_OfferMemo sales_memo, IFNULL(L_OfferM_CustomerName, '') sales_customer_name, 
                M_CustomerName customer_name, M_CustomerCode customer_code,
                M_CustomerID customer_id, L_OfferS_StaffID sales_staff, S_StaffName staff_name,
                IFNULL(L_OfferFranco, '') sales_franco, IFNULL(L_OfferDelivery, '') sales_delivery,
                L_OfferM_PaymentPlanID sales_payment, L_OfferM_TermID sales_term, IFNULL(L_OfferValidity, '') sales_validity,
                IFNULL(L_OfferStockNote, '') sales_stocknote,
                IFNULL(L_SalesID, 0) so_id, IFNULL(L_SalesNumber, '') so_number,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('id', L_OfferDetailID, 'item', JSON_OBJECT('item_id', M_ItemID,'item_code', M_ItemCode,'item_name', M_ItemName), 'other', L_OfferDetailOther, 'other_name', IFNULL(L_OfferDetailOtherName, ''), 'price', L_OfferDetailPrice, 'qty', L_OfferDetailQty, 'disc', L_OfferDetailDisc, 'discrp', L_OfferDetailDiscRp, 'disctype', IF(L_OfferDetailDiscRp=0,'P','R'), 'ppn', L_OfferDetailPPN, 'ppn_amount', L_OfferDetailPPNAmount, 'total', L_OfferDetailTotal, 'subtotal', L_OfferDetailSubTotal, 'netto', L_OfferDetailSubTotal, 
                'pack', JSON_OBJECT('pack_id', IFNULL(pa.M_PackID, 0), 'pack_name', IFNULL(pa.M_PackName, 0)),
                'unit', JSON_OBJECT('unit_id', IFNULL(ua.M_UnitID, 0), 'unit_name', IFNULL(ua.M_UnitName, 0)) )), ']') details

                FROM `{$this->table_name}`
                JOIN m_customer ON L_OfferM_CustomerID = M_CustomerID
                    LEFT JOIN l_offerdetail ON L_OfferDetailL_OfferID=L_OfferID and L_OfferDetailIsActive = 'Y'
                    LEFT JOIN m_item ON M_ItemID = L_OfferDetailA_ItemID
                    LEFT JOIN m_unit ua ON L_OfferDetailM_UnitID = ua.M_UnitID
                    LEFT JOIN m_pack pa ON L_OfferDetailM_PackID = pa.M_PackID
                LEFT JOIN s_staff ON L_OfferS_StaffID = S_StaffID
                LEFT JOIN m_leadtype ON L_OfferM_LeadTypeID = M_LeadTypeID
                LEFT JOIN l_sales ON L_SalesL_OfferID = L_OfferID and L_SalesIsActive = 'Y'

                WHERE (`L_OfferNumber` LIKE ? OR M_CustomerName LIKE ?)
                {$id}
                AND `L_OfferIsActive` = 'Y'
                AND ((L_OfferM_CustomerID = ? AND ? > 0) OR ? = 0)
                AND ((L_OfferS_StaffID = ? AND ? > 0) OR ? = 0)
                AND L_OfferDate BETWEEN ? AND ?
                AND ((L_OfferUsed = ? AND ? <> '') OR ? = '' {$edits})
                AND ((L_OfferM_LeadTypeID = ? ANd ? <> 0) OR ? = 0)
                GROUP BY L_OfferID
                ORDER BY L_OfferDate DESC, L_OfferNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $customer, $customer, $customer,
                    $staff, $staff, $staff,
                    $d['sdate'], $d['edate'],
                    $used, $used, $used, $lead, $lead, $lead]);
        if ($r)
        {
            $r = $r->result_array();
            $this->load->model('inventory/i_stock');
            foreach ($r as $k => $v)
            {
                $details = json_decode($v['details']);
                foreach ($details as $m => $w)
                {
                    if ($w->other=='Y') $details[$m]->item = null;
                    else
                    {
                        $stocks = $this->i_stock->search_by_item($w->item->item_id);
                        $stock = 0;
                        foreach ($stocks as $n => $z) $stock += $z['stock_qty'];

                        $details[$m]->item->stocks = $stocks;
                        $details[$m]->item->stock = $stock;
                    }
                    
                }
                $r[$k]['details'] = $details; //json_decode($v['details']);

                // $r[$k]['details'] = json_decode($v['details']);
                // foreach ($r[$k]['details'] as $m => $w)
                //     if ($w->other=='Y') $r[$k]['details'][$m]->item = null;
            }
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_customer ON L_OfferM_CustomerID = M_CustomerID
            WHERE (`L_OfferNumber` LIKE ? OR M_CustomerName LIKE ?)
            {$id}
            AND ((L_OfferM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND ((L_OfferS_StaffID = ? AND ? > 0) OR ? = 0)
            AND L_OfferDate BETWEEN ? AND ?
            AND ((L_OfferUsed = ? AND ? <> '') OR ? = '' {$edits})
            AND ((L_OfferM_LeadTypeID = ? ANd ? <> 0) OR ? = 0)
            AND `L_OfferIsActive` = 'Y'", [$d['search'], $d['search'], $customer, $customer, $customer, 
                $staff, $staff, $staff,
                $d['sdate'], $d['edate'],
                $used, $used, $used, $lead, $lead, $lead]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_old( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $customer = isset($d['customer_id'])?$d['customer_id']:0;
        $edits = isset($d['edits']) ? "OR FIND_IN_SET(L_OfferID, '{$d['edits']}')" : "";

        $r = $dbiv->query(
                "SELECT L_OfferID sales_id, L_OfferNumber sales_number, L_OfferDate sales_date, L_OfferTotal sales_total,
                0 sales_paid, 0 sales_unpaid,
                L_OfferDone sales_note,
                L_OfferNote sales_note
                FROM `{$this->table_name}`
                WHERE `L_OfferNumber` LIKE ?
                AND `L_OfferIsActive` = 'Y'
                AND (`L_OfferDone` = 'N' {$edits})
                AND ((L_OfferM_CustomerID = ? AND ? > 0) OR ? = 0)
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $customer, $customer, $customer]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `L_OfferNumber` LIKE ?
            AND (`L_OfferLunas` = 'N' {$edits})
            AND ((L_OfferM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND `L_OfferIsActive` = 'Y'", [$d['search'], $customer, $customer, $customer]);
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
        $d['sdate'] = '2021-01-01';
        $d['edate'] = '2050-01-01';
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function save ( $d, $id = 0, $uid )
    {
        $hdata = json_decode($d['hdata']);
        $hdata->p_date = date("Y-m-d", strtotime($hdata->p_date));
        $d['hdata'] = json_encode($hdata);

        $r = $this->db->query("CALL sp_offer_save(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function delete ($id, $uid = 0)
    {
        $r = $this->db->query("CALL sp_offer_delete(?,?)", [$id, $uid])->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function get($id)
    {
        return $this->search([
            'page' => 1,
            'id' => $id,
            'sdate' => '2021-01-01',
            'edate' => '2050-01-01',
            'search' => '%'
        ]);
    }

    function convert_to_sales ( $id )
    {
        $r = $this->db->query("CALL sp_offer_sales_save(?)", [
                        $id
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function get_for_duplicate($id, $from = 'OFFER')
    {
        $offer_id = $id;
        $sales_id = 0;
        $delivery_id = 0;
        $invoice_id = 0;

        if ($from == 'SALES') {
            $sales_id = $id;
            $q = "SELECT L_SalesL_OfferID offer_id FROM l_sales wHERE L_SalesID = ?";
        } else if ($from == 'DELIVERY') {
            $delivery_id = $id;
            $q = "SELECT L_SalesL_OfferID offer_id, L_SalesID sales_id FROM l_delivery 
                    JOIN l_deliverydetail ON L_DeliveryDetailL_DeliveryID = L_DeliveryID AND L_DeliveryDetailIsActive = 'Y'
                    JOIN l_salesdetail ON L_DeliveryDetailL_salesDetailID = L_SalesDetailID
                    JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
                    wHERE L_DeliveryID = ? LIMIT 1";
        } else if ($from == 'INVOICE') {
            $invoice_id = $id;
            $q = "SELECT L_SalesL_OfferID offer_id, L_SalesID sales_id, L_DeliveryDetailL_DeliveryID delivery_id FROM l_invoice 
                    JOIN l_invoicedetail ON L_InvoiceDetailL_InvoiceID = L_InvoiceID AND L_InvoiceDetailIsActive = 'Y'
                    JOIN l_deliverydetail ON L_InvoiceDetailL_DeliveryID = L_DeliveryDetailL_DeliveryID AND L_InvoiceDetailA_ItemID = L_DeliveryDetailA_ItemID
                        AND L_DeliveryDetailIsActive = 'Y'
                    JOIN l_salesdetail ON L_DeliveryDetailL_salesDetailID = L_SalesDetailID
                    JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
                    wHERE L_InvoiceID = ? LIMIT 1";
        }

        if ($from != 'OFFER')
        {
            $r = $this->db->query($q, [$id])->row();
            $offer_id = $r->offer_id;
            if (isset($r->sales_id)) $sales_id = $r->sales_id;
            if (isset($r->delivery_id)) $delivery_id = $r->delivery_id;
        }

        // get hdata
        $q = "SELECT L_OfferDate offer_date, L_OfferM_LeadTypeID offer_lead, L_OfferTotal offer_total, L_OfferGrandTotal offer_grandtotal,
                IFNULL(L_OfferFranco, '') offer_franco, IFNULL(L_OfferStockNote, '') offer_stocknote, IFNULL(L_OfferDelivery, '') offer_delivery,
                IFNULL(L_OfferValidity, '') offer_validity, IFNULL(L_OfferNote, '') offer_note, IFNULL(L_OfferMemo, '') offer_memo,
                IFNULL(L_OfferStatus, '') offer_status, L_OfferM_LeadTypeID offer_lead,

                IFNULL(L_SalesDate, '') sales_date, IFNULL(L_SalesRef, '') sales_ref, IFNULL(L_SalesSubTotal, 0) sales_subtotal,
                IFNULL(L_SalesTotal, 0) sales_total, IFNULL(L_SalesDiscount, 0) sales_disc,	IFNULL(L_SalesDiscountRp, 0) sales_discrp,
                IFNULL(L_SalesTotalHPP, 0) sales_totalhpp, IFNULL(L_SalesPPN, 0) sales_ppn,	IFNULL(L_SalesDP, 0) sales_dp,
                IFNULL(L_SalesGrandTotal, 0) sales_grandtotal, IFNULL(L_SalesNote, '') sales_note,
                IFNULL(L_SalesMemo, '') sales_memo, IFNULL(L_SalesStatus, '') sales_status,
                IFNULL(L_SalesM_AffiliateID, 0) sales_affiliateid,
                IFNULL(L_SalesAffiliateFee, 0) sales_affiliatefee, IFNULL(L_SalesAffiliateFeeRp, 0) sales_affiliatefeerp,

                IFNULL(L_DeliveryDate, '') delivery_date,	
                IFNULL(L_DeliveryRefNumber, '') delivery_refnumber,
                IFNULL(L_DeliveryM_WarehouseID, 0) delivery_warehouse,
                IFNULL(L_DeliveryNote, '') delivery_note,
                IFNULL(L_DeliveryMemo, '') delivery_memo,
                IFNULL(L_DeliveryM_DeliveryTypeID, 0) delivery_type,
                IFNULL(L_DeliverySendNote, '') delivery_sendnote,
                IFNULL(L_DeliveryS_StaffID, 0) delivery_staff,

                IFNULL(L_InvoiceDate, '') invoice_date,
                IFNULL(L_InvoiceDueDate, '') invoice_duedate,
                IFNULL(L_InvoiceSubTotal, 0) invoice_subtotal,
                IFNULL(L_InvoiceTotal, 0) invoice_total,
                IFNULL(L_InvoiceDiscount, 0) invoice_discount,
                IFNULL(L_InvoiceDiscountRp, 0) invoice_discountrp,
                IFNULL(L_InvoiceDiscountTotal, 0) invoice_discounttotal,
                IFNULL(L_InvoiceDiscountTotalRp, 0) invoice_discounttotalrp,
                IFNULL(L_InvoicePPN, 0) invoice_ppn,
                IFNULL(L_InvoicePPNValue, 0) invoice_ppnvalue,
                IFNULL(L_InvoiceGrandTotal, 0) invoice_grandtotal,
                IFNULL(L_InvoiceNote, '') invoice_note,
                IFNULL(L_InvoiceMemo, '') invoice_memo,	

                L_OfferM_CustomerID x_customer,
                IFNULL(L_SalesIncludePPN, L_OfferIncludePPN) x_include_ppn,
                IFNULL(L_InvoiceShipping, IFNULL(L_SalesShipping, L_OfferShipping)) x_shipping,
                IFNULL(L_InvoiceM_TermID, IFNULL(L_SalesM_TermID, L_OfferM_TermID)) x_term,
                IFNULL(L_InvoiceS_StaffID, IFNULL(L_SalesS_StaffID, L_OfferS_StaffID)) x_staff,
                IF(L_DeliveryM_DeliveryAddressID IS NULL OR L_DeliveryM_DeliveryAddressID = 0, L_SalesM_DeliveryAddressID, L_DeliveryM_DeliveryAddressID) x_deliveryaddress,
                IFNULL(L_DeliveryM_ExpeditionID, L_SalesM_ExpeditionID) x_expedition
                FROM l_offer
                LEFT JOIN l_sales ON L_SalesID = ? AND ? <> 0
                LEFT JOIN l_delivery ON L_DeliveryID = ? AND ? <> 0
                LEFT JOIN l_invoice ON L_InvoiceID = ? AND ? <> 0
                WHERE L_OfferID = ?";
        $r = $this->db->query($q, [$sales_id, $sales_id, $delivery_id, $delivery_id, $invoice_id, $invoice_id, $offer_id])->row();

        // get details
        $what = strtolower($from);
        if ($what == 'delivery') $what = 'sales';

        $q = "SELECT JSON_OBJECT('item_id', M_ItemID, 'item_name', M_ItemName, 'item_code', M_ItemCode) item, l_{$what}detailqty qty,
            M_UnitID unit_id, M_UnitName unit_name, l_{$what}detailprice price, l_{$what}detaildisc disc, l_{$what}detaildiscrp discrp, 
            l_{$what}detailsubtotal subtotal, l_{$what}detailsubtotal netto, l_{$what}detailtotal total, l_{$what}detailppn ppn, l_{$what}detailppnamount ppnamount
            FROM l_{$what}detail JOIN m_item ON l_{$what}detaila_itemid = m_itemid
            JOIN m_unit ON M_ItemM_UnitID = M_UnitID 
            WHERE l_{$what}detaill_{$what}id = ? AND L_{$what}DetailIsActive = 'Y'";
        $rd = $this->db->query($q, [($what=='offer'?$offer_id:($what=='invoice'?$invoice_id:$sales_id))])->result_array();

        foreach($rd as $k => $v) $rd[$k]['item'] = json_decode($v['item']);
        $r->details = $rd;

        return $r;
    }
}

?>