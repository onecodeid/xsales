<?php

class F_payment extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_payment";
        $this->table_key = "F_PaymentID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT F_PaymentID payment_id, F_PaymentDate payment_date, F_PaymentNumber	payment_number, A_CustomerName customer_name,
                    F_PaymentTotal payment_total, F_PaymentNote payment_note,
                    CONCAT('[',GROUP_CONCAT(JSON_OBJECT('type', M_PaymentTypeID, 'amount', F_PaymentDetailAmount, 'invoice', F_PaymentDetailL_InvoiceID, 'post', F_PaymentDetailPost) ORDER BY F_PaymentDetailID SEPARATOR ','), ']') details,
                    CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('\"', M_PaymentTypeName, '\"') SEPARATOR ','), ']') types
                FROM `{$this->table_name}`
                JOIN one_iv.a_customer ON F_PaymentA_CustomerID = A_CustomerID
                LEFT JOIN f_paymentdetail ON F_PaymentID = F_PaymentDetailF_PaymentID
                    AND F_PaymentDetailIsActive = 'Y'
                LEFT JOIN one_iv.m_paymenttype ON F_PaymentDetailM_PaymentTypeID = M_PaymentTypeID
                WHERE (`F_PaymentNumber` LIKE ? OR `F_PaymentNote` LIKE ? OR A_CustomerName LIKE ?)
                AND `F_PaymentIsActive` = 'Y'
                AND F_PaymentDate BETWEEN ? AND ?
                GROUP BY F_PaymentID
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate']))]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['details'] = json_decode($v['details']);
                $r[$k]['accounts'] = json_decode($v['accounts']);
            }
                
            $l['records'] = $r;
            $l['query'] = $this->db->last_query();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN one_iv.a_customer ON F_PaymentA_CustomerID = A_CustomerID
                WHERE (`F_PaymentNumber` LIKE ? OR `F_PaymentNote` LIKE ? OR A_CustomerName LIKE ?)
                AND `F_PaymentIsActive` = 'Y'
                AND F_PaymentDate BETWEEN ? AND ?", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate']))]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function save ( $d, $id = 0 )
    {
        $r = $this->db->query("CALL sp_payment_save(?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata']
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function save2 ( $d, $id = 0 )
    {
        $r = $this->db->query("CALL sp_payment2_save_new(?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata']
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function del ($id)
    {
        $r = $this->db->query("CALL sp_payment_delete(?)", [$id])
                        ->row();

        return $r;
    }

    function post ($id)
    {
        $r = $this->db->query("CALL sp_payment_post(?)", [$id])
                        ->row();

        return $r;
    }

    function get ($id)
    {
        $r = $this->db->query("SELECT F_PaymentID payment_id, F_PaymentDate payment_date, F_PaymentNumber	payment_number, A_CustomerName customer_name, A_CustomerID customer_id,
                            F_PaymentTotal payment_total, F_PaymentNote payment_note,
                            F_Payment2A_BankAccountID bank_account_id,
                            F_Payment2M_BankID	bank_id,	
                            F_Payment2GiroDate	giro_date,
                            F_Payment2GiroNumber giro_number,
                            F_Payment2TransferDate transfer_date,
                            CONCAT('[',GROUP_CONCAT(JSON_OBJECT('type', M_PaymentTypeID, 'amount', F_PaymentDetailAmount, 'invoice', F_PaymentDetailL_InvoiceID, 'post', F_PaymentDetailPost) ORDER BY F_PaymentDetailID SEPARATOR ','), ']') details,
                            CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('\"', M_PaymentTypeName, '\"') SEPARATOR ','), ']') types
                        FROM `{$this->table_name}`
                        JOIN one_iv.a_customer ON F_PaymentA_CustomerID = A_CustomerID
                        LEFT JOIN f_paymentdetail ON F_PaymentID = F_PaymentDetailF_PaymentID
                            AND F_PaymentDetailIsActive = 'Y'
                        LEFT JOIN one_iv.m_paymenttype ON F_PaymentDetailM_PaymentTypeID = M_PaymentTypeID
                        WHERE (`F_PaymentID` = ?)", [$id])
            ->row();
        return $r;
    }
}

?>