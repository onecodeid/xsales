<?php

class An_inventory extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "l_delivery";
        $this->table_key = "L_DeliveryID";
    }

    function pareto ( $sdate, $edate, $warehouse = 0, $orderby = 'omzet_freq desc' )
    {
        $l = [];
        $diff = round ((strtotime($edate) - strtotime($sdate)) / (60 * 60 * 24)) + 1;

        // RECORDS
        $q = "SELECT M_WarehouseID warehouse_id, IF(? = 0, 'Semua Gudang', M_WarehouseName) warehouse_name,
                M_ItemID item_id, M_ItemName item_name, M_ItemCode item_code, stock_min as item_min_stock,
                COUNT(DISTINCT L_DeliveryID) omzet_freq, SUM(L_DeliveryDetailQty) omzet_qty,
                SUM(L_DeliveryDetailQty) / {$diff} omzet_av,
                (SUM(L_DeliveryDetailQty) / {$diff}) * 30 as recapt_3month_av,
                -- An_RecaptMonth3MonthAv recapt_3month_av, 
                IFNULL(stock_qty, 0) stock_qty,
                An_RecaptFreqMonth recapt_freq_month, An_RecaptFreq2Month recapt_freq_2month,
                An_RecaptFreqStatus recapt_freq_status
                FROM l_deliverydetail
                JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
                    AND L_DeliveryDate BETWEEN ? AND ?
                    AND L_DeliveryConfirm = 'Y'
                    AND L_DeliveryIsActive = 'Y'
                JOIN m_warehouse ON L_DeliveryM_WarehouseID = M_WarehouseID
                JOIN m_item ON L_DeliveryDetailA_ItemID = M_ItemID
                LEFT JOIN an_recapt_month ON An_RecaptMonthM_ItemID = M_ItemID
                    AND An_RecaptMonthM_WarehouseID = M_WarehouseID
                LEFT JOIN an_recapt_freq ON An_RecaptFreqM_ItemID = M_ItemID
                    AND An_RecaptFreqM_WarehouseID = M_WarehouseID

                LEFT JOIN (
                    SELECT SUM(I_StockQty) stock_qty, I_StockM_ItemID stock_item_id,
                        SUM(I_StockMinQty) stock_min
                    FROM i_stock
                    WHERE ((I_StockM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                        AND I_StockIsActive = 'Y'
                    GROUP BY I_StockM_ItemID
                ) stock ON stock.stock_item_id = M_ItemID

                WHERE L_DeliveryDetailIsActive = 'Y'
                    AND ((L_DeliveryM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                GROUP BY L_DeliveryDetailA_ItemID
                ORDER BY {$orderby}";

        $r = $this->db->query( $q, [$warehouse, $sdate, $edate, 
                        $warehouse, $warehouse, $warehouse,
                        $warehouse, $warehouse, $warehouse] )
                    ->result_array();
        $l['records'] = $r;

        // COUNT
        $q = "SELECT COUNT(item_id) cnt FROM (
                SELECT L_DeliveryDetailA_ItemID item_id
                FROM l_deliverydetail
                JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
                    AND L_DeliveryDate BETWEEN ? AND ?
                    AND L_DeliveryConfirm = 'Y'
                    AND L_DeliveryIsActive = 'Y'
                WHERE L_DeliveryDetailIsActive = 'Y'
                    AND ((L_DeliveryM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                GROUP BY L_DeliveryDetailA_ItemID) x";

        $r = $this->db->query( $q, [$sdate, $edate, $warehouse, $warehouse, $warehouse] )
                    ->row();
        $l['total'] = $r->cnt;

        // RETURN
        return $l;
    }

    function recapt_daily ( $sdate, $edate, $wh )
    {
        $l = [];

        // RECORDS
        $q = "SELECT M_CustomerID customer_id, M_CustomerName customer_name,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('item_id', M_ItemId, 'item_name', M_ItemName, 'unit_id', IFNULL(M_UnitID, 0), 'unit_name', IFNULL(M_UnitName, ''),
                    'item_qty', L_DeliveryDetailQty)), ']') items,
                S_StaffID staff_id, S_StaffShortName staff_name,
                L_DeliveryDate invoice_date, L_DeliveryID invoice_id, IFNULL(M_ExpeditionID, 0) expedition_id, IFNULL(M_ExpeditionName, 'Tanpa Ekspedisi') expedition_name,
                IFNULL(M_DeliveryTypeName, '') delivery_name, IFNULL(M_TermName, '') payment_name

            FROM l_deliverydetail
            JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID AND L_DeliveryDate BETWEEN ? AND ?
            JOIN m_customer ON L_DeliveryM_CustomerID = M_CustomerID

            JOIN l_salesdetail ON L_SalesDetailID = L_DeliveryDetailL_SalesDetailID
            JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
            LEFT JOIN s_staff ON L_SalesS_StaffID = S_StaffID
            LEFT JOIN m_expedition ON L_SalesM_ExpeditionID = M_ExpeditionID
            LEFT JOIN m_deliverytype ON L_DeliveryM_DeliveryTypeID = M_DeliveryTypeID
            LEFT JOIN m_paymentplan ON L_SalesM_PaymentPlanID = M_PaymentPlanID
            LEFT JOIN m_term ON L_SalesM_TermID = M_TermID

            JOIN m_item ON L_DeliveryDetailA_ItemID = M_ItemID
            LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
            
            WHERE L_DeliveryDetailIsActive = 'Y'
                AND ((L_DeliveryM_WarehouseID = ? AND ? <> 0) OR ? = 0)
            GROUP BY L_DeliveryID";

        $r = $this->db->query( $q, [$sdate, $edate, $wh, $wh, $wh] )->result_array();
        foreach ($r as $k => $v)
        {
            $r[$k]['items'] = json_decode($v['items']);
            $r[$k]['rowspan'] = sizeof($r[$k]['items']);
        }

        $l['records'] = $r;

        // COUNT
        $q = "SELECT COUNT(ivid) cnt FROM (
            SELECT L_DeliveryID ivid
            FROM l_deliverydetail
            JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID AND L_DeliveryDate BETWEEN ? AND ?
            JOIN m_customer ON L_DeliveryM_CustomerID = M_CustomerID
            WHERE L_DeliveryDetailIsActive = 'Y'
                AND ((L_DeliveryM_WarehouseID = ? AND ? <> 0) OR ? = 0)
            GROUP BY L_DeliveryID) x";

        $r = $this->db->query( $q, [$sdate, $edate, $wh, $wh, $wh] )->row();
        $l['total'] = $r->cnt;

        // RETURN
        return $l;
    }

    function omzet_category($sdate, $edate, $category = 0)
    {
        $q = "SELECT invoice_id, invoice_date, invoice_number,

                staff_id, staff_name, customer_id, customer_name,
                item_id, item_code, item_name, sum(item_qty) item_qty, min(item_price) min_item_price, max(item_price) max_item_price, 
                item_disc,
                sum((item_subtotal * item_disctotal / 100)) as  item_disctotal, 
                sum(item_subtotal * (100-item_disctotal)/100) as item_subtotal, 
                item_incppn, 
                item_ppn,
                
                sum( IF(item_incppn = 'N',
                item_subtotal * (100-item_disctotal) * invoice_ppnvalue / (100), 
                item_subtotal * (100-item_disctotal) * invoice_ppnvalue / (1 + invoice_ppnvalue) ) )
                as item_ppnamount,

                -- group_concat(IF(item_incppn = 'N',
                -- item_subtotal * (100-item_disctotal) * invoice_ppnvalue / (100), 
                -- item_subtotal * (100-item_disctotal) * invoice_ppnvalue / (1 + invoice_ppnvalue) )) xxx,
                
                sum( (item_subtotal * (100-item_disctotal)/100) + IF(item_incppn = 'N',
                item_subtotal * (100-item_disctotal) * invoice_ppnvalue / (100), 0 ) ) item_total,
                
                -- sum(item_total) item_total, 
                unit_name, category_id, category_name
                FROM (SELECT L_InvoiceID invoice_id, L_InvoiceDate invoice_date, L_InvoiceNumber invoice_number,
                    -- L_InvoiceSubTotal invoice_subtotal, L_InvoiceTotal invoice_total, L_InvoiceNote invoice_note,
                    L_InvoicePPNValue / 100 invoice_ppnvalue,
                    S_StaffID staff_id, S_StaffName staff_name, 
                    M_CustomerID customer_id, M_customerName customer_name,
                    M_ItemID item_id, M_ItemName item_name, M_ItemCode item_code,
                    L_InvoiceDetailQty item_qty,
                    L_InvoiceDetailPrice item_price,
                    -- L_InvoiceDetailDisc item_disc,	
                    -- L_InvoiceDetailDiscRp item_discrp,
                    (L_InvoiceDetailPrice * L_InvoiceDetailDisc / 100) + L_InvoiceDetailDiscRp item_disc,
                    L_InvoiceDiscountTotal item_disctotal,
                    ((L_InvoiceDetailPrice * (100-L_InvoiceDetailDisc) / 100) - L_InvoiceDetailDiscRp) * L_InvoiceDetailQty item_subtotal,
                    -- L_InvoiceDetailSubTotal item_subtotal,
                    L_InvoiceDetailIncludePPN item_incppn,
                    L_InvoiceDetailPPN item_ppn,
                    L_InvoiceDetailPPNAmount item_ppnamount,
                    L_InvoiceDetailTotal item_total,
                    IFNULL(M_UnitName, '') unit_name, IFNULL(M_CategoryID, 0) category_id, IFNULL(M_CategoryName, '') category_name
                    FROM l_invoicedetail
                    JOIN l_invoice ON L_InvoiceDetailL_InvoiceID = L_InvoiceID
                    AND L_InvoiceDate BETWEEN ? AND ?
                    JOIN m_item ON L_InvoiceDetailA_ItemID = M_ItemID
                        AND ((M_ItemM_CategoryID = ? AND ? <> 0) OR ? = 0)
                    LEFT JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
                    LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                    
                    -- JOIN s_staff ON ((L_InvoiceS_StaffID = S_StaffID AND pstaff <> 0) OR pstaff = 0)
                    JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID
                    
                    JOIN l_delivery ON L_DeliveryL_InvoiceID = L_InvoiceID AND L_DeliveryIsActive = 'Y'
                    JOIN l_deliverydetail ON L_DeliveryDetailL_DeliveryID = L_DeliveryID AND L_DeliveryDetailIsActive = 'Y'
                        AND L_DeliveryDetailA_ItemID = L_InvoiceDetailA_ItemID
                    JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
                    JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
                        -- AND ((L_SalesS_StaffID = pstaff AND pstaff <> 0) OR pstaff = 0)
                    LEFT JOIN s_staff ON (L_SalesS_StaffID = S_StaffID)
                    WHERE L_InvoiceDetailIsActive = 'Y'
                        -- AND ((L_InvoiceDetailA_ItemID = pitem AND pitem <> 0) OR pitem = 0)
                    ORDER BY M_ItemName) x
                    GROUP BY item_id
                    ORDER BY item_name";

        $r = $this->db->query( $q, [$sdate, $edate, $category, $category, $category] )->result_array();
        // foreach ($r as $k => $v)
        // {
        //     $r[$k]['items'] = json_decode($v['items']);
        //     $r[$k]['rowspan'] = sizeof($r[$k]['items']);
        // }

        $l['records'] = $r;
        $l['total'] = sizeof($r);

        // COUNT
        // $q = "SELECT COUNT(ivid) cnt FROM (
        //     SELECT L_DeliveryID ivid
        //     FROM l_deliverydetail
        //     JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID AND L_DeliveryDate BETWEEN ? AND ?
        //     JOIN m_customer ON L_DeliveryM_CustomerID = M_CustomerID
        //     WHERE L_DeliveryDetailIsActive = 'Y'
        //     GROUP BY L_DeliveryID) x";

        // $r = $this->db->query( $q, [$sdate, $edate] )->row();
        // $l['total'] = $r->cnt;

        // RETURN
        return $l;

// SELECT invoice_id, invoice_date, invoice_number,

// staff_id, staff_name, customer_id, customer_name,
// item_id, item_name, item_qty, item_price, 
// item_disc,
// (item_subtotal * item_disctotal / 100) as  item_disctotal, 
// item_subtotal * (100-item_disctotal)/100 as item_subtotal, 
// item_incppn, 
// item_ppn,

// IF(item_incppn = "N",
// item_subtotal * (100-item_disctotal) * invoice_ppnvalue / (100), 
// item_subtotal * (100-item_disctotal) * invoice_ppnvalue / (1 + invoice_ppnvalue) ) 
// as item_ppnamount,

// item_total, unit_name
// FROM (
// SELECT L_InvoiceID invoice_id, L_InvoiceDate invoice_date, L_InvoiceNumber invoice_number,
//     -- L_InvoiceSubTotal invoice_subtotal, L_InvoiceTotal invoice_total, L_InvoiceNote invoice_note,
//     L_InvoicePPNValue / 100 invoice_ppnvalue,
//     S_StaffID staff_id, S_StaffName staff_name, 
//     M_CustomerID customer_id, M_customerName customer_name,
// M_ItemID item_id, M_ItemName item_name, L_InvoiceDetailQty item_qty,
// L_InvoiceDetailPrice item_price,
// -- L_InvoiceDetailDisc item_disc,	
// -- L_InvoiceDetailDiscRp item_discrp,
// (L_InvoiceDetailPrice * L_InvoiceDetailDisc / 100) + L_InvoiceDetailDiscRp item_disc,
// L_InvoiceDiscountTotal item_disctotal,
// ((L_InvoiceDetailPrice * (100-L_InvoiceDetailDisc) / 100) - L_InvoiceDetailDiscRp) * L_InvoiceDetailQty item_subtotal,
// -- L_InvoiceDetailSubTotal item_subtotal,
// L_InvoiceDetailIncludePPN item_incppn,
// L_InvoiceDetailPPN item_ppn,
// L_InvoiceDetailPPNAmount item_ppnamount,
// L_InvoiceDetailTotal item_total,
// IFNULL(M_UnitName, '') unit_name
// FROM l_invoicedetail
// JOIN l_invoice ON L_InvoiceDetailL_InvoiceID = L_InvoiceID
//   AND L_InvoiceDate BETWEEN sdate AND edate
// JOIN m_item ON L_InvoiceDetailA_ItemID = M_ItemID
// LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID

// -- JOIN s_staff ON ((L_InvoiceS_StaffID = S_StaffID AND pstaff <> 0) OR pstaff = 0)
// JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID

// JOIN l_delivery ON L_DeliveryL_InvoiceID = L_InvoiceID AND L_DeliveryIsActive = "Y"
// JOIN l_deliverydetail ON L_DeliveryDetailL_DeliveryID = L_DeliveryID AND L_DeliveryDetailIsActive = "Y"
// JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
// JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
//     -- AND ((L_SalesS_StaffID = pstaff AND pstaff <> 0) OR pstaff = 0)
// LEFT JOIN s_staff ON (L_SalesS_StaffID = S_StaffID)
// WHERE L_InvoiceDetailIsActive = "Y"
//     AND ((L_InvoiceDetailA_ItemID = pitem AND pitem <> 0) OR pitem = 0)
// ORDER BY L_InvoiceDate, M_ItemName) xxx ;

// -- L_InvoiceDate	date NULL	
// -- L_InvoiceM_TermID	int(11) [0]	
// -- L_InvoiceDueDate	date NULL	
// -- L_InvoiceNumber	varchar(25) NULL	
// -- L_InvoiceProforma	char(1) [N]	
// -- L_InvoiceP_ReceiveID	int(11) [0]	
// -- L_InvoiceM_CustomerID	int(11) [0]	
// -- L_InvoiceSubTotal	double [0]	
// -- L_InvoiceTotal	double [0]	
// -- L_InvoiceDiscount	double [0]	
// -- L_InvoiceDiscountRp	double [0]	
// -- L_InvoicePPN	double [0]	
// -- L_InvoiceShipping	double [0]	
// -- L_InvoiceGrandTotal	double [0]	
// -- L_InvoiceDP	double [0]	
// -- L_InvoicePaid	double [0]	
// -- L_InvoiceUnpaid	double [0]	
// -- L_InvoiceLunas	char(1) [N]	
// -- L_InvoiceNote	varchar(255) NULL	
// -- L_InvoiceT_JournalID	int(11) [0]	
// -- L_InvoiceS_StaffID	int(11) [0]	
// -- L_InvoiceUserID	

// END

    }
}

