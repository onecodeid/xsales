<?php

class F_pay2 extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_pay2";
        $this->table_key = "F_Pay2ID";
    }

    function save ( $d, $id = 0, $uid = 0)
    {
        $hdata = json_decode($d['hdata']);
        $hdata->pay_date = date("Y-m-d", strtotime($hdata->pay_date));
        $d['hdata'] = json_encode($hdata);

        $r = $this->db->query("CALL sp_finance_pay2_save(?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function search( $d )
    {
        if (!isset($d['page'])) $d['page'] = 1;
        if (!isset($d['search'])) $d['search'] = '%';
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $acc_id = $d['account_id'];
        $wh_account = $acc_id != 0 ? "AND (L_InvoiceCreditM_AccountID = {$acc_id} OR L_InvoiceDebitM_AccountID = {$acc_id})" : "";

        $select = $this->select();
        $r = $this->db->query(
                "SELECT {$select}

                FROM `{$this->table_name}`
                JOIN f_memo ON F_Pay2L_InvoiceID = L_InvoiceID
                JOIN m_customer ON F_Pay2M_CustomerID = M_CustomerID
                JOIN m_account ac ON ac.M_AccountID = F_Pay2CreditM_AccountID
                JOIN m_account ad ON ad.M_AccountID = F_Pay2DebitM_AccountID
                -- LEFT JOIN m_account aa ON L_InvoiceDebitM_AccountID = aa.M_AccountID
                -- LEFT JOIN m_account ab ON L_InvoiceCreditM_AccountID = ab.M_AccountID
                
                WHERE (`F_Pay2Number` LIKE ?)
                AND `F_Pay2IsActive` = 'Y'
                AND F_Pay2Date BETWEEN ? AND ?
                {$wh_account}
                ORDER BY F_Pay2Number DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate']))]);
        if ($r)
        {
            $r = $r->result_array();

            // foreach ($r as $k => $v)
            // {
            //     if ($v['invoice_receipt'] != '')
            //     {
            //         $r[$k]['invoice_img'] = "data:image/jpg;base64,".base64_encode(file_get_contents(base_url()."../".$v['invoice_receipt']));
            //     }
            // }
            $l['records'] = $r;
            // $l['query'] = $this->db->last_query();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN f_memo ON F_Pay2L_InvoiceID = L_InvoiceID
            JOIN m_customer ON F_Pay2M_CustomerID = M_CustomerID
            JOIN m_account ac ON ac.M_AccountID = F_Pay2CreditM_AccountID
            JOIN m_account ad ON ad.M_AccountID = F_Pay2DebitM_AccountID
            WHERE (`F_Pay2Number` LIKE ?)
                AND `F_Pay2IsActive` = 'Y'
                AND F_Pay2Date BETWEEN ? AND ?
                ", [$d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate']))]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_id( $id )
    {
        $select = $this->select();
        $r = $this->db->query(
                "SELECT {$select}
                FROM `{$this->table_name}`
                JOIN l_invoice ON F_Pay2L_InvoiceID = L_InvoiceID
                JOIN m_customer ON F_Pay2M_CustomerID = M_CustomerID
                LEFT JOIN m_account ac ON ac.M_AccountID = F_Pay2CreditM_AccountID
                LEFT JOIN m_account ad ON ad.M_AccountID = F_Pay2DiscM_AccountID

                WHERE (`F_Pay2ID` = ?)
                AND `F_Pay2IsActive` = 'Y'", [$id]);
        if ($r)
        {
            $r = $r->row();
            $r->credit_account = json_decode($r->credit_account);
            try {
                if (!$r->credit_account->account_id)
                    $r->credit_account = null;
            } catch (\Throwable $th) {
                $r->credit_account = null;
            }
            $r->disc_account = json_decode($r->disc_account);
            $r->customer = json_decode($r->customer);
            $r->memos = $this->get_memos($r->pay_id); //json_decode($r->memos);
            return $r;
        }

        return false;
    }

    function save_img($id, $uri)
    {
        $x = $this->db->set("L_InvoiceReceipt", $uri)
                ->where("L_InvoiceID", $id)
                ->update($this->table_name);

        return $x;
    }

    function delete($id, $uid = 0)
    {
        $r = $this->db->query("CALL sp_finance_pay2_delete(?, ?)", [
            $id, 
            $uid
        ])
        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function select() {
        return "L_InvoiceNumber invoice_number, DATE_FORMAT(L_InvoiceDate, '%d-%m-%Y') invoice_date, L_InvoiceGrandTotal invoice_grand_total,
            L_InvoicePaid invoice_paid, L_InvoiceUnpaid invoice_unpaid, L_InvoiceGrandTotal invoice_grand_total,
            F_Pay2ID pay_id,
            DATE_FORMAT(F_Pay2Date, '%d-%m-%Y') pay_date,
            F_Pay2Number pay_number,
            F_Pay2L_InvoiceID invoice_id,
            F_Pay2Amount pay_amount,
            F_Pay2Total pay_total,
            IFNULL(F_Pay2Note, '') pay_note,
            IFNULL(F_Pay2Memo, '') pay_memo,
            IFNULL(F_Pay2Receipt, '') pay_receipt,
            F_Pay2Disc pay_disc, F_Pay2DiscRp pay_discrp, F_Pay2DiscAmount pay_discamount,
            IFNULL(F_Pay2Memos, '[]') memos,
            IFNULL(F_Pay2MemoAmount, 0) memo_amount,

            JSON_OBJECT('account_id', ac.M_AccountID, 'account_name', ac.M_AccountName, 'account_code', ac.M_AccountCode) credit_account,
            IF (ad.M_AccountID IS NOT NULL,
                JSON_OBJECT('account_id', ad.M_AccountID, 'account_name', ad.M_AccountName, 'account_code', ad.M_AccountCode), null) disc_account,


            JSON_OBJECT('customer_id', M_CustomerID, 'customer_name', M_CustomerName) customer";
    }

    function get_memos ($id) {
        $r = $this->db->query("SELECT CONCAT('[',
                GROUP_CONCAT( JSON_OBJECT('memo_id', F_MemoID, 'memo_number', F_MemoNumber, 'memo_date', F_MemoDate, 
                'memo_amount', F_MemoAmount, 'memo_used', F_MemoUsed, 'memo_refunded', F_MemoRefunded) SEPARATOR ','),
                ']') x
                FROM f_pay2memo 
                JOIN f_memo ON F_Pay2MemoF_MemoID = F_MemoID
                WHERE F_Pay2MemoF_Pay2ID = ? AND F_Pay2MemoIsActive = 'Y'
                ", [$id])->row();
        if ($r) return json_decode($r->x);
        return [];
    }

    function get_history($invoice_id) {
        $r = $this->db->query("SELECT F_Pay2ID pay_id, F_Pay2Date pay_date, M_AccountID account_id, M_AccountName account_name, M_AccountCode account_code,
                F_Pay2Amount pay_amount, F_Pay2DiscAmount pay_disc_amount, F_Pay2Total pay_total
                FROM f_pay2 
                JOIN m_account ON F_Pay2CreditM_AccountID = M_AccountID
                WHERE F_Pay2L_InvoiceID = ?
                AND F_Pay2IsActive = 'Y'
                ORDER BY F_Pay2Date ASC", [$invoice_id])->result_array();
        
        return $r;
    }
}