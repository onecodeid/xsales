<?php

class L_invoice extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "l_invoice";
        $this->table_key = "L_InvoiceID";
        $this->load->model(['sales/l_sales', 'master/m_customer']);
    }

    function search( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $customer = isset($d['customer_id'])?$d['customer_id']:0;
        $staff = isset($d['staff_id'])?$d['staff_id']:0;
        $lunas = isset($d['lunas'])?" AND L_InvoiceLunas='{$d['lunas']}'" : "";
        $proforma = isset($d['proforma'])?$d['proforma'] : "N";

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        $duedate = isset($d['duedate'])?date("y-m-d", strtotime($d['duedate'])):'2029-01-01';

        // retur
        $retur = isset($d['retur']) ? $d['retur'] : 'N';
        
        $r = $dbiv->query(
                "SELECT L_InvoiceID invoice_id, L_InvoiceNumber invoice_number, DATE_FORMAT(L_InvoiceDate, '%d-%m-%Y') invoice_date, 
                DATE_FORMAT(L_InvoiceDueDate, '%d-%m-%Y') invoice_due_date, datediff(now(), L_InvoiceDueDate) invoice_past_due,
                L_InvoiceTotal invoice_total, L_InvoiceGrandTotal invoice_grand_total, L_InvoicePaid invoice_paid, L_InvoiceUnpaid invoice_unpaid,
                IFNULL(L_InvoiceNote, '') invoice_note, IFNULL(L_InvoiceMemo, '') invoice_memo, 
                M_CustomerID customer_id, M_CustomerName customer_name, 
                L_InvoiceDiscount invoice_disc, L_InvoiceDiscountRp invoice_discrp,
                M_CustomerID customer_id, L_InvoiceLunas invoice_lunas, L_InvoiceM_TermID invoice_term, L_InvoiceDp invoice_dp,
                L_InvoiceShipping invoice_shipping, L_InvoiceProforma invoice_proforma,

                SUM(L_InvoiceDetailReturNominal) invoice_retur,

                IFNULL(T_JournalID, 0) journal_id,
                IFNULL(T_JournalDate, '0000-00-00') journal_date,
                IFNULL(T_JournalNote, '') journal_note,
                IFNULL(T_JournalReceipt, '') journal_receipt,

                IFNULL(GROUP_CONCAT(distinct L_DeliveryMemo SEPARATOR ';'), '') delivery_memos,
                IFNULL(memo_customer, 0) memo_customer, IFNULL(memo_total, 0) memo_total, IFNULL(memo_count, 0) memo_count,
                memos
                
                FROM `{$this->table_name}`
                JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID

                LEFT JOIN l_invoicedetail ON L_InvoiceID = L_InvoiceDetailL_InvoiceID AND L_InvoiceDetailIsActive = 'Y'
                LEFT JOIN t_journal ON L_InvoiceT_JournalID = T_JournalID
                LEFT JOIN l_delivery ON L_InvoiceDetailL_DeliveryID = L_DeliveryID

                LEFT JOIN (
                    SELECT F_MemoM_CustomerID memo_customer, SUM(F_MemoAmount - (F_MemoUsed + F_MemoRefunded)) memo_total,
                        COUNT(F_MEmoID) memo_count, 
                        CONCAT('[', GROUP_CONCAT(JSON_OBJECT('memo_id', F_MemoID, 'retur_id', L_ReturID, 'memo_invoice', l_invoicenumber)), ']') memos
                    FROM f_memo 
                    JOIN l_retur ON L_ReturF_MemoID = F_MemoID
                    JOIN l_invoice ON L_ReturL_InvoiceID = L_InvoiceID
                    WHERE F_MemoIsActive = 'Y' AND F_MemoAmount - (F_MemoUsed + F_MemoRefunded) > 0
                    GROUP BY F_MemoM_CustomerID
                ) memo ON m_customerid = memo_customer
                   
                WHERE (`L_InvoiceNumber` LIKE ? OR M_CustomerName LIKE ?)
                AND `L_InvoiceIsActive` = 'Y'
                AND ((L_InvoiceM_CustomerID = ? AND ? > 0) OR ? = 0)
                AND ((L_InvoiceS_StaffID = ? AND ? > 0) OR ? = 0)
                AND L_InvoiceProforma = ?   
                AND L_InvoiceDate BETWEEN ? AND ?
                AND L_InvoiceDueDate <= ?
                AND ((L_InvoiceDetailQty > L_InvoiceDetailReturQty AND ? = 'Y') OR ? = 'N')
                {$lunas}
                GROUP BY L_InvoiceID      
                ORDER BY L_InvoiceDate DESC, L_InvoiceNumber DESC       
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $customer, $customer, $customer, $staff, $staff, $staff,
                    $proforma, $sdate, $edate, $duedate, $retur, $retur]);
        if ($r)
        {
            $l['q'] = $this->db->last_query();
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                if ($v['invoice_proforma'] == 'Y')
                    $details = $this->db->query("SELECT fn_sales_invoice_proforma_detail(?) x", [$v['invoice_id']])->row();
                else
                    $details = $this->db->query("SELECT fn_sales_invoice_detail(?) x", [$v['invoice_id']])->row();
                $accs = $this->db->query("SELECT fn_journal_detail(?) y", [$v['journal_id']])->row();
                $dps = $this->db->query("SELECT fn_sales_invoice_dp(?) x", [$v['invoice_id']])->row();

                // GET SALES
                $sales = json_decode($this->db->query("SELECT fn_sales_invoice_sales(?) x", [$v['invoice_id']])->row()->x);
                
                $r[$k]['retur_qty'] = 0;
                $r[$k]['retur_nominal'] = 0;
                $details = json_decode($details->x);
                foreach ($details as $m => $n)
                    foreach ($n->items as $o => $p)
                    {
                        $r[$k]['retur_qty'] += $p->retur_qty;
                        $r[$k]['retur_nominal'] += $p->retur_nominal;
                    }
                        

                $r[$k]['details'] = $details;
                $r[$k]['accounts'] = json_decode($accs->y);
                $r[$k]['invoice_dps'] = json_decode($dps->x);
                $r[$k]['sales'] = $sales;
                $r[$k]['payments'] = $this->get_payment_history($v['invoice_id']);
                $r[$k]['memos'] = $v['memos'] == null ? [] : json_decode($v['memos']);

                // DELIVERY ADDRESS
                $r[$k]['delivery_address'] = $this->get_address($v['invoice_id']);

                // MAIN ADDRESS
                $r[$k]['main_address'] = $this->m_customer->get_main_address($v['customer_id']);
            }
                // $r[$k]['items'] = json_decode($v['items']);
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(distinct `{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID
            LEFT JOIN l_invoicedetail ON L_InvoiceID = L_InvoiceDetailL_InvoiceID AND L_InvoiceDetailIsActive = 'Y'
            
            WHERE (`L_InvoiceNumber` LIKE ? OR M_CustomerName LIKE ?)
            AND ((L_InvoiceM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND ((L_InvoiceS_StaffID = ? AND ? > 0) OR ? = 0)
            AND L_InvoiceProforma = ?   
            AND L_InvoiceDate BETWEEN ? AND ?
            AND L_InvoiceDueDate <= ?
            AND ((L_InvoiceDetailQty > L_InvoiceDetailReturQty AND ? = 'Y') OR ? = 'N')
            {$lunas}
            AND `L_InvoiceIsActive` = 'Y'", [$d['search'], $d['search'], $customer, $customer, $customer, $staff, $staff, $staff,
                $proforma, $sdate, $edate, $duedate, $retur, $retur]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_4_payment( $d )
    {
        $dbiv = $this->load->database('dbiv', true);
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $customer = isset($d['customer_id'])?$d['customer_id']:0;
        $edits = isset($d['edits']) ? "OR FIND_IN_SET(One_InvoiceID, '{$d['edits']}')" : "";

        $r = $dbiv->query(
                "SELECT One_InvoiceID invoice_id, One_InvoiceNumber invoice_number, DATE_FORMAT(One_InvoiceDate, '%d-%m-%Y') invoice_date, One_InvoiceDueDate invoice_duedate, One_InvoiceTotal invoice_total,
                One_InvoicePaid invoice_paid, One_InvoiceUnpaid invoice_unpaid,
                '' invoice_note
                FROM `{$this->table_name}`
                WHERE `One_InvoiceNumber` LIKE ?
                AND One_InvoiceLunas = 'N'
                AND ((One_InvoicePelangganID = ? AND ? > 0) OR ? = 0)
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $customer, $customer, $customer]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `One_InvoiceNumber` LIKE ?
                AND One_InvoiceLunas = 'N'
                AND ((One_InvoicePelangganID = ? AND ? > 0) OR ? = 0)", [$d['search'], $customer, $customer, $customer]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_autocomplete( $d )
    {
        $d['limit'] = 100;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function save ( $d, $id, $uid = 0 )
    {
        $r = $this->db->query("CALL sp_invoice_save(?,?,?,?)", [
                        $id,
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function save_proforma ( $d, $id, $uid = 0 )
    {
        $r = $this->db->query("CALL sp_invoice_proforma_save(?,?,?,?)", [
                        json_decode($d['jdata'])[0],
                        $id,
                        $d['hdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function delete ( $id, $uid )
    {
        $r = $this->db->query("CALL sp_invoice_delete(?,?)", [
                        $id,
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function get_by_journal($jid)
    {
        $r = $this->db->where('L_InvoiceT_JournalID', $jid)
                    ->where('L_InvoiceIsActive', 'Y')
                    ->get($this->table_name)
                    ->row();
        return $r;
    }

    function get_address($ivid)
    {
        $r = $this->db->query("SELECT L_SalesDetailL_SalesID id
            FROM l_invoice
            JOIN l_delivery ON L_DeliveryL_InvoiceID = L_InvoiceID AND L_DeliveryIsActive = 'Y'
            JOIN l_deliverydetail ON L_DeliveryDetailL_DeliveryID = L_DeliveryID AND L_DeliveryDetailIsActive = 'Y'
            JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
            WHERE L_InvoiceID = ?
            LIMIT 1", $ivid)->row();


        $a = $this->l_sales->get_address($r->id);
        return $a;
    }

    function search_id( $id )
    {
        $dbiv = $this->db;
        $r = $dbiv->query(
                "SELECT L_InvoiceID invoice_id, L_InvoiceNumber invoice_number, L_InvoiceDate invoice_date, L_InvoiceDueDate invoice_due_date, L_InvoiceTotal invoice_total, L_InvoiceGrandTotal invoice_grand_total, L_InvoicePaid invoice_paid, L_InvoiceUnpaid invoice_unpaid,
                IFNULL(L_InvoiceNote, '') invoice_note, IFNULL(L_InvoiceMemo, '') invoice_memo, 
                M_CustomerID customer_id, M_CustomerName customer_name, 
                L_InvoiceDiscount invoice_disc, L_InvoiceDiscountRp invoice_discrp,
                M_CustomerID customer_id, L_InvoiceLunas invoice_lunas, L_InvoiceM_TermID invoice_term, L_InvoiceDp invoice_dp,
                L_InvoiceShipping invoice_shipping, L_InvoiceProforma invoice_proforma,

                IFNULL(T_JournalID, 0) journal_id,
                IFNULL(T_JournalDate, '0000-00-00') journal_date,
                IFNULL(T_JournalNote, '') journal_note,
                IFNULL(T_JournalReceipt, '') journal_receipt,

                IFNULL(GROUP_CONCAT(distinct L_DeliveryMemo SEPARATOR ';'), '') delivery_memos
                
                FROM `{$this->table_name}`
                JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID

                LEFT JOIN l_invoicedetail ON L_InvoiceID = L_InvoiceDetailL_InvoiceID
                LEFT JOIN t_journal ON L_InvoiceT_JournalID = T_JournalID
                LEFT JOIN l_delivery ON L_InvoiceDetailL_DeliveryID = L_DeliveryID
                   
                WHERE (`L_InvoiceID` = ?)
                AND `L_InvoiceIsActive` = 'Y'", [$id]);
        if ($r)
        {
            $r = $r->row();
            
                $details = $this->db->query("SELECT fn_sales_invoice_detail(?) x", [$r->invoice_id])->row();
                $accs = $this->db->query("SELECT fn_journal_detail(?) y", [$r->journal_id])->row();
                $dps = $this->db->query("SELECT fn_sales_invoice_dp(?) x", [$r->invoice_id])->row();

                // GET SALES
                $sales = json_decode($this->db->query("SELECT fn_sales_invoice_sales(?) x", [$r->invoice_id])->row()->x);
                
                $r->details = json_decode($details->x);
                $r->accounts = json_decode($accs->y);
                $r->invoice_dps = json_decode($dps->x);
                $r->sales = $sales;

                // DELIVERY ADDRESS
                $r->delivery_address = $this->get_address($r->invoice_id);

                // MAIN ADDRESS
                $r->main_address = $this->m_customer->get_main_address($r->customer_id);
            
        }
            
        return $r;
    }

    function get_payment_history($id)
    {
        $r = $this->db->query("SELECT F_Pay2ID pay_id, F_Pay2Date pay_date, F_Pay2Number pay_number, F_Pay2Total pay_total 
                FROM f_pay2 WHERE F_Pay2L_InvoiceID = ? AND F_Pay2IsActive = 'Y' ORDER BY F_Pay2Number DESC", [$id])
                ->result_array();

        if (!$r) return [];
        return $r;
    }
    
    function header_stats()
    {
        $r = $this->db->query("CALL sp_header_stats('SALES')")->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return json_decode($r->stats);
    }

    function search_by_do_id( $id )
    {
        $dbiv = $this->db;
        $r = $dbiv->query(
                "SELECT L_InvoiceID invoice_id, L_InvoiceNumber invoice_number, L_InvoiceDate invoice_date, L_InvoiceDueDate invoice_due_date, 
                    L_InvoiceTotal invoice_total, L_InvoiceGrandTotal invoice_grand_total, L_InvoicePaid invoice_paid, L_InvoiceUnpaid invoice_unpaid,
                IFNULL(L_InvoiceNote, '') invoice_note, IFNULL(L_InvoiceMemo, '') invoice_memo, 
                M_CustomerID customer_id, M_CustomerName customer_name, 
                L_InvoiceDiscount invoice_disc, L_InvoiceDiscountRp invoice_discrp,
                M_CustomerID customer_id, L_InvoiceLunas invoice_lunas, L_InvoiceM_TermID invoice_term, L_InvoiceDp invoice_dp,
                L_InvoiceShipping invoice_shipping, L_InvoiceProforma invoice_proforma
                
                FROM l_invoicedetail
                JOIN l_invoice ON L_InvoiceID = L_InvoiceDetailL_InvoiceID
                JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID

                WHERE L_InvoiceDetailL_DeliveryID = ? AND `L_InvoiceDetailIsActive` = 'Y'
                GROUP BY L_InvoiceID
                ", [$id]);
        if ($r)
        {
            $r = $r->result_array();

            return $r;
        }
            
        return [];
    }
}

?>