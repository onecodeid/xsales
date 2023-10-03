<?php

class F_memodebit extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_memodebit";
        $this->table_key = "F_MemoDebitID";
    }

    // {"memo_date":"2023-07-20","memo_vendor":"2966","memo_account":"119","memo_amount":"34230","memo_note":"asdasda","memo_tags":"[\"Ghani Ramdhani\",\"Rusmana\"]"}
    function save ( $d, $id = 0, $uid = 0)
    {
        $r = $this->db->query("CALL sp_finance_memo_debit_save(?,?,?)", [
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
        // $wh_account = $acc_id != 0 ? "AND (F_MemoDebitCreditM_AccountID = {$acc_id} OR F_MemoDebitDebitM_AccountID = {$acc_id})" : "";
        $wh_account = "";

        $select = $this->select();
        $r = $this->db->query(
                "SELECT {$select}

                FROM `{$this->table_name}`
                JOIN m_vendor ON F_MemoDebitM_VendorID = M_VendorID
                LEFT JOIN f_bill ON F_MemoDebitF_BillID = F_BillID
                LEFT JOIN m_account aa ON F_MemoDebitM_AccountID = aa.M_AccountID
                -- LEFT JOIN m_account ab ON F_MemoDebitCreditM_AccountID = ab.M_AccountID
                
                WHERE (`F_MemoDebitNumber` LIKE ? OR F_BillNumber LIKE ? OR M_VendorName LIKE ?)
                AND `F_MemoDebitIsActive` = 'Y'
                AND F_MemoDebitDate BETWEEN ? AND ?
                {$wh_account}
                ORDER BY F_MemoDebitNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate']))]);
        if ($r)
        {
            $r = $r->result_array();

            foreach ($r as $k => $v)
            {
                $r[$k]['vendor'] = json_decode($v['vendor']);
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
            JOIN m_vendor ON F_MemoDebitM_VendorID = M_VendorID
            JOIN f_bill ON F_MemoDebitF_BillID = F_BillID
            WHERE (`F_MemoDebitNumber` LIKE ? OR F_BillNumber LIKE ? OR M_VendorName LIKE ?)
                AND `F_MemoDebitIsActive` = 'Y'
                AND F_MemoDebitDate BETWEEN ? AND ?
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
                JOIN m_vendor ON F_MemoDebitM_VendorID = M_VendorID
                JOIN f_bill ON F_MemoDebitF_BillID = F_BillID

                WHERE (`F_MemoDebitID` = ?)
                AND `F_MemoDebitIsActive` = 'Y'", [$id]);
        if ($r)
        {
            $r = $r->row();
            $r->vendor = json_decode($r->vendor);
            return $r;
        }

        return false;
    }

    function save_img($id, $uri)
    {
        $x = $this->db->set("F_MemoDebitReceipt", $uri)
                ->where("F_MemoDebitID", $id)
                ->update($this->table_name);

        return $x;
    }

    function delete($id, $uid = 0)
    {
        $r = $this->db->query("CALL sp_finance_memo_debit_delete(?, ?)", [
            $id, 
            $uid
        ])
        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function select() {
        return "F_MemoDebitID memo_id, F_MemoDebitDate memo_date, F_MemoDebitNumber memo_number,
            F_MemoDebitAmount memo_amount,
            F_MemoDebitAmount memo_total, F_MemoDebitNote memo_note, F_MemoDebitNote memo_memo,
            IFNULL(F_MemoDebitTags, '[]') memo_tags, IFNULL(F_MemoDebitM_AccountID, 0) memo_account,
            M_VendorID vendor_id, M_VendorName vendor_name,
            IFNULL(F_BillID, 0) bill_id, IFNULL(F_BillNumber, '') bill_number,
            M_VendorName vendor_name, M_VendorID vendor_id,
            F_MemoDebitUsed memo_used, F_MemoDebitRefunded memo_refunded,
            JSON_OBJECT('vendor_id', M_VendorID, 'vendor_name', M_VendorName) vendor,
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
                JOIN m_vendor ON F_MemoDebitM_VendorID = M_VendorID
                LEFT JOIN f_bill ON F_MemoDebitF_BillID = F_BillID
                LEFT JOIN m_account aa ON F_MemoDebitM_AccountID = aa.M_AccountID
                -- LEFT JOIN m_account aa ON F_MemoDebitDebitM_AccountID = aa.M_AccountID
                -- LEFT JOIN m_account ab ON F_MemoDebitCreditM_AccountID = ab.M_AccountID
                
                WHERE (`F_MemoDebitM_VendorID` = ?)
                AND `F_MemoDebitIsActive` = 'Y'
                AND F_MemoDebitAmount > (F_MemoDebitUsed + F_MemoDebitRefunded)
                ORDER BY F_MemoDebitNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['vendor_id']]);
        if ($r)
        {
            $r = $r->result_array();

            foreach ($r as $k => $v)
            {
                $r[$k]['vendor'] = json_decode($v['vendor']);
            }
        }

        return $r;
    }
}