// L_DeliveryID	int(11) Inkrementasi Otomatis	
// L_DeliveryDate	date NULL	
// L_DeliveryNumber	varchar(25) NULL	
// L_DeliveryRefNumber	varchar(50) NULL	
// L_DeliveryProforma	char(1) [N]	
// L_DeliveryM_CustomerID	int(11) [0]	
// L_DeliveryM_WarehouseID	int(11) [0]	
// L_DeliveryM_DeliveryAddressID	int(11) [0]	
// L_DeliveryNote	varchar(1000) NULL	
// L_DeliveryMemo	varchar(1000) NULL	
// L_DeliveryTotalQty	double [0]	
// L_DeliveryConfirm	char(1) [N]	
// L_DeliveryL_InvoiceID	int(11) [0]	
// L_DeliveryM_DeliveryTypeID	int(11) [1]	
// L_DeliverySendNote	varchar(100) NULL	
// L_DeliveryS_StaffID	int(11) [0]	
// L_DeliveryIsActive	char(1) [Y]	
// L_DeliveryCreated	datetime [CURRENT_TIMESTAMP]	
// L_DeliveryLastUpdated

// L_InvoiceDetailID	int(11) Inkrementasi Otomatis	
// L_InvoiceDetailL_InvoiceID	int(11) [0]	
// L_InvoiceDetailL_DeliveryID	int(11) [0]	
// L_InvoiceDetailA_ItemID	int(11) [0]	
// L_InvoiceDetailQty	double [0]	
// L_InvoiceDetailPrice	double [0]	
// L_InvoiceDetailDisc	double [0]	
// L_InvoiceDetailDiscRp	double [0]	
// L_InvoiceDetailSubTotal	double [0]	
// L_InvoiceDetailIncludePPN	char(1) [N]	
// L_InvoiceDetailPPN	char(1) [N]	
// L_InvoiceDetailPPNAmount	double [0]	
// L_InvoiceDetailTotal	double [0]	
// L_InvoiceDetailIsActive	char(1) [Y]	
// L_InvoiceDetailCreated	datetime [CURRENT_TIMESTAMP]	
// L_InvoiceDetailLastUpdate