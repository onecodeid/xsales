<?php

class R_reportsales extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "l_invoice";
        $this->table_key = "L_InvoiceID";
        $this->load->model("master/m_customer");
    }

    function sales_015( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $customer = isset($d['customer_id'])?$d['customer_id']:0;

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        
        $q = "SELECT 
                CONCAT('[', GROUP_CONCAT(
                    JSON_OBJECT('invoice_id', L_InvoiceID, 'invoice_number', L_InvoiceNumber, 'invoice_date', L_InvoiceDate, 'invoice_due_date', L_InvoiceDueDate, 'invoice_subtotal', L_InvoiceSubTotal, 'invoice_total', L_InvoiceTotal, 'invoice_grand_total', L_InvoiceGrandTotal, 'invoice_paid', L_InvoicePaid, 'invoice_unpaid', L_InvoiceUnpaid,
                    'invoice_note', IFNULL(L_InvoiceNote, ''), 'invoice_memo', IFNULL(L_InvoiceMemo, ''), 'invoice_lunas', L_InvoiceLunas, 'invoice_term', L_InvoiceM_TermID, 'invoice_dp', L_InvoiceDp, 'invoice_shipping', L_InvoiceShipping, 'invoice_proforma', L_InvoiceProforma, 'invoice_disc', L_InvoiceDiscount, 'invoice_discrp', L_InvoiceDiscountRp, 'invoice_disctotal', L_InvoiceDiscountTotalRp, 'invoice_ppn', L_InvoicePPN, 'invoice_ppnvalue', L_InvoicePPNValue,
                    'journal_id', IFNULL(T_JournalID, 0),
                    'journal_date', IFNULL(T_JournalDate, '0000-00-00'),
                    'journal_note', IFNULL(T_JournalNote, ''),
                    'journal_receipt', IFNULL(T_JournalReceipt, '') )), ']') invoices,
                    SUM(L_InvoiceSubTotal) invoice_subtotal, SUM(L_InvoiceDiscountTotalRp) invoice_disctotal, SUM(L_InvoicePPN) invoice_ppn, SUM(L_InvoiceGrandTotal) invoice_grandtotal,
                    SUM(L_InvoiceShipping) invoice_shipping, SUM(L_InvoiceDP) invoice_dp,
                
                M_CustomerID customer_id, M_CustomerName customer_name, 
                M_CustomerID customer_id, 

                 '' delivery_memos

            FROM m_customer
            JOIN l_invoice ON L_InvoiceM_CustomerID = M_customerID AND L_InvoiceIsActive = 'Y'
            LEFT JOIN t_journal ON L_InvoiceT_JournalID = T_JournalID
            WHERE (`L_InvoiceNumber` LIKE ? OR M_CustomerName LIKE ?)
                AND ((L_InvoiceM_CustomerID = ? AND ? > 0) OR ? = 0)
                AND L_InvoiceDate BETWEEN ? AND ?
            GROUP BY M_CustomerID
            ORDER BY M_CustomerName, L_InvoiceDate
            LIMIT {$limit} OFFSET {$offset}
            ";
        $r = $dbiv->query(
                $q, [$d['search'], $d['search'], $customer, $customer, $customer, $sdate, $edate]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $invoices = json_decode($v['invoices']);
                foreach ($invoices as $l => $w)
                {
                    $w = (array) $w;
                    $details = $this->db->query("SELECT fn_sales_invoice_detail(?) x", [$w['invoice_id']])->row();
                    $accs = $this->db->query("SELECT fn_journal_detail(?) y", [$w['journal_id']])->row();
                    $dps = $this->db->query("SELECT fn_sales_invoice_dp(?) x", [$w['invoice_id']])->row();

                    // GET SALES
                    $sales = json_decode($this->db->query("SELECT fn_sales_invoice_sales(?) x", [$w['invoice_id']])->row()->x);

                    $invoices[$l]->details = json_decode($details->x);
                    $invoices[$l]->accounts = json_decode($accs->y);
                    $invoices[$l]->invoice_dps = json_decode($dps->x);
                    $invoices[$l]->sales = $sales;
                }

                $r[$k]['invoices'] = $invoices;

                // MAIN ADDRESS
                $r[$k]['main_address'] = $this->m_customer->get_main_address($v['customer_id']);
            }
            $lx['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(DISTINCT `L_InvoiceM_CustomerID`) n
            FROM `{$this->table_name}`
            JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID
            
            WHERE (`L_InvoiceNumber` LIKE ? OR M_CustomerName LIKE ?)
            AND ((L_InvoiceM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND L_InvoiceDate BETWEEN ? AND ?
            AND `L_InvoiceIsActive` = 'Y'", [$d['search'], $d['search'], $customer, $customer, $customer, $sdate, $edate]);
        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $lx;
    }

    function sales_016( $d )
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
                    JSON_OBJECT('invoice_id', L_InvoiceID, 'invoice_number', L_InvoiceNumber, 'invoice_date', L_InvoiceDate, 'invoice_due_date', L_InvoiceDueDate, 'invoice_subtotal', L_InvoiceSubTotal, 'invoice_total', L_InvoiceTotal, 'invoice_grand_total', L_InvoiceGrandTotal, 'invoice_paid', L_InvoicePaid, 'invoice_unpaid', L_InvoiceUnpaid,
                    'invoice_note', IFNULL(L_InvoiceNote, ''), 'invoice_memo', IFNULL(L_InvoiceMemo, ''), 'invoice_lunas', L_InvoiceLunas, 'invoice_term', L_InvoiceM_TermID, 'invoice_dp', L_InvoiceDp, 'invoice_shipping', L_InvoiceShipping, 'invoice_proforma', L_InvoiceProforma, 'invoice_disc', L_InvoiceDiscount, 'invoice_discrp', L_InvoiceDiscountRp, 'invoice_disctotal', L_InvoiceDiscountTotalRp, 'invoice_ppn', L_InvoicePPN, 'invoice_ppnvalue', L_InvoicePPNValue,
                    'detail_qty', L_InvoiceDetailQty, 'detail_price', L_InvoiceDetailPrice, 'detail_disc', L_InvoiceDetailDisc,
                    'detail_discrp', L_InvoiceDetailDiscRp, 'detail_subtotal', L_InvoiceDetailSubTotal, 'detail_ppn', L_InvoiceDetailPPN,
                    'detail_ppnamount', L_InvoiceDetailPPNAmount, 'detail_total', L_InvoiceDetailTotal,
                    'detail_disctotal', ((L_InvoiceDetailDisc * L_InvoiceDetailPrice / 100) + L_InvoiceDetailDiscRp),
                    'unit_id', M_UnitID, 'unit_name', M_UnitName,
                    'customer_id', M_CustomerID, 'customer_code', M_CustomerCode, 'customer_name', M_CustomerName, 'sales_id', S_StaffID, 'sales_name', S_StaffName
                     )), ']') invoices,
                     SUM(L_InvoiceDetailQty) detail_qty, SUM(L_InvoiceDetailDiscRp) detail_discrp, SUM(L_InvoiceDetailSubTotal) detail_subtotal, 
                     SUM(L_InvoiceDetailPPNAmount) detail_ppnamount, SUM(L_InvoiceDetailTotal) detail_total,
                     SUM((L_InvoiceDetailDisc * L_InvoiceDetailPrice / 100) + L_InvoiceDetailDiscRp) detail_disctotal,

                M_CategoryID category_id, M_CategoryName category_name,
                M_ItemID item_id, M_ItemName item_name, M_UnitID unit_id, M_UnitName unit_name,
                S_StaffID sales_id, S_StaffName sales_name
                

            FROM l_invoice
            JOIN l_invoicedetail ON L_InvoiceDetailL_InvoiceID = L_InvoiceID 
            JOIN m_item ON L_InvoiceDetailA_ItemID = M_ItemID
            JOIN m_unit ON M_ItemM_UnitID = M_UnitID
            JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
            JOIN s_staff ON L_InvoiceS_StaffID = S_StaffID
            JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID

            WHERE (`L_InvoiceNumber` LIKE ? OR M_CustomerName LIKE ? OR M_ItemName LIKE ?)
                AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)
                AND ((S_StaffID = ? AND ? > 0) OR ? = 0)
                AND L_InvoiceDate BETWEEN ? AND ?
                AND L_InvoiceIsActive = 'Y'
                AND L_InvoiceIsNew = 'Y'
            GROUP BY M_ItemID
            ORDER BY M_CategoryName, M_ItemName, L_InvoiceDate
            LIMIT {$limit} OFFSET {$offset}
            ";
        $r = $dbiv->query(
                $q, [$d['search'], $d['search'], $d['search'], $category, $category, $category, $staff, $staff, $staff, $sdate, $edate]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $invoices = json_decode($v['invoices']);
                foreach ($invoices as $l => $w)
                {
                    $w = (array) $w;

                    // GET SALES
                    $sales = json_decode($this->db->query("SELECT fn_sales_invoice_sales(?) x", [$w['invoice_id']])->row()->x);
                    $invoices[$l]->sales = $sales;
                }

                $r[$k]['invoices'] = $invoices;
            }
            $lx['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(DISTINCT `M_ItemID`) n
            FROM l_invoice
            JOIN l_invoicedetail ON L_InvoiceDetailL_InvoiceID = L_InvoiceID 
            JOIN m_item ON L_InvoiceDetailA_ItemID = M_ItemID
            JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
            JOIN s_staff ON L_InvoiceS_StaffID = S_StaffID
            JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID
            
            WHERE (`L_InvoiceNumber` LIKE ? OR M_CustomerName LIKE ? OR M_ItemName LIKE ?)
                AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)
                AND ((S_StaffID = ? AND ? > 0) OR ? = 0)
                AND L_InvoiceDate BETWEEN ? AND ?
                AND L_InvoiceIsActive = 'Y'
                AND L_InvoiceIsNew = 'Y'", [$d['search'], $d['search'], $d['search'], $category, $category, $category, $staff, $staff, $staff, $sdate, $edate]);
        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $lx;
    }

    function sales_017( $d )
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
                    JSON_OBJECT('invoice_id', L_InvoiceID, 'invoice_number', L_InvoiceNumber, 'invoice_date', L_InvoiceDate, 'invoice_due_date', L_InvoiceDueDate, 'invoice_subtotal', L_InvoiceSubTotal, 'invoice_total', L_InvoiceTotal, 'invoice_grand_total', L_InvoiceGrandTotal, 'invoice_paid', L_InvoicePaid, 'invoice_unpaid', L_InvoiceUnpaid,
                    'invoice_note', IFNULL(L_InvoiceNote, ''), 'invoice_memo', IFNULL(L_InvoiceMemo, ''), 'invoice_lunas', L_InvoiceLunas, 'invoice_term', L_InvoiceM_TermID, 'invoice_dp', L_InvoiceDp, 'invoice_shipping', L_InvoiceShipping, 'invoice_proforma', L_InvoiceProforma, 'invoice_disc', L_InvoiceDiscount, 'invoice_discrp', L_InvoiceDiscountRp, 'invoice_disctotal', L_InvoiceDiscountTotalRp, 'invoice_ppn', L_InvoicePPN, 'invoice_ppnvalue', L_InvoicePPNValue,
                    'detail_qty', L_InvoiceDetailQty, 'detail_price', L_InvoiceDetailPrice, 'detail_disc', L_InvoiceDetailDisc,
                    'detail_discrp', L_InvoiceDetailDiscRp, 'detail_subtotal', L_InvoiceDetailSubTotal, 'detail_ppn', L_InvoiceDetailPPN,
                    'detail_ppnamount', L_InvoiceDetailPPNAmount, 'detail_total', L_InvoiceDetailTotal,
                    'detail_disctotal', ((L_InvoiceDetailDisc * L_InvoiceDetailPrice / 100) + L_InvoiceDetailDiscRp),
                    'unit_id', M_UnitID, 'unit_name', M_UnitName,
                    'customer_id', M_CustomerID, 'customer_code', M_CustomerCode, 'customer_name', M_CustomerName, 'sales_id', S_StaffID, 'sales_name', S_StaffName,
                    'retur_qty', IFNULL(retur_qty, 0), 'retur_nominal', IFNULL(retur_nominal, 0)
                     )), ']') invoices,
                     SUM(L_InvoiceDetailQty) detail_qty, SUM(L_InvoiceDetailDiscRp) detail_discrp, SUM(L_InvoiceDetailSubTotal) detail_subtotal, 
                     SUM(L_InvoiceDetailPPNAmount) detail_ppnamount, SUM(L_InvoiceDetailTotal) detail_total,
                     SUM((L_InvoiceDetailDisc * L_InvoiceDetailPrice / 100) + L_InvoiceDetailDiscRp) detail_disctotal,
                     IFNULL(SUM(retur_qty), 0) retur_qty, IFNULL(SUM(retur_nominal), 0) retur_nominal,

                M_CategoryID category_id, M_CategoryName category_name,
                M_ItemID item_id, M_ItemCode item_code, M_ItemName item_name, M_UnitID unit_id, M_UnitName unit_name,
                S_StaffID sales_id, S_StaffName sales_name

            FROM l_invoice
            JOIN l_invoicedetail ON L_InvoiceDetailL_InvoiceID = L_InvoiceID 
            JOIN m_item ON L_InvoiceDetailA_ItemID = M_ItemID
            JOIN m_unit ON M_ItemM_UnitID = M_UnitID
            JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
            JOIN s_staff ON L_InvoiceS_StaffID = S_StaffID
            JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID

            LEFT JOIN (
                SELECT L_InvoiceID invoice_id, SUM(L_ReturDetailQty) retur_qty, SUM(L_ReturDetailTotal) retur_nominal,
                    L_InvoiceDetailID detail_id
                FROM l_returdetail
                JOIN l_retur ON L_returDetailL_ReturID = L_ReturID AND L_ReturIsActive = 'Y'
                JOIN l_invoicedetail ON L_ReturDetailL_InvoiceDetailID = L_InvoiceDetailID
                JOIN l_invoice ON L_InvoiceDetailL_InvoiceID = L_InvoiceID
                    AND L_InvoiceDate BETWEEN ? AND ?
                WHERE L_ReturDetailIsActive = 'Y'
                GROUP BY L_InvoiceDetailID
            ) retur ON L_InvoiceDetailID = detail_id

            WHERE (`L_InvoiceNumber` LIKE ? OR M_CustomerName LIKE ? OR M_ItemName LIKE ?)
                AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)
                AND ((S_StaffID = ? AND ? > 0) OR ? = 0)
                AND L_InvoiceDate BETWEEN ? AND ?
                AND L_InvoiceIsActive = 'Y'
            GROUP BY M_ItemID
            ORDER BY M_CategoryName, M_ItemName, L_InvoiceDate
            LIMIT {$limit} OFFSET {$offset}
            ";
        $r = $dbiv->query(
                $q, [$sdate, $edate, $d['search'], $d['search'], $d['search'], $category, $category, $category, $staff, $staff, $staff, $sdate, $edate]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $invoices = json_decode($v['invoices']);
                foreach ($invoices as $l => $w)
                {
                    $w = (array) $w;
                    // $details = $this->db->query("SELECT fn_sales_invoice_detail(?) x", [$w['invoice_id']])->row();
                    // $accs = $this->db->query("SELECT fn_journal_detail(?) y", [$w['journal_id']])->row();
                    // $dps = $this->db->query("SELECT fn_sales_invoice_dp(?) x", [$w['invoice_id']])->row();

                    // GET SALES
                    $sales = json_decode($this->db->query("SELECT fn_sales_invoice_sales(?) x", [$w['invoice_id']])->row()->x);

                    // $invoices[$l]->details = json_decode($details->x);
                    // $invoices[$l]->accounts = json_decode($accs->y);
                    // $invoices[$l]->invoice_dps = json_decode($dps->x);
                    $invoices[$l]->sales = $sales;
                }

                $r[$k]['invoices'] = $invoices;

                // MAIN ADDRESS
                // $r[$k]['main_address'] = $this->m_customer->get_main_address($v['customer_id']);
            }
                // $r[$k]['items'] = json_decode($v['items']);
            $lx['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(DISTINCT `M_ItemID`) n
            FROM l_invoice
            JOIN l_invoicedetail ON L_InvoiceDetailL_InvoiceID = L_InvoiceID 
            JOIN m_item ON L_InvoiceDetailA_ItemID = M_ItemID
            JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
            JOIN s_staff ON L_InvoiceS_StaffID = S_StaffID
            JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID
            
            WHERE (`L_InvoiceNumber` LIKE ? OR M_CustomerName LIKE ? OR M_ItemName LIKE ?)
                AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)
                AND ((S_StaffID = ? AND ? > 0) OR ? = 0)
                AND L_InvoiceDate BETWEEN ? AND ?
                AND L_InvoiceIsActive = 'Y'
                AND L_InvoiceIsNew = 'Y'", [$d['search'], $d['search'], $d['search'], $category, $category, $category, $staff, $staff, $staff, $sdate, $edate]);
        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $lx;
    }
}

?>