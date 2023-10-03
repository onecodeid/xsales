<?php

class F_billdp extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_billdp";
        $this->table_key = "F_BillDpID";
    }

    function search( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['vendor_id'])?$d['vendor_id']:0;
        $account = isset($d['account'])?$d['account'].'%':'%';
        $full = isset($d['full'])?"AND F_PaymentDpFullyUsed = '{$d['full']}'":'';
        
        $r = $dbiv->query(
                "SELECT F_BillDpID dp_id, F_BillDpNumber dp_number, F_BillDpDate dp_date, F_BillDpAmount dp_amount, 
                F_BillDpM_PaymentTypeID dp_paymenttype,

                F_PayA_BankAccountID dp_bankaccount,
                F_PayM_BankID dp_bank,
                F_PayGiroDate dp_giro_date,
                F_PayGiroNumber dp_giro_number,
                F_PayTransferDate dp_transfer_date,

                F_BillDpUsed dp_used, F_BillDpUnused dp_unused,
                F_BillDpNote dp_note, M_VendorName vendor_name,
                M_VendorID vendor_id, IFNULL(F_BillNumber, '') bill_number, IFNULL(F_BillDate, '') bill_date, M_PaymentTypeName paymenttype_name,
                F_BillDpAcc dp_acc,

                IFNULL(T_JournalID, 0) journal_id,
                IFNULL(T_JournalDate, '0000-00-00') journal_date,
                IFNULL(T_JournalNote, '') journal_note,
                IFNULL(T_JournalReceipt, '') journal_receipt,
                IFNULL(T_JournalMainM_AccountID, 0) journal_account,
                IFNULL(b.M_AccountName, '') account_name,
                CONCAT('[',GROUP_CONCAT(JSON_OBJECT('account', JSON_OBJECT('account_id', a.M_AccountID, 'account_code', a.M_AccountCode, 'account_name', a.M_AccountName, 'parent_code', ''), 'debit', T_JournalDetailDebit, 'credit', T_JournalDetailCredit, 'post', T_JournalDetailPost) ORDER BY T_JournalDetailID SEPARATOR ','), ']') details
                
                FROM `{$this->table_name}`
                JOIN f_pay ON F_BillDpF_PayID = F_PayID
                JOIN m_vendor ON F_BillDpM_VendorID = M_VendorID
                LEFT JOIN m_paymenttype ON F_BillDpM_PaymentTypeID = M_PaymentTypeID
                LEFT JOIN f_bill ON F_BillDpF_BillID = F_BillID
                LEFT JOIN t_journal ON F_BillDpT_JournalID = T_JournalID
                LEFT JOIN t_journaldetail ON T_JournalID = T_JournalDetailT_JournalID AND T_JournalDetailIsActive = 'Y'
                LEFT JOIN m_account a ON T_JournalDetailM_AccountID = a.M_AccountID
                left JOIN m_account b ON T_JournalMainM_AccountID = b.M_AccountiD
                    AND ((b.M_AccountCode LIKE ?))
                   
                WHERE (`F_BillDpNumber` LIKE ? OR M_VendorName LIKE ?)
                AND `F_BillDpIsActive` = 'Y'
                AND ((F_BillDpM_VendorID = ? AND ? > 0) OR ? = 0)
                GROUP BY F_BillDpID
                ORDER BY F_BillDpDate DESC, F_BillDpNumber DESC       
                LIMIT {$limit} OFFSET {$offset}", [$account, $d['search'], $d['search'], $supplier, $supplier, $supplier]);
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
            JOIN m_vendor ON F_BillDpM_VendorID = M_VendorID
            
            WHERE (`F_BillDpNumber` LIKE ? OR M_VendorName LIKE ?)
            AND ((F_BillDpM_VendorID = ? AND ? > 0) OR ? = 0)
            AND `F_BillDpIsActive` = 'Y'", [$d['search'], $d['search'], $supplier, $supplier, $supplier]);
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
        $r = $this->db->query("CALL sp_billdp_save(?,?,?)", [
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
        $r = $this->db->query("CALL sp_billdp_delete(?,?)", [
                        $id,
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
}

?>