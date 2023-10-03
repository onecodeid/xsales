<?php

class F_receive extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_receive";
        $this->table_key = "F_ReceiveID";
    }

    // function search( $d )
    // {
    //     $limit = isset($d['limit']) ? $d['limit'] : 10;
    //     $offset = ($d['page'] - 1) * $limit;
    //     $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

    //     $r = $this->db->query(
    //             "SELECT F_PaymentID payment_id, F_PaymentDate payment_date, F_PaymentNumber	payment_number, A_CustomerName customer_name,
    //                 F_PaymentTotal payment_total, F_PaymentNote payment_note,
    //                 CONCAT('[',GROUP_CONCAT(JSON_OBJECT('type', M_PaymentTypeID, 'amount', F_PaymentDetailAmount, 'invoice', F_PaymentDetailL_InvoiceID, 'post', F_PaymentDetailPost) ORDER BY F_PaymentDetailID SEPARATOR ','), ']') details,
    //                 CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('\"', M_PaymentTypeName, '\"') SEPARATOR ','), ']') types
    //             FROM `{$this->table_name}`
    //             JOIN one_iv.a_customer ON F_PaymentA_CustomerID = A_CustomerID
    //             LEFT JOIN f_paymentdetail ON F_PaymentID = F_PaymentDetailF_PaymentID
    //                 AND F_PaymentDetailIsActive = 'Y'
    //             LEFT JOIN one_iv.m_paymenttype ON F_PaymentDetailM_PaymentTypeID = M_PaymentTypeID
    //             WHERE (`F_PaymentNumber` LIKE ? OR `F_PaymentNote` LIKE ? OR A_CustomerName LIKE ?)
    //             AND `F_PaymentIsActive` = 'Y'
    //             AND F_PaymentDate BETWEEN ? AND ?
    //             GROUP BY F_PaymentID
    //             LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate']))]);
    //     if ($r)
    //     {
    //         $r = $r->result_array();
    //         foreach ($r as $k => $v)
    //         {
    //             $r[$k]['details'] = json_decode($v['details']);
    //             $r[$k]['accounts'] = json_decode($v['accounts']);
    //         }
                
    //         $l['records'] = $r;
    //         $l['query'] = $this->db->last_query();
    //     }

    //     $r = $this->db->query(
    //         "SELECT count(`{$this->table_key}`) n
    //         FROM `{$this->table_name}`
    //         JOIN one_iv.a_customer ON F_PaymentA_CustomerID = A_CustomerID
    //             WHERE (`F_PaymentNumber` LIKE ? OR `F_PaymentNote` LIKE ? OR A_CustomerName LIKE ?)
    //             AND `F_PaymentIsActive` = 'Y'
    //             AND F_PaymentDate BETWEEN ? AND ?", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate']))]);
    //     if ($r)
    //     {
    //         $l['total'] = $r->row()->n;
    //         $l['total_page'] = ceil($r->row()->n / $limit);
    //     }
            
    //     return $l;
    // }

    function save ( $d, $id = 0 )
    {
        $sp = $d['out'] == true ? "sp_cash_out" : "sp_cash_receive2";
        $r = $this->db->query("CALL {$sp}(?,?,?)", [
            $id, 
            $d['hdata'],
            $d['jdata']
        ])
        ->row();
        
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    // function del ($id)
    // {
    //     $r = $this->db->query("CALL sp_payment_delete(?)", [$id])
    //                     ->row();

    //     return $r;
    // }

    // function post ($id)
    // {
    //     $r = $this->db->query("CALL sp_payment_post(?)", [$id])
    //                     ->row();

    //     return $r;
    // }

    function get ($id, $jid = 0)
    {
        $wh = "(`F_ReceiveID` = ?)";
        if ($jid != 0)
            $wh = "(`F_ReceiveT_JournalID` = ?)";

        $r = $this->db->query("SELECT F_ReceiveID payment_id, F_ReceiveDate payment_date, F_ReceiveNumber	payment_number,
                            F_ReceiveTotal payment_total, F_ReceiveNote payment_note,
                            F_ReceivePost payment_post,
                            F_ReceiveA_BankAccountID bank_account_id,
                            F_ReceiveM_BankID	bank_id,	
                            F_ReceiveGiroDate	giro_date,
                            F_ReceiveGiroNumber giro_number,
                            F_ReceiveTransferDate transfer_date,
                            CONCAT('[',GROUP_CONCAT(JSON_OBJECT('amount', F_ReceiveDetailAmount, 'account', JSON_OBJECT('account_code', M_AccountCode, 'account_id', M_AccountID, 'account_name', M_AccountName), 'post', F_ReceiveDetailPost) ORDER BY F_ReceiveDetailID SEPARATOR ','), ']') details,
                            CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('\"', M_AccountName, '\"') SEPARATOR ','), ']') types
                        FROM `{$this->table_name}`
                        LEFT JOIN f_receivedetail ON F_ReceiveID = F_ReceiveDetailF_ReceiveID
                            AND F_ReceiveDetailIsActive = 'Y'
                        LEFT JOIN m_account ON F_ReceiveDetailM_AccountID = M_AccountID
                        WHERE {$wh}", [$jid==0?$id:$jid])
            ->row();
            
            $r->details = json_decode($r->details);
        return $r;
    }
}

?>