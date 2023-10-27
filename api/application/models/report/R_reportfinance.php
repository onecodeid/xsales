<?php

class R_reportfinance extends MY_Model
{
    public $dir;

    function __construct()
    {
        parent::__construct();

        $this->table_name = "l_invoice";
        $this->table_key = "L_InvoiceID";
        $this->load->model("master/m_customer");

        $this->dir = getcwd()."/application/models/report/sql/";
    }

    function get_balance_monthly($sdate, $edate)
    {
        $r = $this->db->query("SELECT fn_finance_get_balance_monthly(?,?) as x", [$sdate, $edate])->row();

        return json_decode($r->x);
    }

    function fin_001( $d )
    {
        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        $year = date('Y', strtotime($sdate));

        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_fin001.sql");
        $r = $this->db->query($q, [$sdate, $edate, 
            $sdate, $sdate, $year, $sdate, 
            $year, $d['search']])->result_array();

        foreach ($r as $k => $v)
        {
            $details = json_decode($v['details']);
            if ($details == null) $details = [];

            foreach ($details as $l => $w) {
                if($w->b_debit > $w->b_credit) {
                    $details[$l]->b_debit -= $w->b_credit;
                    $details[$l]->b_credit = 0;
                } else {
                    $details[$l]->b_credit -= $w->b_debit;
                    $details[$l]->b_debit = 0;
                }
            }
            
            $r[$k]['details'] = $details;
        }

        return $r;
    }

