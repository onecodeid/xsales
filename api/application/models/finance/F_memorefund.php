<?php

class F_memorefund extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_memorefund";
        $this->table_key = "F_MemoRefundID";
    }

    function save ( $d, $id = 0, $uid = 0)
    {
        $r = $this->db->query("CALL sp_finance_memo_refund(?,?,?)", [
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
        $wh_account = $acc_id != 0 ? "AND (F_MemoCreditM_AccountID = {$acc_id} OR F_MemoDebitM_AccountID = {$acc_id})" : "";

        $select = $this->select();
        $r = $this->db->query(
                "SELECT {$select}

                FROM `{$this->table_name}`
                JOIN f_memo ON F_MemoRefundF_MemoID = F_MemoID
                JOIN m_customer ON F_MemoRefundM_CustomerID = M_CustomerID
                JOIN m_account ac ON ac.M_AccountID = F_MemoRefundCreditM_AccountID
                JOIN m_account ad ON ad.M_AccountID = F_MemoRefundDebitM_AccountID
                -- LEFT JOIN m_account aa ON F_MemoDebitM_AccountID = aa.M_AccountID
                -- LEFT JOIN m_account ab ON F_MemoCreditM_AccountID = ab.M_AccountID
                
                WHERE (`F_MemoRefundNumber` LIKE ?)
                AND `F_MemoRefundIsActive` = 'Y'
                AND F_MemoRefundDate BETWEEN ? AND ?
                {$wh_account}
                ORDER BY F_MemoRefundNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate']))]);
        if ($r)
        {
            $r = $r->result_array();

            // foreach ($r as $k => $v)
            // {
            //     if ($v['memo_receipt'] != '')
            //     {
            //         $r[$k]['memo_img'] = "data:image/jpg;base64,".base64_encode(file_get_contents(base_url()."../".$v['memo_receipt']));
            //     }
            // }
            $l['records'] = $r;
            // $l['query'] = $this->db->last_query();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN f_memo ON F_MemoRefundF_MemoID = F_MemoID
            JOIN m_customer ON F_MemoRefundM_CustomerID = M_CustomerID
            JOIN m_account ac ON ac.M_AccountID = F_MemoRefundCreditM_AccountID
            JOIN m_account ad ON ad.M_AccountID = F_MemoRefundDebitM_AccountID
            WHERE (`F_MemoRefundNumber` LIKE ?)
                AND `F_MemoRefundIsActive` = 'Y'
                AND F_MemoRefundDate BETWEEN ? AND ?
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
                JOIN f_memo ON F_MemoRefundF_MemoID = F_MemoID
                JOIN m_customer ON F_MemoRefundM_CustomerID = M_CustomerID
                JOIN m_account ac ON ac.M_AccountID = F_MemoRefundCreditM_AccountID
                JOIN m_account ad ON ad.M_AccountID = F_MemoRefundDebitM_AccountID

                WHERE (`F_MemoRefundID` = ?)
                AND `F_MemoRefundIsActive` = 'Y'", [$id]);
        if ($r)
        {
            $r = $r->row();
            $r->credit_account = json_decode($r->credit_account);
            $r->debit_account = json_decode($r->debit_account);
            $r->customer = json_decode($r->customer);
            return $r;
        }

        return false;
    }

    function save_img($id, $uri)
    {
        $x = $this->db->set("F_MemoReceipt", $uri)
                ->where("F_MemoID", $id)
                ->update($this->table_name);

        return $x;
    }

    function delete($id, $uid = 0)
    {
        $r = $this->db->query("CALL sp_finance_memo_delete(?, ?)", [
            $id, 
            $uid
        ])
        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

//     F_MemoRefundDate	date NULL	
// F_MemoRefundNumber	varchar(25) NULL	
// F_MemoRefundF_MemoID	int(11) [0]	
// F_MemoRefundM_CustomerID	int(11) [0]	
// F_MemoRefundAmount	double [0]	
// F_MemoRefundCreditM_AccountID	int(11) [0]	
// F_MemoRefundDebitM_AccountID	int(11) [0]	
// F_MemoRefundNote	varchar(255) NULL	
// F_MemoRefundMemo

    function select() {
        return "F_MemoNumber memo_number, F_MemoDate memo_date, F_MemoAmount memo_amount,
            F_MemoRefundID refund_id,
            F_MemoRefundDate refund_date,
            F_MemoRefundNumber refund_number,
            F_MemoRefundF_MemoID memo_id,
            F_MemoRefundAmount refund_amount,
            IFNULL(F_MemoRefundNote, '') refund_note,
            IFNULL(F_MemoRefundMemo, '') refund_memo,

            JSON_OBJECT('account_id', ac.M_AccountID, 'account_name', ac.M_AccountName, 'account_code', ac.M_AccountCode) credit_account,
            JSON_OBJECT('account_id', ad.M_AccountID, 'account_name', ad.M_AccountName, 'account_code', ad.M_AccountCode) debit_account,

            JSON_OBJECT('customer_id', M_CustomerID, 'customer_name', M_CustomerName) customer";
    }
}