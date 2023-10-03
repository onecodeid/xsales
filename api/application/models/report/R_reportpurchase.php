<?php

class R_reportpurchase extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_bill";
        $this->table_key = "F_BillID";
        $this->load->model("master/m_vendor");
    }

    function purchase_002( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $vendor = isset($d['vendor_id'])?$d['vendor_id']:0;

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");

        $q = "SELECT 
                CONCAT('[', GROUP_CONCAT(
                    JSON_OBJECT('bill_id', F_BillID, 'bill_number', F_BillNumber, 'bill_date', F_BillDate, 'bill_due_date', F_BillDueDate, 
                    'bill_diff_date', datediff(now(), F_BillDueDate), 'bill_subtotal', F_BillSubTotal, 'bill_total', F_BillTotal, 'bill_grand_total', F_BillGrandTotal, 'bill_paid', F_BillPaid, 'bill_unpaid', F_BillUnpaid,
                    'bill_note', IFNULL(F_BillNote, ''), 'bill_memo', '', 'bill_lunas', F_BillLunas, 'bill_term', F_BillM_TermID, 'bill_dp', F_BillDp, 'bill_shipping', 0, 'bill_proforma', 'N', 'bill_disc', F_BillDiscount, 'bill_discrp', F_BillDiscountRp, 'bill_disctotal', F_BillDiscountTotalRp, 'bill_ppn', F_BillPPN, 'bill_ppnvalue', F_BillPPNValue,
                    'term_id', M_TermID, 'term_duration', M_TermDuration,
                    'journal_id', IFNULL(T_JournalID, 0),
                    'journal_date', IFNULL(T_JournalDate, '0000-00-00'),
                    'journal_note', IFNULL(T_JournalNote, ''),
                    'journal_receipt', IFNULL(T_JournalReceipt, '') )), ']') bills,
                    SUM(F_BillSubTotal) bill_subtotal, SUM(F_BillDiscountTotalRp) bill_disctotal, SUM(F_BillPPN) bill_ppn, SUM(F_BillGrandTotal) bill_grandtotal,
                    0 bill_shipping,

                    SUM(F_BillUnpaid) total_unpaid,
                    SUM(IF(datediff(now(), F_BillDueDate) <= 0, F_BillUnpaid, 0)) total_ongoing,
                    SUM(IF(datediff(now(), F_BillDueDate) BETWEEN 1 AND 30, F_BillUnpaid, 0)) total_30,
                    SUM(IF(datediff(now(), F_BillDueDate) BETWEEN 31 AND 60, F_BillUnpaid, 0)) total_60,
                    SUM(IF(datediff(now(), F_BillDueDate) BETWEEN 61 AND 90, F_BillUnpaid, 0)) total_90,
                    SUM(IF(datediff(now(), F_BillDueDate) BETWEEN 91 AND 120, F_BillUnpaid, 0)) total_120,
                    SUM(IF(datediff(now(), F_BillDueDate) > 120, F_BillUnpaid, 0)) total_rest,
                
                M_VendorID vendor_id, M_VendorName vendor_name, 
                M_VendorID vendor_id, 

                 '' delivery_memos

            FROM f_bill
            JOIN m_vendor ON F_BillM_VendorID = M_vendorID 
            JOIN m_term ON F_BillM_TermID = M_TermID
            LEFT JOIN t_journal ON F_BillT_JournalID = T_JournalID
            WHERE (`F_BillNumber` LIKE ? OR M_VendorName LIKE ?)
                AND ((F_BillM_VendorID = ? AND ? > 0) OR ? = 0)
                AND F_BillDate BETWEEN ? AND ?
                AND F_BillIsActive = 'Y'
                AND F_BillUnpaid > 0
            GROUP BY M_VendorID
            ORDER BY M_VendorName, F_BillDate
            LIMIT {$limit} OFFSET {$offset}
            ";
        $r = $dbiv->query(
            $q, [$d['search'], $d['search'], $vendor, $vendor, $vendor, $sdate, $edate]);

        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $bills = json_decode($v['bills']);
                foreach ($bills as $l => $w)
                {
                    $w = (array) $w;
                    $details = $this->db->query("SELECT fn_finance_bill_detail(?) x", [$w['bill_id']])->row();
                    $accs = $this->db->query("SELECT fn_journal_detail(?) y", [$w['journal_id']])->row();
                    $dps = $this->db->query("SELECT fn_bill_dp(?) x", [$w['bill_id']])->row();

                    // GET SALES
                    // $sales = json_decode($this->db->query("SELECT fn_sales_bill_sales(?) x", [$w['bill_id']])->row()->x);

                    $bills[$l]->details = json_decode($details->x);
                    $bills[$l]->accounts = json_decode($accs->y);
                    $bills[$l]->bill_dps = json_decode($dps->x);
                    // $bills[$l]->sales = $sales;
                }

                $r[$k]['bills'] = $bills;

                // MAIN ADDRESS
                // $r[$k]['main_address'] = $this->m_vendor->get_main_address($v['vendor_id']);
            }
            $lx['records'] = $r;
        }

        // COUNT
        $r = $dbiv->query(
            "SELECT count(DISTINCT `F_BillM_VendorID`) n
            FROM `{$this->table_name}`
            JOIN m_vendor ON F_BillM_VendorID = M_VendorID
            
            WHERE (`F_BillNumber` LIKE ? OR M_VendorName LIKE ?)
            AND ((F_BillM_VendorID = ? AND ? > 0) OR ? = 0)
            AND F_BillDate BETWEEN ? AND ?
            AND `F_BillIsActive` = 'Y'
            AND F_BillUnpaid > 0", [$d['search'], $d['search'], $vendor, $vendor, $vendor, $sdate, $edate]);
        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }

        // GRAND TOTAL
        $q = "SELECT 
                IFNULL(SUM(F_BillUnpaid), 0) total_unpaid,
                IFNULL(SUM(IF(datediff(now(), F_BillDueDate) <= 0, F_BillUnpaid, 0)), 0) total_ongoing,
                IFNULL(SUM(IF(datediff(now(), F_BillDueDate) BETWEEN 1 AND 30, F_BillUnpaid, 0)), 0) total_30,
                IFNULL(SUM(IF(datediff(now(), F_BillDueDate) BETWEEN 31 AND 60, F_BillUnpaid, 0)), 0) total_60,
                IFNULL(SUM(IF(datediff(now(), F_BillDueDate) BETWEEN 61 AND 90, F_BillUnpaid, 0)), 0) total_90,
                IFNULL(SUM(IF(datediff(now(), F_BillDueDate) BETWEEN 91 AND 120, F_BillUnpaid, 0)), 0) total_120,
                IFNULL(SUM(IF(datediff(now(), F_BillDueDate) > 120, F_BillUnpaid, 0)), 0) total_rest
            FROM f_bill
            JOIN m_vendor ON F_BillM_VendorID = M_VendorID
            WHERE (`F_BillNumber` LIKE ? OR M_VendorName LIKE ?)
                AND ((F_BillM_VendorID = ? AND ? > 0) OR ? = 0)
                AND F_BillDate BETWEEN ? AND ?
                AND F_BillIsActive = 'Y'
                AND F_BillUnpaid > 0
            ";
        $r = $dbiv->query(
            $q, [$d['search'], $d['search'], $vendor, $vendor, $vendor, $sdate, $edate]);
        if ($r)
        {
            $r = $r->row();
            $lx['grand_total'] = $r;
        }
            
        return $lx;
    }

    function purchase_003 ($d)
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $vendor = isset($d['vendor_id'])?$d['vendor_id']:0;

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        
        $q = "SELECT 
                CONCAT('[', GROUP_CONCAT(
                    JSON_OBJECT('bill_id', F_BillID, 'bill_number', F_BillNumber, 'bill_date', F_BillDate, 'bill_due_date', F_BillDueDate, 'bill_subtotal', F_BillSubTotal, 'bill_total', F_BillTotal, 'bill_grand_total', F_BillGrandTotal, 'bill_paid', F_BillPaid, 'bill_unpaid', F_BillUnpaid,
                    'bill_note', IFNULL(F_BillNote, ''), 'bill_memo', '', 'bill_lunas', F_BillLunas, 'bill_term', F_BillM_TermID, 'bill_dp', 0, 'bill_shipping', 0, 'bill_proforma', 'N', 'bill_disc', F_BillDiscount, 'bill_discrp', F_BillDiscountRp, 'bill_disctotal', F_BillDiscountTotalRp, 'bill_ppn', F_BillPPN, 'bill_ppnvalue', F_BillPPNValue,
                    'journal_id', IFNULL(T_JournalID, 0),
                    'journal_date', IFNULL(T_JournalDate, '0000-00-00'),
                    'journal_note', IFNULL(T_JournalNote, ''),
                    'journal_receipt', IFNULL(T_JournalReceipt, ''),
                    'term_id', IFNULL(M_TermID, 0), 'term_name', IFNULL(M_TermName, '') )), ']') bills,
                    SUM(F_BillSubTotal) bill_subtotal, SUM(F_BillDiscountTotalRp) bill_disctotal, SUM(F_BillPPN) bill_ppn, SUM(F_BillGrandTotal) bill_grandtotal,
                    0 bill_shipping,
                    M_VendorID vendor_id, M_VendorName vendor_name, 
                    M_VendorID vendor_id, 
                    '' delivery_memos

            FROM m_vendor
            JOIN f_bill ON F_BillM_VendorID = M_vendorID AND F_BillIsActive = 'Y'
            LEFT JOIN t_journal ON F_BillT_JournalID = T_JournalID
            LEFT JOIN m_term ON F_BillM_TermID = M_TermID
            WHERE (`F_BillNumber` LIKE ? OR M_VendorName LIKE ?)
                AND ((F_BillM_VendorID = ? AND ? > 0) OR ? = 0)
                AND F_BillDate BETWEEN ? AND ?
            GROUP BY M_VendorID
            ORDER BY M_VendorName, F_BillDate
            LIMIT {$limit} OFFSET {$offset}
            ";
        $r = $dbiv->query(
                $q, [$d['search'], $d['search'], $vendor, $vendor, $vendor, $sdate, $edate]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $bills = json_decode($v['bills']);
                foreach ($bills as $l => $w)
                {
                    $w = (array) $w;
                    $details = $this->db->query("SELECT fn_finance_bill_detail(?) x", [$w['bill_id']])->row();
                    $accs = $this->db->query("SELECT fn_journal_detail(?) y", [$w['journal_id']])->row();
                    $dps = $this->db->query("SELECT fn_bill_dp(?) x", [$w['bill_id']])->row();
                    
                    $bills[$l]->details = json_decode($details->x);
                    $bills[$l]->accounts = json_decode($accs->y);
                    $bills[$l]->bill_dps = json_decode($dps->x);
                  
                }

                $r[$k]['bills'] = $bills;   
            }
            $lx['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(DISTINCT `F_BillM_VendorID`) n
            FROM `{$this->table_name}`
            JOIN m_vendor ON F_BillM_VendorID = M_VendorID
            
            WHERE (`F_BillNumber` LIKE ? OR M_VendorName LIKE ?)
            AND ((F_BillM_VendorID = ? AND ? > 0) OR ? = 0)
            AND F_BillDate BETWEEN ? AND ?
            AND `F_BillIsActive` = 'Y'", [$d['search'], $d['search'], $vendor, $vendor, $vendor, $sdate, $edate]);
        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $lx;
    }    

    function purchase_004 ($d)
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $vendor = isset($d['vendor_id'])?$d['vendor_id']:0;

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        
        $q = "SELECT 
                M_VendorID vendor_id, M_VendorName vendor_name, SUM(P_PurchaseDetailTotal) total,
                CONCAT('[', GROUP_CONCAT(
                JSON_OBJECT('receive_id', P_ReceiveID, 'receive_date', P_ReceiveDate, 'receive_number', P_ReceiveSecondaryNumber,
                'warehouse_id', M_WarehouseID,
                'warehouse_name', M_WarehouseName, 'receive_note', P_ReceiveNote, 'receive_memo', P_ReceiveMemo,
                'detail_id', P_ReceiveDetailID,
                'item_id', M_ItemID, 'item_name', M_ItemName, 'item_code', M_ItemCode,
                'detail_qty', P_ReceiveDetailQty, 'detail_price', P_PurchaseDetailPrice, 'detail_disc', P_PurchaseDetailDisc,
                'detail_discrp', P_PurchaseDetailDiscRp, 'detail_subtotal', P_PurchaseDetailSubTotal,
                'detail_ppn', P_PurchaseDetailPPN, 'detail_ppnamount', P_PurchaseDetailPPNAmount,
                'detail_total', P_PurchaseDetailTotal,
                'detail_note', IFNULL(P_ReceiveDetailNote, ''),
                'unit_id', IFNULL(M_UnitID, 0), 'unit_name', IFNULL(M_UnitName, ''),
                'sales_name', IFNULL(S_StaffName, '') ) SEPARATOR ','), ']') details

            FROM p_receivedetail
            JOIN p_receive ON P_ReceiveID = P_ReceiveDetailP_ReceiveID
                AND P_ReceiveIsActive = 'Y'
                AND P_ReceiveDate BETWEEN ? AND ?
            JOIN p_purchasedetail ON P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
            JOIN p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID
            JOIN m_vendor ON P_ReceiveM_VendorID = m_vendorID
            JOIN m_warehouse ON P_ReceiveM_WarehouseID = M_WarehouseID
            JOIN m_item ON P_ReceiveDetailA_ItemID = M_ItemID
            LEFT JOIN m_unit ON M_UnitID = M_ItemM_UnitID
            LEFT JOIN s_staff ON P_PurchaseS_StaffID = S_StaffID

            WHERE P_ReceiveDetailIsActive = 'Y'
            AND (`P_ReceiveNumber` LIKE ? OR M_VendorName LIKE ? OR M_ItemName LIKE ?)

            GROUP BY M_VendorID
            ORDER BY M_VendorName ASC, M_ItemName ASC, P_ReceiveDate ASC
            LIMIT {$limit} OFFSET {$offset}
            ";
        $r = $dbiv->query(
                $q, [$sdate, $edate, $d['search'], $d['search'], $d['search']]); //, $vendor, $vendor, $vendor]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $details = json_decode($v['details']);
                // $ndetails = [];
                // $n = 0;
                // foreach ($details as $l => $w)
                // {
                //     if ($l == 0) 
                //     {   
                //         $ndetails[] = $w; $n++;
                //     }
                //     else if ($w->item_id != $details[$l-1]->item_id)
                //     {
                //         $ndetails[] = $w; $n++;
                //     }
                //     else
                //     {
                //         $ndetails[$n-1]->detail_qty += $w->detail_qty;
                //         $ndetails[$n-1]->detail_total += $w->detail_total;
                //     }
                // }

                // $r[$k]['details'] = $ndetails;
                $r[$k]['details'] = $details;

                // $bills = json_decode($v['bills']);
                // foreach ($bills as $l => $w)
                // {
                //     $w = (array) $w;
                //     $details = $this->db->query("SELECT fn_finance_bill_detail(?) x", [$w['bill_id']])->row();
                //     $accs = $this->db->query("SELECT fn_journal_detail(?) y", [$w['journal_id']])->row();
                //     $dps = $this->db->query("SELECT fn_bill_dp(?) x", [$w['bill_id']])->row();
                    
                //     $bills[$l]->details = json_decode($details->x);
                //     $bills[$l]->accounts = json_decode($accs->y);
                //     $bills[$l]->bill_dps = json_decode($dps->x);
                  
                // }

                // $r[$k]['bills'] = $bills;   
            }
            $lx['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(DISTINCT `P_ReceiveM_VendorID`) n
            FROM p_receivedetail
            JOIN p_receive ON P_ReceiveID = P_ReceiveDetailP_ReceiveID
                AND P_ReceiveIsActive = 'Y'
                AND P_ReceiveDate BETWEEN ? AND ?
            JOIN m_vendor ON P_ReceiveM_VendorID = m_vendorID
            JOIN m_warehouse ON P_ReceiveM_WarehouseID = M_WarehouseID
            JOIN m_item ON P_ReceiveDetailA_ItemID = M_ItemID
            
            WHERE P_ReceiveDetailIsActive = 'Y'
            AND (`P_ReceiveNumber` LIKE ? OR M_VendorName LIKE ? OR M_ItemName LIKE ?)

            ", [$sdate, $edate, $d['search'], $d['search'], $d['search']]); //, $vendor, $vendor, $vendor, $sdate, $edate]);
        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $lx;
    }
    
    function purchase_005( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $category = isset($d['category_id'])?$d['category_id']:0;
        $staff = isset($d['staff_id'])?$d['staff_id']:0;

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");

        $q = "SELECT 
                CONCAT('[', GROUP_CONCAT(
                    JSON_OBJECT('bill_id', F_BillID, 'bill_number', F_BillNumber, 'bill_date', F_BillDate, 'bill_due_date', F_BillDueDate, 'bill_subtotal', F_BillSubTotal, 'bill_total', F_BillTotal, 'bill_grand_total', F_BillGrandTotal, 'bill_paid', F_BillPaid, 'bill_unpaid', F_BillUnpaid,
                    'bill_note', IFNULL(F_BillNote, ''), 'bill_memo', '', 'bill_lunas', F_BillLunas, 'bill_term', F_BillM_TermID, 'bill_dp', F_BillDp, 'bill_shipping', 0, 'bill_proforma', 'N', 'bill_disc', F_BillDiscount, 'bill_discrp', F_BillDiscountRp, 'bill_disctotal', F_BillDiscountTotalRp, 'bill_ppn', F_BillPPN, 'bill_ppnvalue', F_BillPPNValue,
                    'detail_qty', F_BillDetailQty, 'detail_price', F_BillDetailPrice, 'detail_disc', F_BillDetailDisc,
                    'detail_discrp', F_BillDetailDiscRp, 'detail_subtotal', F_BillDetailSubTotal, 'detail_ppn', F_BillDetailPPN,
                    'detail_ppnamount', F_BillDetailPPNAmount, 'detail_total', F_BillDetailTotal,
                    'detail_disctotal', ((F_BillDetailDisc * F_BillDetailPrice / 100) + F_BillDetailDiscRp),
                    'unit_id', M_UnitID, 'unit_name', M_UnitName,
                    'vendor_id', M_VendorID, 'vendor_code', M_VendorCode, 'vendor_name', M_VendorName, 'sales_id', S_StaffID, 'sales_name', S_StaffName,
                    'retur_qty', IFNULL(retur_qty, 0), 'retur_nominal', IFNULL(retur_nominal, 0)
                     )), ']') bills,
                     SUM(F_BillDetailQty) detail_qty, SUM(F_BillDetailDiscRp) detail_discrp, SUM(F_BillDetailSubTotal) detail_subtotal, 
                     SUM(F_BillDetailPPNAmount) detail_ppnamount, SUM(F_BillDetailTotal) detail_total,
                     SUM((F_BillDetailDisc * F_BillDetailPrice / 100) + F_BillDetailDiscRp) detail_disctotal,
                     IFNULL(SUM(retur_qty), 0) retur_qty, IFNULL(SUM(retur_nominal), 0) retur_nominal,

                M_CategoryID category_id, M_CategoryName category_name,
                M_ItemID item_id, M_ItemCode item_code, M_ItemName item_name, M_UnitID unit_id, M_UnitName unit_name,
                S_StaffID sales_id, S_StaffName sales_name

            FROM f_bill
            JOIN f_billdetail ON F_BillDetailF_BillID = F_BillID 
            JOIN m_item ON F_BillDetailA_ItemID = M_ItemID
            JOIN m_unit ON M_ItemM_UnitID = M_UnitID
            JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
            JOIN s_staff ON F_BillS_StaffID = S_StaffID
            JOIN m_vendor ON F_BillM_VendorID = M_VendorID

            LEFT JOIN (
                SELECT F_BillID bill_id, SUM(P_ReturDetailQty) retur_qty, SUM(P_ReturDetailTotal) retur_nominal,
                    F_BillDetailID detail_id
                FROM p_returdetail
                JOIN p_retur ON P_ReturDetailP_ReturID = P_ReturID AND P_ReturIsActive = 'Y'
                JOIN f_billdetail ON P_ReturDetailF_BillDetailID = F_BillDetailID
                JOIN f_bill ON F_BillDetailF_BillID = F_BillID
                    AND F_BillDate BETWEEN ? AND ?
                WHERE P_ReturDetailIsActive = 'Y'
                GROUP BY F_BillDetailID
            ) retur ON F_BillDetailID = detail_id

            WHERE (`F_BillNumber` LIKE ? OR M_VendorName LIKE ? OR M_ItemName LIKE ?)
                AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)
                AND ((S_StaffID = ? AND ? > 0) OR ? = 0)
                AND F_BillDate BETWEEN ? AND ?
                AND F_BillIsActive = 'Y'
            GROUP BY M_ItemID
            ORDER BY M_CategoryName, M_ItemName, F_BillDate
            LIMIT {$limit} OFFSET {$offset}
            ";
        $r = $dbiv->query(
                $q, [$sdate, $edate, $d['search'], $d['search'], $d['search'], $category, $category, $category, $staff, $staff, $staff, $sdate, $edate]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $bills = json_decode($v['bills']);
                // foreach ($invoices as $l => $w)
                // {
                //     $w = (array) $w;
                // }

                $r[$k]['bills'] = $bills;

                // MAIN ADDRESS
                // $r[$k]['main_address'] = $this->m_customer->get_main_address($v['vendor_id']);
            }
                // $r[$k]['items'] = json_decode($v['items']);
            $lx['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(DISTINCT `M_ItemID`) n
            FROM f_bill
            JOIN f_billdetail ON F_BillDetailF_BillID = F_BillID 
            JOIN m_item ON F_BillDetailA_ItemID = M_ItemID
            JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
            JOIN s_staff ON F_BillS_StaffID = S_StaffID
            JOIN m_vendor ON F_BillM_VendorID = M_VendorID
            
            WHERE (`F_BillNumber` LIKE ? OR M_VendorName LIKE ? OR M_ItemName LIKE ?)
                AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)
                AND ((S_StaffID = ? AND ? > 0) OR ? = 0)
                AND F_BillDate BETWEEN ? AND ?
                AND F_BillIsActive = 'Y'", [$d['search'], $d['search'], $d['search'], $category, $category, $category, $staff, $staff, $staff, $sdate, $edate]);
        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $lx;
    }

    function purchase_006( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $staff = isset($d['staff_id'])?$d['staff_id']:0;
        $supplier = isset($d['supplier_id'])?$d['supplier_id']:0;
        $done = isset($d['done']) ? "AND P_PurchaseDone = '{$d['done']}'" : "";

        $r = $dbiv->query(
                "SELECT P_PurchaseID purchase_id, P_PurchaseNumber purchase_number, P_PurchaseDate purchase_date, P_PurchaseTotal purchase_total, P_PurchaseGrandTotal purchase_grandtotal, P_PurchaseDP purchase_dp,
                P_PurchaseShipping purchase_shipping, P_PurchaseDisc purchase_disc, P_PurchaseDiscRp purchase_discrp,
                0 purchase_paid, 0 purchase_unpaid,
                P_PurchaseDone purchase_done,
                P_PurchaseNote purchase_note, P_PurchaseMemo purchase_memo,
                P_PurchaseIncludePPN purchase_ppn, P_PurchasePPN purchase_ppn_amount,
                M_VendorName vendor_name, M_VendorID vendor_id,
                P_PurchaseM_PaymentPlanID purchase_payment, P_PurchaseM_TermID term_id, P_PurchaseS_StaffID purchase_staff,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('item', JSON_OBJECT('item_id', M_ItemID,'item_name', M_ItemName), 'price', P_PurchaseDetailPrice, 'qty', P_PurchaseDetailQty, 
                    'received', P_PurchaseDetailReceived, 'unreceived', P_PurchaseDetailUnReceived, 'unit', IFNULL(M_UnitName, ''), 'disc', P_PurchaseDetailDisc, 
                    'discrp', P_PurchaseDetailDiscRp, 'disctype', IF(P_PurchaseDetailDiscRp=0,'P','R'),'ppn', P_PurchaseDetailPPN,'ppn_amount', P_PurchaseDetailPPNAmount, 'subtotal', P_PurchaseDetailSubTotal, 'total', P_PurchaseDetailTotal)), ']') details,
                IFNULL(M_PaymentPlanName, '') payment_name, IFNULL(M_TermName, '') term_name,

                IFNULL(P_ReceiveDate, '') receive_date, IFNULL(P_ReceiveNumber, '') receive_number,
                IFNULL(F_BillDate, '') bill_date, IFNULL(F_BillNumber, '') bill_number, IFNULL(F_BillPaid, 0) bill_paid,
                IFNULL(F_BillUnpaid, 0) bill_unpaid, IFNULL(F_BillLunas, 'N') bill_lunas,
                IFNULL(S_StaffName, '') sales_name

                FROM `p_purchase`
                JOIN m_vendor ON P_PurchaseM_VendorID = M_VendorID
                    LEFT JOIN p_purchasedetail ON P_PurchaseDetailP_PurchaseID=P_PurchaseID and P_PurchaseDetailIsActive = 'Y'
                    LEFT JOIN m_item ON M_ItemID = P_PurchaseDetailA_ItemID
                    LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                    LEFT JOIN m_paymentplan ON P_PurchaseM_PaymentPlanID = M_PaymentPlanID
                    LEFT JOIN m_term ON P_PurchaseM_TermID = M_TermID

                    LEFT JOIN p_receive ON P_ReceiveP_PurchaseID = P_PurchaseID and P_ReceiveIsActive = 'Y'
                    LEFT JOIN f_bill ON F_BillP_ReceiveID = P_ReceiveID AND F_BillIsActive = 'Y'
                    JOIN s_staff ON P_PurchaseS_StaffID = S_StaffID
                    
                WHERE (`P_PurchaseNumber` LIKE ? OR M_VendorName LIKE ?)
                AND `P_PurchaseIsActive` = 'Y'
                AND ((P_PurchaseM_VendorID = ? AND ? > 0) OR ? = 0)
                AND P_PurchaseDate BETWEEN ? AND ?
                AND ((S_StaffID = ? AND ? > 0) OR ? = 0)
                {$done}
                GROUP BY P_PurchaseID
                ORDER BY P_PurchaseDate DESC, P_PurchaseNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $d['sdate'], $d['edate'], $staff, $staff, $staff]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['details'] = json_decode($v['details']);
                foreach ($r[$k]['details'] as $m => $w)
                {
                    $r[$k]['details'][$m]->itemtotal = (($w->price * (100-$w->disc) / 100) - $w->discrp) * $w->qty;
                }
            }
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`P_PurchaseID`) n
            FROM `p_purchase`
            JOIN m_vendor ON P_PurchaseM_VendorID = M_VendorID
            JOIN s_staff ON P_PurchaseS_StaffID = S_StaffID
            WHERE (`P_PurchaseNumber` LIKE ? OR M_VendorName LIKE ?)
            AND ((P_PurchaseM_VendorID = ? AND ? > 0) OR ? = 0)
            AND P_PurchaseDate BETWEEN ? AND ?
            AND ((S_StaffID = ? AND ? > 0) OR ? = 0)
            {$done}
            AND `P_PurchaseIsActive` = 'Y'", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $d['sdate'], $d['edate'], $staff, $staff, $staff]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }
}

?>