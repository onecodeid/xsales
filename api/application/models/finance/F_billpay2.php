<?php

class F_billpay2 extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_billpay2";
        $this->table_key = "F_BillPay2ID";
    }

    function save ( $d, $id = 0, $uid = 0)
    {
        $hdata = json_decode($d['hdata']);
        $hdata->pay_date = date("Y-m-d", strtotime($hdata->pay_date));
        $d['hdata'] = json_encode($hdata);

        $r = $this->db->query("CALL sp_finance_billpay2_save(?,?,?)", [
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
        $wh_account = $acc_id != 0 ? "AND (F_BillCreditM_AccountID = {$acc_id} OR F_BillDebitM_AccountID = {$acc_id})" : "";

        $select = $this->select();
        $r = $this->db->query(
                "SELECT {$select}

                FROM `{$this->table_name}`
                -- JOIN f_memo ON F_BillPay2F_BillID = F_BillID
                JOIN m_vendor ON F_BillPay2M_VendorID = M_VendorID
                JOIN m_account ac ON ac.M_AccountID = F_BillPay2CreditM_AccountID
                JOIN m_account ad ON ad.M_AccountID = F_BillPay2DebitM_AccountID
                -- LEFT JOIN m_account aa ON F_BillDebitM_AccountID = aa.M_AccountID
                -- LEFT JOIN m_account ab ON F_BillCreditM_AccountID = ab.M_AccountID
                
                WHERE (`F_BillPay2Number` LIKE ?)
                AND `F_BillPay2IsActive` = 'Y'
                AND F_BillPay2Date BETWEEN ? AND ?
                {$wh_account}
                ORDER BY F_BillPay2Number DESC
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
            JOIN f_memo ON F_BillPay2F_BillID = F_BillID
            JOIN m_vendor ON F_BillPay2M_VendorID = M_VendorID
            JOIN m_account ac ON ac.M_AccountID = F_BillPay2CreditM_AccountID
            JOIN m_account ad ON ad.M_AccountID = F_BillPay2DebitM_AccountID
            WHERE (`F_BillPay2Number` LIKE ?)
                AND `F_BillPay2IsActive` = 'Y'
                AND F_BillPay2Date BETWEEN ? AND ?
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
                JOIN f_bill ON F_BillPay2F_BillID = F_BillID
                JOIN m_vendor ON F_BillPay2M_VendorID = M_VendorID
                LEFT JOIN m_account ac ON ac.M_AccountID = F_BillPay2CreditM_AccountID
                LEFT JOIN m_account ad ON ad.M_AccountID = F_BillPay2DiscM_AccountID

                WHERE (`F_BillPay2ID` = ?)
                AND `F_BillPay2IsActive` = 'Y'", [$id]);
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
            $r->vendor = json_decode($r->vendor);
            $r->memos = $this->get_memos($r->pay_id); //json_decode($r->memos);
            return $r;
        }

        return false;
    }

    function save_img($id, $uri)
    {
        $x = $this->db->set("F_BillReceipt", $uri)
                ->where("F_BillID", $id)
                ->update($this->table_name);

        return $x;
    }

    function delete($id, $uid = 0)
    {
        $r = $this->db->query("CALL sp_finance_invoice_delete(?, ?)", [
            $id, 
            $uid
        ])
        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function select() {
        return "F_BillNumber bill_number, F_BillDate bill_date, F_BillGrandTotal bill_grand_total,
            F_BillPay2ID pay_id,
            F_BillPay2Date pay_date,
            F_BillPay2Number pay_number,
            F_BillPay2F_BillID bill_id,
            F_BillPay2Amount pay_amount,
            F_BillPay2Total pay_total,
            IFNULL(F_BillPay2Note, '') pay_note,
            IFNULL(F_BillPay2Memo, '') pay_memo,
            IFNULL(F_BillPay2Receipt, '') pay_receipt,
            F_BillPay2Disc pay_disc, F_BillPay2DiscRp pay_discrp, F_BillPay2DiscAmount pay_discamount,
            -- IFNULL(F_BillPay2Memos, '[]') memos,
            -- IFNULL(F_BillPay2MemoAmount, 0) memo_amount,

            JSON_OBJECT('account_id', ac.M_AccountID, 'account_name', ac.M_AccountName, 'account_code', ac.M_AccountCode) credit_account,
            IF (ad.M_AccountID IS NOT NULL,
                JSON_OBJECT('account_id', ad.M_AccountID, 'account_name', ad.M_AccountName, 'account_code', ad.M_AccountCode), null) disc_account,


            JSON_OBJECT('vendor_id', M_VendorID, 'vendor_name', M_VendorName) vendor";
    }

    function get_memos ($id) {
        $r = $this->db->query("SELECT CONCAT('[',
                GROUP_CONCAT( JSON_OBJECT('memo_id', F_MemoDebitID, 'memo_number', F_MemoDebitNumber, 'memo_date', F_MemoDebitDate, 
                'memo_amount', F_MemoDebitAmount, 'memo_used', F_MemoDebitUsed, 'memo_refunded', F_MemoDebitRefunded) SEPARATOR ','),
                ']') x
                FROM f_billpay2memo 
                JOIN f_memodebit ON F_BillPay2MemoF_MemoDebitID = F_MemoDebitID
                WHERE F_BillPay2MemoF_BillPay2ID = ? AND F_BillPay2MemoIsActive = 'Y'
                ", [$id])->row();
        if ($r) return json_decode($r->x);
        return [];
    }
}