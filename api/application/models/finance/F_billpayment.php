<?php

class F_billpayment extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_billpayment";
        $this->table_key = "F_BillPaymentID";
    }

    // function search( $d )
    // {
    //     $limit = isset($d['limit']) ? $d['limit'] : 10;
    //     $offset = ($d['page'] - 1) * $limit;
    //     $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

    //     $r = $this->db->query(
    //             "SELECT F_PaymentID payment_id, F_PaymentDate payment_date, F_PaymentNumber	payment_number, M_VendorName supplier_name,
    //                 F_PaymentTotal payment_total, F_PaymentNote payment_note,
    //                 CONCAT('[',GROUP_CONCAT(JSON_OBJECT('type', M_PaymentTypeID, 'amount', F_PaymentDetailAmount, 'invoice', F_PaymentDetailF_BillID, 'post', F_PaymentDetailPost) ORDER BY F_PaymentDetailID SEPARATOR ','), ']') details,
    //                 CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('\"', M_PaymentTypeName, '\"') SEPARATOR ','), ']') types
    //             FROM `{$this->table_name}`
    //             JOIN one_iv.a_supplier ON F_PaymentM_VendorID = M_VendorID
    //             LEFT JOIN f_billpaymentdetail ON F_PaymentID = F_PaymentDetailF_PaymentID
    //                 AND F_PaymentDetailIsActive = 'Y'
    //             LEFT JOIN one_iv.m_paymenttype ON F_PaymentDetailM_PaymentTypeID = M_PaymentTypeID
    //             WHERE (`F_PaymentNumber` LIKE ? OR `F_PaymentNote` LIKE ? OR M_VendorName LIKE ?)
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
    //         JOIN one_iv.a_supplier ON F_PaymentM_VendorID = M_VendorID
    //             WHERE (`F_PaymentNumber` LIKE ? OR `F_PaymentNote` LIKE ? OR M_VendorName LIKE ?)
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
        $r = $this->db->query("CALL sp_billpayment_save(?,?,?)", [
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

    function get ($id, $jid = 0)
    {
        $wh = "(`F_BillPaymentID` = ?)";
        if ($jid != 0)
            $wh = "(`F_BillPaymentT_JournalID` = ?)";
            
            

        $r = $this->db->query("SELECT F_BillPaymentID payment_id, F_BillPaymentDate payment_date, F_BillPaymentNumber	payment_number, M_VendorName vendor_name, M_VendorID vendor_id,
                            F_BillPaymentTotal payment_total, F_BillPaymentNote payment_note,
                            F_BillPaymentPost payment_post,
                            F_BillPaymentA_BankAccountID bank_account_id,
                            F_BillPaymentM_BankID	bank_id,	
                            F_BillPaymentGiroDate	giro_date,
                            F_BillPaymentGiroNumber giro_number,
                            F_BillPaymentTransferDate transfer_date,
                            CONCAT('[',GROUP_CONCAT(JSON_OBJECT('amount', F_BillPaymentDetailAmount, 'bill', JSON_OBJECT('bill_date', F_BillDate, 'bill_id', F_BillID, 'bill_note', F_BillNote, 'bill_number', F_BillNumber,'bill_paid', F_BillPaid, 'bill_total', F_BillGrandTotal, 'bill_unpaid', F_BillUnpaid), 'type', JSON_OBJECT('is_disc', M_PaymentDetailIsDisc, 'is_retur', M_PaymentDetailIsRetur, 'paymentdetail_code', M_PaymentDetailCode, 'paymentdetail_id', M_PaymentDetailID, 'paymentdetail_name', M_PaymentDetailName), 'post', F_BillPaymentDetailPost, 'disc', M_PaymentDetailIsDisc, 'is_retur', M_PaymentDetailIsRetur, 'dp', JSON_OBJECT(
'dp_amount', F_BillDpAmount, 'dp_date', F_BillDpDate, 'dp_id', F_BillDpID, 'dp_number', F_BillDpNumber, 'dp_unused', F_BillDpUnused, 'dp_used', F_BillDpUsed)) ORDER BY F_BillPaymentDetailID SEPARATOR ','), ']') details,
                            CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('\"', M_PaymentTypeName, '\"') SEPARATOR ','), ']') types
                        FROM `{$this->table_name}`
                        JOIN m_vendor ON F_BillPaymentA_SupplierID = M_VendorID
                        LEFT JOIN f_billpaymentdetail ON F_BillPaymentID = F_BillPaymentDetailF_BillPaymentID
                            AND F_BillPaymentDetailIsActive = 'Y'
                        LEFT JOIN m_paymentdetail ON F_BillPaymentDetailM_PaymentDetailID = M_PaymentDetailID
                        LEFT JOIN f_billdp ON F_BillPaymentDetailF_BillDpID = F_BillDpID
                        LEFT JOIN m_paymenttype ON F_BillPaymentM_PaymentTypeID = M_PaymentTypeID
                        LEFT JOIN f_bill ON F_BillPaymentDetailF_BillID = F_BillID
                        WHERE {$wh}", [$jid==0?$id:$jid])
            ->row();
            // $this->load->model('admin/a_supplier');
            $this->load->model('master/m_vendor');
            $r->supplier = $this->m_vendor->get($r->vendor_id);
            $r->details = json_decode($r->details);
        return $r;
    }
}

?>