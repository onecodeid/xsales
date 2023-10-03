<?php

class F_memo extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_memo";
        $this->table_key = "F_MemoID";
    }

    // {"memo_date":"2023-07-20","memo_customer":"2966","memo_account":"119","memo_amount":"34230","memo_note":"asdasda","memo_tags":"[\"Ghani Ramdhani\",\"Rusmana\"]"}
    function save ( $d, $id = 0, $uid = 0)
    {
        $r = $this->db->query("CALL sp_finance_memo_save(?,?,?)", [
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
                JOIN m_customer ON F_MemoM_CustomerID = M_CustomerID
                LEFT JOIN l_invoice ON F_MemoL_InvoiceID = L_InvoiceID
                LEFT JOIN m_account aa ON F_MemoM_AccountID = aa.M_AccountID
                -- LEFT JOIN m_account ab ON F_MemoCreditM_AccountID = ab.M_AccountID
                
                WHERE (`F_MemoNumber` LIKE ? OR L_InvoiceNumber LIKE ? OR M_CustomerName LIKE ?)
                AND `F_MemoIsActive` = 'Y'
                AND F_MemoDate BETWEEN ? AND ?
                {$wh_account}
                ORDER BY F_MemoNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate']))]);
        if ($r)
        {
            $r = $r->result_array();

            foreach ($r as $k => $v)
            {
                $r[$k]['customer'] = json_decode($v['customer']);
                $r[$k]['memo_tags'] = json_decode($v['memo_tags']);
            //     if ($v['memo_receipt'] != '')
            //     {
            //         $r[$k]['memo_img'] = "data:image/jpg;base64,".base64_encode(file_get_contents(base_url()."../".$v['memo_receipt']));
            //     }
            }
            $l['records'] = $r;
            // $l['query'] = $this->db->last_query();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_customer ON F_MemoM_CustomerID = M_CustomerID
            JOIN l_invoice ON F_MemoL_InvoiceID = L_InvoiceID
            WHERE (`F_MemoNumber` LIKE ? OR L_InvoiceNumber LIKE ? OR M_CustomerName LIKE ?)
                AND `F_MemoIsActive` = 'Y'
                AND F_MemoDate BETWEEN ? AND ?
                ", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate']))]);
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
                JOIN m_customer ON F_MemoM_CustomerID = M_CustomerID
                JOIN l_invoice ON F_MemoL_InvoiceID = L_InvoiceID

                WHERE (`F_MemoID` = ?)
                AND `F_MemoIsActive` = 'Y'", [$id]);
        if ($r)
        {
            $r = $r->row();
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

    function select() {
        return "F_MemoID memo_id, F_MemoDate memo_date, F_MemoNumber memo_number,
            F_MemoAmount memo_amount,
            F_MemoAmount memo_total, F_MemoNote memo_note, F_MemoNote memo_memo,
            IFNULL(F_MemoTags, '[]') memo_tags, IFNULL(F_MemoM_AccountID, 0) memo_account,
            M_CustomerID customer_id, M_CustomerName customer_name,
            IFNULL(L_InvoiceID, 0) invoice_id, IFNULL(L_InvoiceNumber, '') invoice_number,
            M_CustomerName customer_name, M_CustomerID customer_id,
            F_MemoUsed memo_used, F_MemoRefunded memo_refunded,
            JSON_OBJECT('customer_id', M_CustomerID, 'customer_name', M_CustomerName) customer,
            IFNULL(M_AccountID, 0) account_id, IFNULL(M_AccountName, '') account_name";
    }

    function search_av( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = 0 * $limit;

        $select = $this->select();
        $r = $this->db->query(
                "SELECT {$select}

                FROM `{$this->table_name}`
                JOIN m_customer ON F_MemoM_CustomerID = M_CustomerID
                LEFT JOIN l_invoice ON F_MemoL_InvoiceID = L_InvoiceID
                LEFT JOIN m_account aa ON F_MemoM_AccountID = aa.M_AccountID
                -- LEFT JOIN m_account aa ON F_MemoDebitM_AccountID = aa.M_AccountID
                -- LEFT JOIN m_account ab ON F_MemoCreditM_AccountID = ab.M_AccountID
                
                WHERE (`F_MemoM_CustomerID` = ?)
                AND `F_MemoIsActive` = 'Y'
                AND F_MemoAmount > (F_MemoUsed + F_MemoRefunded)
                ORDER BY F_MemoNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['customer_id']]);
        if ($r)
        {
            $r = $r->result_array();

            foreach ($r as $k => $v)
            {
                $r[$k]['customer'] = json_decode($v['customer']);
            }
        }

        return $r;
    }
}