    function fin_002( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $account_id = isset($d['account_id'])?$d['account_id']:0;
        $account_ids = isset($d['account_ids'])?$d['account_ids']:'';

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        $year = date('Y', strtotime($sdate));

        $q = file_get_contents($this->dir."q_fin002.sql");
        $r = $dbiv->query(
            $q, [$sdate, $edate, 
                    $sdate, $sdate,
                    $year, $sdate,
                    $account_id, $account_id, $account_id, 
                    $account_ids, $account_ids, $account_ids, 
                    $d['search'], $d['search']]);

        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $details = json_decode($v['details']);
                if ($details == null) $details = [];
                $balance_open = ($v['account_pos'] == 'D' ? $v['balance_debit'] - $v['balance_credit'] : $v['balance_credit'] - $v['balance_debit']);
                $balance_close = $balance_open;

                // patch
                if (sizeof($details) > 1) {
                    if ($details[0]->journal_type == 'J.30') array_splice($details, 0, 1);
                }

                foreach ($details as $l => $w)
                {
                    $trans = ($v['account_pos'] == 'D' ? $w->journal_debit - $w->journal_credit : $w->journal_credit - $w->journal_debit);
                    $details[$l]->balance = $balance_close += $trans;

                    // special case
                    $sjtypes = ["J.01"];
                    if (array_search($details[$l]->journal_type, $sjtypes) !== false) {
                        $details[$l]->ledger_note = $details[$l]->journal_note;
                    }
                }

                $r[$k]['balance_close'] = $balance_close;
                $r[$k]['balance_open'] = $balance_open;
                $r[$k]['details'] = $details;
            }
            $lx['records'] = $r;
            $lx['q'] = $this->db->last_query();
        }

        $r = $dbiv->query(
            "SELECT count(DISTINCT `M_AccountID`) n
            FROM t_journaldetail
            JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalDate 
                BETWEEN ? AND ? AND T_JournalIsActive = 'Y'
            JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
            WHERE T_JournalDetailIsActive = 'Y'
                AND ((T_JournalDetailM_AccountID = ? AND ? <> 0) OR ? = 0)
                and (T_JournalDetailLedgerNote LIKE ? OR T_JournalNumber LIKE ?)
            ", [$sdate, $edate, $account_id, $account_id, $account_id, $d['search'], $d['search']]);
        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $lx;
    }

    function fin_002_1( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $account_id = isset($d['account_id'])?$d['account_id']:0;
        $account_ids = isset($d['account_ids'])?$d['account_ids']:'';

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        $year = date('Y', strtotime($sdate));

        $q = file_get_contents($this->dir."q_fin002.1.sql");
        $r = $dbiv->query(
            $q, [$sdate, $sdate, 
                    $year, $sdate,
                    $sdate, $edate,
                    $sdate, $edate,
                    $account_id, $account_id, $account_id, 
                    $account_ids, $account_ids, $account_ids, 
                    $d['search'], $d['search'],
                    $d['search'], $d['search']]);

        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $details = json_decode($v['details']);
                if ($details == null) $details = [];
                $balance_open = ($v['account_pos'] == 'D' ? $v['balance_debit'] - $v['balance_credit'] : $v['balance_credit'] - $v['balance_debit']);
                $balance_close = $balance_open;

                foreach ($details as $l => $w)
                {
                    $trans = ($v['account_pos'] == 'D' ? $w->journal_debit - $w->journal_credit : $w->journal_credit - $w->journal_debit);
                    $details[$l]->balance = $balance_close += $trans;

                    // special case
                    $sjtypes = ["J.01"];
                    if (array_search($details[$l]->journal_type, $sjtypes) !== false) {
                        $details[$l]->ledger_note = $details[$l]->journal_note;
                    }

                    $details[$l]->acredit = json_decode($details[$l]->acredit);
                    $details[$l]->adebit = json_decode($details[$l]->adebit);
                }

                $r[$k]['balance_close'] = $balance_close;
                $r[$k]['balance_open'] = $balance_open;
                $r[$k]['details'] = $details;
            }
            $lx['records'] = $r;
            $lx['q'] = $this->db->last_query();
        }

        $r = $dbiv->query(
            "SELECT count(DISTINCT `M_AccountID`) n
            FROM t_journaldetail
            JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalDate 
                BETWEEN ? AND ? AND T_JournalIsActive = 'Y'
            JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
            WHERE T_JournalDetailIsActive = 'Y'
                AND ((T_JournalDetailM_AccountID = ? AND ? <> 0) OR ? = 0)
                and (T_JournalDetailLedgerNote LIKE ? OR T_JournalNumber LIKE ?)
            ", [$sdate, $edate, $account_id, $account_id, $account_id, $d['search'], $d['search']]);
        if ($r)
        {
            $lx['total'] = 0;
            $lx['total_page'] = 1;
        }
            
        return $lx;
    }

    // Report Laba Rugi
    function One_fin_008($sdate, $edate)
    {
        $r = $this->db->query("CALL `sp_r_ONE-FIN-008-2`(?,?)", [$sdate, $edate])
                        ->result_array();
            $this->clean_mysqli_connection($this->db->conn_id);

        $accounts = [
            ["id"=>1, "title"=>"Arus Kas dari Aktivitas Operasional", "data"=>[
                ["id"=>1,"code"=>"CF.OPR.CUSTOMER.IN","label"=>"Penerimaan dari Pelanggan"],
                ["id"=>2,"code"=>"CF.OPR.ASSET.CURRENT","label"=>"Aktiva Lancar Lainnya"],
                ["id"=>3,"code"=>"CF.OPR.CUSTOMER.OUT","label"=>"Pembayaran ke Pemasok"],
                ["id"=>4,"code"=>"CF.OPR.LIABLT","label"=>"Hutang Jangka Pendek Lainnya"],
                ["id"=>5,"code"=>"CF.OPR.INCOME.OTHER","label"=>"Pendapatan Lainnya"],
                ["id"=>6,"code"=>"CF.OPR.COST","label"=>"Pengeluaran Operasional"]
            ]],
            ["id"=>2, "title"=>"Arus Kas dari Aktivitas Investasi", "data"=>[
                ["id"=>1,"code"=>"CF.INVEST.FIXED","label"=>"Perolehan / Penjualan Aset"],
                ["id"=>2,"code"=>"CF.INVEST.OTHER","label"=>"Aktivitas Investasi Lainnya"]
            ]],
            ["id"=>3, "title"=>"Arus Kas dari Aktivitas Keuangan", "data"=>[
                ["id"=>1,"code"=>"CF.FINANCE.PAYMENT","label"=>"Pembayaran / Penerimaan Pinjaman"],
                ["id"=>2,"code"=>"CF.FINANCE.BALANCE","label"=>"Ekuitas / Modal"]
            ]]
        ];

        foreach ($accounts as $k => $v) {
            $total = 0;
            foreach ($v['data'] as $l => $w) {
                $w['jdebit'] = 0;
                $w['jcredit'] = 0;
                foreach ($r as $m => $n) {
                    if ($w['code'] == $n['flow_code']) { $w['jdebit']=$n['jdebit']; $w['jcredit']=$n['jcredit']; };
                }
                $accounts[$k]['data'][$l]['jdebit'] = $w['jdebit'];
                $accounts[$k]['data'][$l]['jcredit'] = $w['jcredit'];
                $total += ($w['jdebit']-$w['jcredit']);
            }
            $accounts[$k]['total'] = $total;
        }

        return ['records'=>$accounts];
    }

    function One_fin_008_detail( $d )
    {
        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");

        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;

        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $q = file_get_contents($this->dir."q_fin008_detail.sql");
        $r = $this->db->query(
            $q, [$sdate, $edate,
                    $d['code'], $d['search'], $d['search'], $d['search'], $limit, $offset]);

        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
            }

            $lx['records'] = $r;
            // $lx['q'] = $this->db->last_query();
        }

        $q = file_get_contents($this->dir."q_fin008_detail_total.sql");
        $r = $this->db->query(
            $q, [$sdate, $edate,
                    $d['code'], $d['search'], $d['search'], $d['search']]);

        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }

        return $lx;

        // $accounts = [
        //     ["id"=>1, "title"=>"Arus Kas dari Aktivitas Operasional", "data"=>[
        //         ["id"=>1,"code"=>"CF.OPR.CUSTOMER.IN","label"=>"Penerimaan dari Pelanggan"],
        //         ["id"=>2,"code"=>"CF.OPR.ASSET.CURRENT","label"=>"Aktiva Lancar Lainnya"],
        //         ["id"=>3,"code"=>"CF.OPR.CUSTOMER.OUT","label"=>"Pembayaran ke Pemasok"],
        //         ["id"=>4,"code"=>"CF.OPR.LIABLT","label"=>"Hutang Jangka Pendek Lainnya"],
        //         ["id"=>5,"code"=>"CF.OPR.INCOME.OTHER","label"=>"Pendapatan Lainnya"],
        //         ["id"=>6,"code"=>"CF.OPR.COST","label"=>"Pengeluaran Operasional"]
        //     ]],
        //     ["id"=>2, "title"=>"Arus Kas dari Aktivitas Investasi", "data"=>[
        //         ["id"=>1,"code"=>"CF.INVEST.FIXED","label"=>"Perolehan / Penjualan Aset"],
        //         ["id"=>2,"code"=>"CF.INVEST.OTHER","label"=>"Aktivitas Investasi Lainnya"]
        //     ]],
        //     ["id"=>3, "title"=>"Arus Kas dari Aktivitas Keuangan", "data"=>[
        //         ["id"=>1,"code"=>"CF.FINANCE.PAYMENT","label"=>"Pembayaran / Penerimaan Pinjaman"],
        //         ["id"=>2,"code"=>"CF.FINANCE.BALANCE","label"=>"Ekuitas / Modal"]
        //     ]]
        // ];

        // foreach ($accounts as $k => $v) {
        //     $total = 0;
        //     foreach ($v['data'] as $l => $w) {
        //         $w['jdebit'] = 0;
        //         $w['jcredit'] = 0;
        //         foreach ($r as $m => $n) {
        //             if ($w['code'] == $n['flow_code']) { $w['jdebit']=$n['jdebit']; $w['jcredit']=$n['jcredit']; };
        //         }
        //         $accounts[$k]['data'][$l]['jdebit'] = $w['jdebit'];
        //         $accounts[$k]['data'][$l]['jcredit'] = $w['jcredit'];
        //         $total += ($w['jdebit']-$w['jcredit']);
        //     }
        //     $accounts[$k]['total'] = $total;
        // }

        // return ['records'=>$accounts];
    }

    // Report Laba Rugi
    function One_fin_010($sdate, $edate)
    {
        $edate = date("Y-m-t", strtotime($edate));
        $xdate = date("Y-m-01", strtotime($sdate));
        $ydate = date("Y-m-t", strtotime($sdate));
        $dates = [];
        $rst = [];
        $codes = [];
        $code_names = [];

        while (strtotime($edate) > strtotime($xdate))
        {
            $dates[] = ["start"=>$xdate,"end"=>$ydate,"name"=>date("M y", strtotime($xdate))];
            $xdate = date("Y-m-01", strtotime("+1 month", strtotime($xdate)));
            $ydate = date("Y-m-t", strtotime($xdate));
        }

        foreach ($dates as $k => $v)
        {
            $r = $this->db->query("CALL `sp_r_ONE-FIN-003-2`(?,?)", [$v['start'], $v['end']])
                        ->result_array();
            $this->clean_mysqli_connection($this->db->conn_id);

            foreach ($r as $l => $w) 
            {
                $r[$l]['details'] = json_decode($w['details']);
                if ($w['sub_total'] == null) $r[$l]['sub_total'] = 0;

                if (!isset($codes[$w['sub_type']])) $codes[$w['sub_type']] = [];
                foreach ($r[$l]['details'] as $m => $n)
                {
                    if (array_search($n->account_code, $codes[$w['sub_type']])===false) 
                    {
                        $codes[$w['sub_type']][] = $n->account_code;
                        $code_names[$n->account_code] = $n->account_name;
                    }
                }
            }

            $dates[$k]['data'] = $r;
        }

        // SORT
        foreach ($codes as $k => $v) sort($codes[$k]);

        // PERMAK
        foreach ($dates as $k => $v)
        {
            $r = $v['data'];

            // PER LAPORAN
            foreach ($r as $l => $w) 
            {
                // PER SUB TYPE
                $new_details = [];
                foreach ($codes[$w['sub_type']] as $xx => $yy)
                {
                    $idx = -1;
                    foreach ($w['details'] as $m => $n)
                    {
                        if ($n->account_code == $yy) $idx = $m;
                    }    
                    if ($idx>-1) $new_details[] = $w['details'][$idx];
                    else $new_details[] = ["account_code"=>$yy,"account_name"=>$code_names[$yy],"journal_balance"=>0];
                }
                $r[$l]['details'] = $new_details;
            }

            $dates[$k]['data'] = $r;
        }
        
        
        return $dates;
    }

    function fin_005( $d )
    {
        $sdate = isset($d['sdate'])?date("Y-01-01", strtotime($d['sdate'])):date("Y-01-01");
        $edate = isset($d['edate'])?date("Y-m-t", strtotime($d['edate'])):date("Y-m-d");

        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_fin005.sql");
        $r = $this->db->query($q, [$sdate, $edate, $sdate, $edate, $sdate])->result_array();

        // GET PROFITS
        $balance = $this->get_balance_monthly(date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($edate)));
        if ($balance->profits != 0)
            $r[] = ['account_id'=>99998, 'account_name'=>'Laba Rugi '.$balance->profits_period, 'journal_balance'=>$balance->profits, 'report_type'=>'BALANCE.EQUITY'];
        $r[] = ['account_id'=>99999, 'account_name'=>'Ikhtisar Laba Rugi', 'journal_balance'=>$balance->profit, 'report_type'=>'BALANCE.EQUITY'];

        return $r;
    }

    function fin_006( $d )
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
                    JSON_OBJECT('invoice_id', L_InvoiceID, 'invoice_number', L_InvoiceNumber, 'invoice_date', L_InvoiceDate, 'invoice_due_date', L_InvoiceDueDate, 
                    'invoice_diff_date', datediff(now(), L_InvoiceDueDate), 'invoice_subtotal', L_InvoiceSubTotal, 'invoice_total', L_InvoiceTotal, 'invoice_grand_total', L_InvoiceGrandTotal, 'invoice_paid', L_InvoicePaid, 'invoice_unpaid', L_InvoiceUnpaid,
                    'invoice_note', IFNULL(L_InvoiceNote, ''), 'invoice_memo', IFNULL(L_InvoiceMemo, ''), 'invoice_lunas', L_InvoiceLunas, 'invoice_term', L_InvoiceM_TermID, 'invoice_dp', L_InvoiceDp, 'invoice_shipping', L_InvoiceShipping, 'invoice_proforma', L_InvoiceProforma, 'invoice_disc', L_InvoiceDiscount, 'invoice_discrp', L_InvoiceDiscountRp, 'invoice_disctotal', L_InvoiceDiscountTotalRp, 'invoice_ppn', L_InvoicePPN, 'invoice_ppnvalue', L_InvoicePPNValue,
                    'term_id', M_TermID, 'term_duration', M_TermDuration,
                    'journal_id', IFNULL(T_JournalID, 0),
                    'journal_date', IFNULL(T_JournalDate, '0000-00-00'),
                    'journal_note', IFNULL(T_JournalNote, ''),
                    'journal_receipt', IFNULL(T_JournalReceipt, '') )), ']') invoices,
                    SUM(L_InvoiceSubTotal) invoice_subtotal, SUM(L_InvoiceDiscountTotalRp) invoice_disctotal, SUM(L_InvoicePPN) invoice_ppn, SUM(L_InvoiceGrandTotal) invoice_grandtotal,
                    SUM(L_InvoiceShipping) invoice_shipping,

                    SUM(L_InvoiceUnpaid) total_unpaid,
                    SUM(IF(datediff(now(), L_InvoiceDueDate) <= 0, L_InvoiceUnpaid, 0)) total_ongoing,
                    SUM(IF(datediff(now(), L_InvoiceDueDate) BETWEEN 1 AND 30, L_InvoiceUnpaid, 0)) total_30,
                    SUM(IF(datediff(now(), L_InvoiceDueDate) BETWEEN 31 AND 60, L_InvoiceUnpaid, 0)) total_60,
                    SUM(IF(datediff(now(), L_InvoiceDueDate) BETWEEN 61 AND 90, L_InvoiceUnpaid, 0)) total_90,
                    SUM(IF(datediff(now(), L_InvoiceDueDate) BETWEEN 91 AND 120, L_InvoiceUnpaid, 0)) total_120,
                    SUM(IF(datediff(now(), L_InvoiceDueDate) > 120, L_InvoiceUnpaid, 0)) total_rest,
                
                M_CustomerID customer_id, M_CustomerName customer_name, 
                M_CustomerID customer_id, 

                 '' delivery_memos

            FROM l_invoice
            JOIN m_customer ON L_InvoiceM_CustomerID = M_customerID 
            JOIN m_term ON L_InvoiceM_TermID = M_TermID
            LEFT JOIN t_journal ON L_InvoiceT_JournalID = T_JournalID
            WHERE (`L_InvoiceNumber` LIKE ? OR M_CustomerName LIKE ?)
                AND ((L_InvoiceM_CustomerID = ? AND ? > 0) OR ? = 0)
                AND L_InvoiceDate BETWEEN ? AND ?
                AND L_InvoiceIsActive = 'Y'
                AND L_InvoiceUnpaid > 0
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
            AND `L_InvoiceIsActive` = 'Y'
            AND L_InvoiceUnpaid > 0", [$d['search'], $d['search'], $customer, $customer, $customer, $sdate, $edate]);
        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $lx;
    }

    function fin_007( $d )
    {
        $customerid = 0;
        if (isset($d['customer'])) $customerid = $d['customer'];

        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_fin007.sql");
        // $r = $this->db->query($q, [$d['search'], $d['search'], $d['sdate'], $d['edate']])->result_array();
        $r = $this->db->query($q, [$customerid, $customerid, $customerid])->result_array();

        foreach ($r as $k => $v)
        {
            $r[$k]['invoices'] = json_decode($v['invoices']);
            foreach($r[$k]['invoices'] as $l => $w)
                $r[$k]['invoices'][$l]->payments = json_decode($w->payments);
        }

        return $r;
    }

    function fin_008( $d )
    {
        $dbiv = $this->db;
        $limit = 1000; //isset($d['limit']) ? $d['limit'] : 10;
        $offset = 0; //($d['page'] - 1) * $limit;
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        
        $q = "SELECT m_accountname, IFNULL(jdebit, 0) jdebit, IFNULL(jcredit, 0) jcredit, t_journaltype, m_journaltypename,
                IFNULL(rpttype, M_AccountReportType) rpttype, IFNULL(rpttitle, M_AccountReportTitle) rpttitle,
                IFNULL(rptid, M_AccountReportID) rptid, IFNULL(jids, '[]') jids
                
                FROM m_accountreport
                LEFT JOIN (
                    SELECT m_accountname, sum(t_journaldetaildebit) jdebit, sum(t_journaldetailcredit) jcredit, t_journaltype, m_journaltypename, rpttype, rpttitle, rptid,
                    concat('[', group_concat(t_journalid), ']') jids
                    from t_journaldetail
                    
                    join t_journal on t_journaldetailt_journalid = t_journalid and t_journalisactive = 'Y'
                    and t_journaldate between ? and ?
                    
                    join m_account on t_journaldetailm_accountid = m_accountid and m_accountm_accountgroupid = 1
                    left join m_journaltype on t_journaltype = m_journaltypecode
                    
                    join (
                        select t_journalid jid, m_accountreporttype rpttype, m_accountreporttitle rpttitle, m_accountreportsort rptsort, m_accountreportid rptid
                        from t_journaldetail
                        
                        join t_journal on t_journaldetailt_journalid = t_journalid and t_journalisactive = 'Y'
                        and t_journaldate between ? and ?
                        
                        join m_account on t_journaldetailm_accountid = m_accountid
                        join m_accountreport on m_accountreporttype like 'CASHFLOW%'
                            and m_accountreportisactive = 'Y'
                            and ((m_accountid = m_accountreportm_accountid and m_accountreportm_accountid <> 0) or 
                            (m_accountm_accountgroupid = m_accountreportm_accountgroupid and m_accountreportm_accountgroupid <> 0))
                        
                        where t_journaldetailisactive = 'Y' 
                        order by rptsort, t_journalid
                    ) x on t_journalid = jid
                    
                    where t_journaldetailisactive = 'Y'
                    group by rpttype
                    ORDER BY rptsort) y ON rptid = M_AccountReportID

                WHERE M_AccountReportType LIKE 'CASHFLOW%' AND M_AccountReportIsActive = 'Y'
            ";
        $r = $dbiv->query(
            $q, [$sdate, $edate, $sdate, $edate]);

        if ($r)
        {
            $rst = [
                ['title' => 'Aktivitas Operasional', 'data' => [], 'total' => 0],
                ['title' => 'Aktivitas Investasi', 'data' => [], 'total' => 0],
                ['title' => 'Aktivitas Keuangan', 'data' => [], 'total' => 0]
            ];
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $jids = json_decode($v['jids']);
                $v['jids'] = $jids;
            //     $invoices = json_decode($v['invoices']);
            //     foreach ($invoices as $l => $w)
            //     {
            //         $w = (array) $w;
            //         $details = $this->db->query("SELECT fn_sales_invoice_detail(?) x", [$w['invoice_id']])->row();
            //         $accs = $this->db->query("SELECT fn_journal_detail(?) y", [$w['journal_id']])->row();
            //         $dps = $this->db->query("SELECT fn_sales_invoice_dp(?) x", [$w['invoice_id']])->row();

            //         // GET SALES
            //         $sales = json_decode($this->db->query("SELECT fn_sales_invoice_sales(?) x", [$w['invoice_id']])->row()->x);

            //         $invoices[$l]->details = json_decode($details->x);
            //         $invoices[$l]->accounts = json_decode($accs->y);
            //         $invoices[$l]->invoice_dps = json_decode($dps->x);
            //         $invoices[$l]->sales = $sales;
            //     }

            //     $r[$k]['invoices'] = $invoices;

            //     // MAIN ADDRESS
            //     $r[$k]['main_address'] = $this->m_customer->get_main_address($v['customer_id']);
                if (preg_match("/(CASHFLOW)\.(OPR)/", $v['rpttype']))
                {
                    $rst[0]['data'][] = $v;
                    $rst[0]['total'] += ($v['jdebit'] - $v['jcredit']);
                }

                if (preg_match("/(CASHFLOW)\.(INVEST)/", $v['rpttype']))
                {
                    $rst[1]['data'][] = $v;
                    $rst[1]['total'] += ($v['jdebit'] - $v['jcredit']);
                }

                if (preg_match("/(CASHFLOW)\.(FINANCE)/", $v['rpttype']))
                {
                    $rst[2]['data'][] = $v;
                    $rst[2]['total'] += ($v['jdebit'] - $v['jcredit']);
                }
                // if ($r[$])
            }

            $this->load->model('trans/t_journalyearly');
            $jy = $this->t_journalyearly->get(date('Y', strtotime($edate)));


            $lx['records'] = $rst;
        }

        // $r = $dbiv->query(
        //     "SELECT count(DISTINCT `L_InvoiceM_CustomerID`) n
        //     FROM `{$this->table_name}`
        //     JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID
            
        //     WHERE (`L_InvoiceNumber` LIKE ? OR M_CustomerName LIKE ?)
        //     AND ((L_InvoiceM_CustomerID = ? AND ? > 0) OR ? = 0)
        //     AND L_InvoiceDate BETWEEN ? AND ?
        //     AND `L_InvoiceIsActive` = 'Y'
        //     AND L_InvoiceUnpaid > 0", [$d['search'], $d['search'], $customer, $customer, $customer, $sdate, $edate]);
        // if ($r)
        // {
            $lx['total'] = 1;
            $lx['total_page'] = 1;
        // }
            
        return $lx;
    }

    function fin_008_detail( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        
        $q = "SELECT m_accountname account_name, t_journaldetaildebit jdebit, t_journaldetailcredit jcredit, t_journaltype, m_journaltypename, rpttype, rpttitle, jid,
                t_journaldetailledgernote jnote, t_journalnumber jnumber, t_journaldate jdate

                from t_journaldetail
                
                join t_journal on t_journaldetailt_journalid = t_journalid and t_journalisactive = 'Y'
                and t_journaldate between ? and ?
                
                join m_account on t_journaldetailm_accountid = m_accountid and m_accountm_accountgroupid = 1
                left join m_journaltype on t_journaltype = m_journaltypecode
                
                join (
                    select t_journalid jid, m_accountreporttype rpttype, m_accountreporttitle rpttitle, m_accountreportsort rptsort
                    from t_journaldetail
                    
                    join t_journal on t_journaldetailt_journalid = t_journalid and t_journalisactive = 'Y'
                    and t_journaldate between ? and ?
                    
                    join m_account on t_journaldetailm_accountid = m_accountid
                    join m_accountreport on m_accountreportid = ?
                        and m_accountreportisactive = 'Y'
                        and ((m_accountid = m_accountreportm_accountid and m_accountreportm_accountid <> 0) or 
                        (m_accountm_accountgroupid = m_accountreportm_accountgroupid and m_accountreportm_accountgroupid <> 0))
                    
                    where t_journaldetailisactive = 'Y' 
                    order by rptsort, t_journalid
                ) x on t_journalid = jid
                
                where t_journaldetailisactive = 'Y'
                ORDER BY t_journaldate
                LIMIT {$limit} OFFSET {$offset}
            ";
        $r = $dbiv->query(
            $q, [$sdate, $edate, $sdate, $edate, $d['account_report_id']]);

        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                
            //     $invoices = json_decode($v['invoices']);
            //     foreach ($invoices as $l => $w)
            //     {
            //         $w = (array) $w;
            //         $details = $this->db->query("SELECT fn_sales_invoice_detail(?) x", [$w['invoice_id']])->row();
            //         $accs = $this->db->query("SELECT fn_journal_detail(?) y", [$w['journal_id']])->row();
            //         $dps = $this->db->query("SELECT fn_sales_invoice_dp(?) x", [$w['invoice_id']])->row();

            //         // GET SALES
            //         $sales = json_decode($this->db->query("SELECT fn_sales_invoice_sales(?) x", [$w['invoice_id']])->row()->x);

            //         $invoices[$l]->details = json_decode($details->x);
            //         $invoices[$l]->accounts = json_decode($accs->y);
            //         $invoices[$l]->invoice_dps = json_decode($dps->x);
            //         $invoices[$l]->sales = $sales;
            //     }

            //     $r[$k]['invoices'] = $invoices;

            //     // MAIN ADDRESS
            //     $r[$k]['main_address'] = $this->m_customer->get_main_address($v['customer_id']);
                
                // if ($r[$])
            }
            $lx['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(t_journaldetailid) n
                from t_journaldetail
                join t_journal on t_journaldetailt_journalid = t_journalid and t_journalisactive = 'Y'
                    and t_journaldate between ? and ?
                join m_account on t_journaldetailm_accountid = m_accountid and m_accountm_accountgroupid = 1
            
                join (
                    select t_journalid jid
                    from t_journaldetail
                    join t_journal on t_journaldetailt_journalid = t_journalid and t_journalisactive = 'Y'
                    and t_journaldate between ? and ?
                    join m_account on t_journaldetailm_accountid = m_accountid
                    join m_accountreport on  m_accountreportid = ?
                        and m_accountreportisactive = 'Y'
                        and ((m_accountid = m_accountreportm_accountid and m_accountreportm_accountid <> 0) or 
                        (m_accountm_accountgroupid = m_accountreportm_accountgroupid and m_accountreportm_accountgroupid <> 0))
                    where t_journaldetailisactive = 'Y' 
                ) x on t_journalid = jid
                where t_journaldetailisactive = 'Y'", [$sdate, $edate, $sdate, $edate, $d['account_report_id']]);
        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $lx;
    }

    function fin_009( $d )
    {
        // $period = $d['period'];
        
        // $r = $this->db->query("CALL `sp_r_ONE-FIN-009`(?)", [$period])->row();
        $r = $this->db->query("CALL `sp_r_ONE-FIN-009-2`(?, ?)", [$d['sdate'], $d['edate']])->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        $data = [];
        if ($r)
        {
            $data = $r;
        }

        return ['records' => $data, 'total' => 1, 'total_page' => 1];
    }

    function fin_011( $d )
    {
        $customerid = 0;

        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_fin011.sql");
        $r = $this->db->query($q, [$d['search'], $d['search'], $d['sdate'], $d['edate'], 0, 0, 0])->result_array();

        foreach ($r as $k => $v)
        {
            $r[$k]['invoices'] = json_decode($v['invoices']);
            foreach($r[$k]['invoices'] as $l => $w)
                $r[$k]['invoices'][$l]->payments = json_decode($w->payments);
        }

        return $r;
    }

    function fin_021( $d )
    {
        $customerid = 0;
        $ptype = isset($d['ptype'])?$d['ptype']:0;

        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_fin021.sql");
        $r = $this->db->query($q, [$d['sdate'], $d['edate'], $ptype, $ptype, $ptype])->result_array();

        // foreach ($r as $k => $v)
        // {
        //     $r[$k]['invoices'] = json_decode($v['invoices']);
        //     foreach($r[$k]['invoices'] as $l => $w)
        //         $r[$k]['invoices'][$l]->payments = json_decode($w->payments);
        // }

        return $r;
    }
}

?>