<?php

class F_paymentdp extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_paymentdp";
        $this->table_key = "F_PaymentDpID";
    }

    function search( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $customer = isset($d['customer_id'])?$d['customer_id']:0;
        $account = isset($d['account'])?$d['account'].'%':'%';
        $full = isset($d['full'])?"AND F_PaymentDpFullyUsed = '{$d['full']}'":'';
        
        if (isset($d['edits']))
            $full = preg_replace("/(AND)/", "AND (", $full) . " OR F_PaymentDpID IN ('{$d['edits']}'))";

        $r = $dbiv->query(
                "SELECT F_PaymentDpID dp_id, F_PaymentDpNumber dp_number, F_PaymentDpDate dp_date, F_PaymentDpAmount dp_amount, F_PaymentDpUsed dp_used, F_PaymentDpUnused dp_unused, F_PaymentDpFullyUsed dp_full, F_PayM_PaymentTypeID dp_paymenttype,
                F_PayA_BankAccountID dp_bankaccount,
                F_PayM_BankID dp_bank,
                F_PayGiroDate dp_giro_date,
                F_PayGiroNumber dp_giro_number,
                F_PayTransferDate dp_transfer_date,
                F_PaymentDpUsed dp_used, F_PaymentDpUnused dp_unused,
                F_PaymentDpNote dp_note, M_CustomerName customer_name,
                M_CustomerID customer_id, M_PaymentTypeName paymenttype_name,
                F_PaymentDpAcc dp_acc,

                IFNULL(T_JournalID, 0) journal_id,
                IFNULL(T_JournalDate, '0000-00-00') journal_date,
                IFNULL(T_JournalNote, '') journal_note,
                IFNULL(T_JournalReceipt, '') journal_receipt,
                IFNULL(T_JournalMainM_AccountID, 0) journal_account,
                IFNULL(b.M_AccountName, '') account_name,
                CONCAT('[',GROUP_CONCAT(JSON_OBJECT('account', JSON_OBJECT('account_id', a.M_AccountID, 'account_code', a.M_AccountCode, 'account_name', a.M_AccountName, 'parent_code', ''), 'debit', T_JournalDetailDebit, 'credit', T_JournalDetailCredit, 'post', T_JournalDetailPost) ORDER BY T_JournalDetailID SEPARATOR ','), ']') details
                
                FROM `{$this->table_name}`
                JOIN f_pay ON F_PaymentDpF_PayID = F_PayID
                JOIN m_customer ON F_PaymentDpM_CustomerID = M_CustomerID
                left JOIN m_paymenttype ON F_PayM_PaymentTypeID = M_PaymentTypeID
                left JOIN t_journal ON F_PaymentDpT_JournalID = T_JournalID
                LEFT JOIN t_journaldetail ON T_JournalID = T_JournalDetailT_JournalID AND T_JournalDetailIsActive = 'Y'
                LEFT JOIN m_account a ON T_JournalDetailM_AccountID = a.M_AccountID
                left JOIN m_account b ON T_JournalMainM_AccountID = b.M_AccountiD
                    AND ((b.M_AccountCode LIKE ?))
                   
                WHERE (`F_PaymentDpNumber` LIKE ? OR M_CustomerName LIKE ?)
                AND `F_PaymentDpIsActive` = 'Y'
                AND ((F_PaymentDpM_CustomerID = ? AND ? > 0) OR ? = 0)
                {$full}
                GROUP BY F_PaymentDpID
                ORDER BY F_PaymentDpDate DESC, F_PaymentDpNumber DESC       
                LIMIT {$limit} OFFSET {$offset}", [$account, $d['search'], $d['search'], $customer, $customer, $customer]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['details'] = json_decode($v['details']);
            }
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_customer ON F_PaymentDpM_CustomerID = M_CustomerID
            JOIN f_pay ON F_PaymentDpF_PayID = F_PayID
            JOIN t_journal ON F_PaymentDpT_JournalID = T_JournalID
            JOIN m_account b ON T_JournalMainM_AccountID = b.M_AccountiD
                    AND ((b.M_AccountCode LIKE ?))
            
            WHERE (`F_PaymentDpNumber` LIKE ? OR M_CustomerName LIKE ?)
            AND ((F_PaymentDpM_CustomerID = ? AND ? > 0) OR ? = 0)
            AND `F_PaymentDpIsActive` = 'Y'
            {$full}", [$account, $d['search'], $d['search'], $customer, $customer, $customer]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_autocomplete( $d )
    {
        $d['limit'] = 200;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function save ( $d, $id = 0, $uid = 0 )
    {
        $r = $this->db->query("CALL sp_paymentdp_save(?,?,?)", [
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
        $r = $this->db->query("CALL sp_paymentdp_delete(?,?)", [
                        $id,
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
}

?>