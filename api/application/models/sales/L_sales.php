<?php

class L_Sales extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "l_sales";
        $this->table_key = "L_SalesID";
    }

    function search( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $customer = isset($d['customer_id'])?$d['customer_id']:0;
        $staff = isset($d['staff_id'])?$d['staff_id']:0;
        $edits = isset($d['edits']) ? "OR FIND_IN_SET(L_SalesID, '{$d['edits']}')" : "";
        $done = isset($d['done']) ? "AND L_SalesDone = '{$d['done']}'" : "";

        $r = $dbiv->query(
                "SELECT L_SalesID sales_id, L_SalesNumber sales_number, DATE_FORMAT(L_SalesDate, '%d-%m-%Y') sales_date, L_SalesTotal sales_total,
                0 sales_paid, 0 sales_unpaid, L_SalesShipping sales_shipping, L_SalesDP sales_dp, L_SalesGrandTotal sales_grandtotal,
                L_SalesDiscount sales_disc, L_SalesDiscountRp sales_discrp,
                L_SalesDone sales_done, L_SalesProforma sales_proforma, L_SalesProformaNumber proforma_number, L_SalesProformaDueDate proforma_duedate,
                L_SalesNote sales_note, L_SalesMemo sales_memo, M_CustomerName customer_name, IFNULL(L_SalesRef, '') sales_ref,
                L_SalesIncludePPN sales_ppn,
                M_CustomerID customer_id, L_SalesS_StaffID sales_staff, S_StaffName staff_name,
                L_OfferID offer_id, L_OfferNumber offer_number, L_OfferDate offer_date,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('item', JSON_OBJECT('item_id', M_ItemID,'item_name', IFNULL(L_SalesDetailA_ItemName, M_ItemName)), 'price', L_SalesDetailPrice, 'qty', L_SalesDetailQty, 'disc', L_SalesDetailDisc, 'discrp', L_SalesDetailDiscRp, 'disctype', IF(L_SalesDetailDiscRp=0,'P','R'), 'ppn', L_SalesDetailPPN, 'ppn_amount', L_SalesDetailPPNAmount, 'total', L_SalesDetailTotal, 
                'subtotal', L_SalesDetailQty * ((L_SalesDetailPrice * (100 - L_SalesDetailDisc) / 100) - L_SalesDetailDiscRp), 
                'netto', L_SalesDetailSubtotal, 'detail_id', L_SalesDetailID)), ']') details,
                L_SalesM_DeliveryAddressID address_id, L_SalesM_PaymentPlanID payment_id, L_SalesM_TermID term_id,
                L_SalesM_ExpeditionID expedition_id, L_SalesL_InvoiceID sales_invoice,
                L_SalesM_AffiliateID affiliate_id, L_SalesAffiliateFee affiliate_fee, IFNULL(M_AffiliateName, '') affiliate_name

                FROM `{$this->table_name}`
                JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID
                    LEFT JOIN l_salesdetail ON L_SalesDetailL_SalesID=L_SalesID and L_SalesDetailIsActive = 'Y'
                    LEFT JOIN m_item ON M_ItemID = L_SalesDetailA_ItemID
                LEFT JOIN s_staff ON L_SalesS_StaffID = S_StaffID
                LEFT JOIN m_affiliate ON L_SalesM_AffiliateID = M_AffiliateID
                LEFT JOIN l_offer ON L_SalesL_OfferID = L_OfferID
                WHERE (`L_SalesNumber` LIKE ? OR `M_CustomerName` LIKE ?)
                AND `L_SalesIsActive` = 'Y'
                AND ((L_SalesM_CustomerID = ? AND ? > 0) OR ? = 0)
                AND ((L_SalesS_StaffID = ? AND ? > 0) OR ? = 0)
                AND L_SalesDate BETWEEN ? AND ? {$done}
                GROUP BY L_SalesID
                ORDER BY L_SalesDate DESC, L_SalesNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $customer, $customer, $customer, 
                    $staff, $staff, $staff,
                    ($d['sdate']==null?'1971-01-01':$d['sdate']), $d['edate']]);
        if ($r)
        {
            $r = $r->result_array();
            $this->load->model('inventory/i_stock');
            foreach ($r as $k => $v)
            {    
                
                    

                $details = json_decode($v['details']);
                foreach ($details as $m => $w)
                {
                    $stocks = $this->i_stock->search_by_item($w->item->item_id);
                    $stock = 0;
                    foreach ($stocks as $n => $z) $stock += $z['stock_qty'];

                    $details[$m]->item->stocks = $stocks;
                    $details[$m]->item->stock = $stock;

                    // $details[$m]->stock = $this->i_stock->search_by_item($w->item);
                }
                $r[$k]['details'] = $details; //json_decode($v['details']);
            }
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID
            WHERE (`L_SalesNumber` LIKE ? OR `M_CustomerName` LIKE ?)
            AND ((L_SalesM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND ((L_SalesS_StaffID = ? AND ? > 0) OR ? = 0)
            AND L_SalesDate BETWEEN ? AND ? {$done}
            AND `L_SalesIsActive` = 'Y'", [$d['search'], $d['search'], $customer, $customer, $customer, 
                $staff, $staff, $staff,
                ($d['sdate']==null?'1971-01-01':$d['sdate']), $d['edate']]);
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
        $edits = isset($d['edits']) ? "OR FIND_IN_SET(L_SalesID, '{$d['edits']}')" : "";

        $r = $dbiv->query(
                "SELECT L_SalesID sales_id, L_SalesNumber sales_number, L_SalesDate sales_date, L_SalesTotal sales_total,
                0 sales_paid, 0 sales_unpaid,
                L_SalesDone sales_note,
                L_SalesNote sales_note
                FROM `{$this->table_name}`
                WHERE `L_SalesNumber` LIKE ?
                AND `L_SalesIsActive` = 'Y'
                AND (`L_SalesDone` = 'N' {$edits})
                AND ((L_SalesM_CustomerID = ? AND ? > 0) OR ? = 0)
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $customer, $customer, $customer]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `L_SalesNumber` LIKE ?
            AND (`L_SalesLunas` = 'N' {$edits})
            AND ((L_SalesM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND `L_SalesIsActive` = 'Y'", [$d['search'], $customer, $customer, $customer]);
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
        $hdata = json_decode($d['hdata']);
        $hdata->p_date = date("Y-m-d", strtotime($hdata->p_date));
        $d['hdata'] = json_encode($hdata);
        
        $r = $this->db->query("CALL sp_sales_save(?,?,?,?)", [
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
        $r = $this->db->query("CALL sp_sales_delete(?,?)", [$id, $uid])->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function search_4_proforma( $d )
    {
        $d['limit'] = 100;
        $d['page'] = 1;

        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['customer_id'])?$d['customer_id']:0;
        $warehouse = isset($d['warehouse_id'])?$d['warehouse_id']:0;
        $invoice_only = "";

        $sales_id = isset($d['sales_id'])?"AND L_SalesID = {$d['sales_id']}":"";

        if (isset($d['invoice_only']))
        {
            $invoice_only = " AND L_SalesDone='N'";
            // if(isset($d['edits']))
            // {
            //     $invoice_only = " AND (L_DeliveryL_InvoiceID=0 OR FIND_IN_SET(L_DeliveryID, '{$d['edits']}'))";
            // }
        }

        $r = $dbiv->query(
                "SELECT L_SalesID sales_id, L_SalesNumber sales_number, L_SalesDate sales_date, 0 sales_total_qty,
                L_SalesNote sales_note, IFNULL(L_SalesMemo, '') sales_memo, M_CustomerName customer_name, L_SalesProforma sales_proforma,
                '' warehouse_name, M_CustomerID customer_id, 0 warehouse_id,
                CONCAT('[',GROUP_CONCAT(JSON_OBJECT('sales_id',L_SalesDetailID,'sales_memo',IFNULL(L_SalesMemo, ''),'item_id', M_ItemID,'item_name', M_ItemName,'item_unit',M_UnitName,'qty',L_SalesDetailQty,'price',L_SalesDetailPrice,'disc',L_SalesDetailDisc,'discrp',L_SalesDetailDiscRp,'ppn',L_SalesDetailPPN,'ppn_amount',L_SalesDetailPPNAmount,'include_ppn',L_SalesIncludePPN,'note',L_SalesDetailNote)),']') items,
                L_SalesS_StaffID sales_staff, S_StaffName staff_name
                FROM `{$this->table_name}`
                JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID
                
                    JOIN l_salesdetail ON L_SalesDetailL_SalesID = L_SalesID
                    JOIN m_item ON M_ItemID = L_SalesDetailA_ItemID
                    JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                    LEFT JOIN s_staff ON L_SalesS_StaffID = S_StaffID
                WHERE (`L_SalesNumber` LIKE ? OR `M_CustomerName` LIKE ?)
                AND `L_SalesIsActive` = 'Y'
                AND ((L_SalesM_CustomerID = ? AND ? > 0) OR ? = 0)
                
                {$invoice_only}
                {$sales_id}
                GROUP BY L_SalesID
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $supplier, $supplier, $supplier]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $items = (array)json_decode($v['items']);
                foreach ($items as $kk => $vv)
                {
                    $vv->subtotal = ($vv->qty * $vv->price * (100-$vv->disc) / 100) - ($vv->qty * $vv->discrp);

                    if ($vv->ppn=="Y"&&$vv->include_ppn=="Y")
                    {
                        $vv->price = $vv->price / 1.1;
                        $y = ($vv->qty * $vv->price * (100-$vv->disc) / 100) - ($vv->qty * $vv->discrp);
                        $vv->ppn_amount = $vv->subtotal - $y;
                    }
                    
                    if ($vv->ppn=="Y"&&$vv->include_ppn=="N")
                        $vv->ppn_amount = $vv->subtotal * 0.1;

                        // $vv->subtotal = $vv->subtotal * 1.1;
                    $items[$kk] = $vv;
                }
                $r[$k]['items'] = $items;
            }
                
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID
            WHERE (`L_SalesNumber` LIKE ? OR `M_CustomerName` LIKE ?)
            AND ((L_SalesM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND `L_SalesIsActive` = 'Y'
            {$sales_id}
            {$invoice_only}", [$d['search'], $d['search'], $supplier, $supplier, $supplier]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function save_proforma ( $id, $d, $uid = 0 )
    {
        $r = $this->db->query("CALL sp_invoice_proforma_save_new(?,?,?)", [
                        $id,
                        $d['hdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function get_address($id)
    {
        $r = $this->db->query("SELECT M_DeliveryAddressID address_id, M_DeliveryAddressName address_name, 
            M_DeliveryAddressDesc address_desc,
            IFNULL(M_KelurahanName, '') village_name, IFNULL(M_DistrictName, '') district_name,
            IFNULL(M_CityName, '') city_name, IFNULL(M_ProvinceName, '') province_name,
            IFNULL(M_DeliveryAddressPhones, '[]') phones
            FROM l_sales
            LEFT JOIN m_deliveryaddress ON 
                ((L_SalesM_DeliveryAddressID = M_DeliveryAddressID AND L_SalesM_DeliveryAddressID <> 0) OR 
                (L_SalesM_DeliveryAddressID = 0 AND M_DeliveryAddressM_CustomerID = L_SalesM_CustomerID AND M_DeliveryAddressIsMain = 'Y'
                    AND M_DeliveryAddressIsActive = 'Y'))
            LEFT JOIN m_kelurahan ON M_DeliveryAddressM_KelurahanID = M_KelurahanID
            LEFT JOIN m_district ON M_DeliveryAddressM_DistrictID = M_DistrictID
            LEFT JOIN m_city ON M_DeliveryAddressM_CityID = M_CityID
            LEFT JOIN m_province ON M_CityM_ProvinceID = M_ProvinceID
            WHERE L_SalesID = ?
            LIMIT 1", $id)->row();

        $r->phones = json_decode($r->phones);
        return $r;
    }

    function search_id( $id )
    {
        $r = $this->db->query(
                "SELECT L_SalesID sales_id, L_SalesNumber sales_number, DATE_FORMAT(L_SalesDate, '%d-%m-%Y') sales_date, L_SalesTotal sales_total,
                0 sales_paid, 0 sales_unpaid, L_SalesShipping sales_shipping, L_SalesDP sales_dp, L_SalesGrandTotal sales_grandtotal,
                L_SalesDiscount sales_disc, L_SalesDiscountRp sales_discrp,
                L_SalesDone sales_done, L_SalesProforma sales_proforma, L_SalesProformaNumber proforma_number, L_SalesProformaDueDate proforma_duedate,
                L_SalesNote sales_note, L_SalesMemo sales_memo, M_CustomerName customer_name, IFNULL(L_SalesRef, '') sales_ref,
                L_SalesIncludePPN sales_ppn,
                M_CustomerID customer_id, L_SalesS_StaffID sales_staff, S_StaffName staff_name,
                L_OfferID offer_id, L_OfferNumber offer_number, L_OfferDate offer_date,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('item', JSON_OBJECT('item_id', M_ItemID,'item_name', IFNULL(L_SalesDetailA_ItemName, M_ItemName)), 'price', L_SalesDetailPrice, 'qty', L_SalesDetailQty, 'disc', L_SalesDetailDisc, 'discrp', L_SalesDetailDiscRp, 'disctype', IF(L_SalesDetailDiscRp=0,'P','R'), 'ppn', L_SalesDetailPPN, 'ppn_amount', L_SalesDetailPPNAmount, 'total', L_SalesDetailTotal, 
                'subtotal', L_SalesDetailQty * ((L_SalesDetailPrice * (100 - L_SalesDetailDisc) / 100) - L_SalesDetailDiscRp), 
                'netto', L_SalesDetailSubtotal, 'detail_id', L_SalesDetailID)), ']') details,
                L_SalesM_DeliveryAddressID address_id, L_SalesM_PaymentPlanID payment_id, L_SalesM_TermID term_id,
                L_SalesM_ExpeditionID expedition_id, L_SalesL_InvoiceID sales_invoice,
                L_SalesM_AffiliateID affiliate_id, L_SalesAffiliateFee affiliate_fee, IFNULL(M_AffiliateName, '') affiliate_name

                FROM `{$this->table_name}`
                JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID
                    LEFT JOIN l_salesdetail ON L_SalesDetailL_SalesID=L_SalesID and L_SalesDetailIsActive = 'Y'
                    LEFT JOIN m_item ON M_ItemID = L_SalesDetailA_ItemID
                LEFT JOIN s_staff ON L_SalesS_StaffID = S_StaffID
                LEFT JOIN m_affiliate ON L_SalesM_AffiliateID = M_AffiliateID
                JOIN l_offer ON L_SalesL_OfferID = L_OfferID
                WHERE L_SalesID = ?
                AND `L_SalesIsActive` = 'Y'
                GROUP BY L_SalesID", [$id]);
        if ($r)
        {
            $r = $r->row();
            $this->load->model('inventory/i_stock');
               
            $details = json_decode($r->details);
            foreach ($details as $m => $w)
            {
                $stocks = $this->i_stock->search_by_item($w->item->item_id);
                $stock = 0;
                foreach ($stocks as $n => $z) $stock += $z['stock_qty'];

                $details[$m]->item->stocks = $stocks;
                $details[$m]->item->stock = $stock;

                // $details[$m]->stock = $this->i_stock->search_by_item($w->item);
            }
            $r->details = $details; //json_decode($v['details']);
        }
            
        return $r;
    }
}

?>