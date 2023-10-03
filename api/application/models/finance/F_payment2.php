<?php

class F_payment2 extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_payment2";
        $this->table_key = "F_Payment2ID";
    }

    function search( $d )
    {
        if (!isset($d['page'])) $d['page'] = 1;
        if (!isset($d['search'])) $d['search'] = '%';
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $j_id = 0;
        if (isset($d['journal_id']))
            $j_id = $d['journal_id'];

        $r = $this->db->query(
                "SELECT F_Payment2ID payment_id, F_Payment2Date payment_date, F_Payment2Number	payment_number, M_CustomerName customer_name, M_CustomerID customer_id,
                    F_Payment2Total payment_total, F_Payment2Note payment_note,
                    CONCAT('[',GROUP_CONCAT(JSON_OBJECT('type', 0, 'amount', F_Payment2DetailAmount, 'invoice', F_Payment2DetailL_InvoiceID, 'post', F_Payment2DetailPost, 'disc', F_Payment2DetailIsDisc) ORDER BY F_Payment2DetailID SEPARATOR ','), ']') details,
                    CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('\"', 'KAS BANK dst', '\"') SEPARATOR ','), ']') types,
                    F_Payment2DiscT_JournalID disc_journal_id
                FROM `{$this->table_name}`
                JOIN m_customer ON F_Payment2A_CustomerID = M_CustomerID
                LEFT JOIN f_payment2detail ON F_Payment2ID = F_Payment2DetailF_Payment2ID
                    AND F_Payment2DetailIsActive = 'Y'
--                LEFT JOIN erp.m_paymenttype ON F_Payment2M_PaymentTypeID = M_PaymentTypeID
                WHERE (`F_Payment2Number` LIKE ? OR `F_Payment2Note` LIKE ? OR M_CustomerName LIKE ?)
                AND `F_Payment2IsActive` = 'Y'
                AND F_Payment2Date BETWEEN ? AND ?
                AND ((F_Payment2T_JournalID = ? AND ? <> 0) OR ? = 0)
                GROUP BY F_Payment2ID
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate'])), $j_id, $j_id, $j_id]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['details'] = json_decode($v['details']);

                // $r[$k]['accounts'] = json_decode($v['accounts']);
            }
                
            $l['records'] = $r;
            $l['query'] = $this->db->last_query();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_customer ON F_Payment2A_CustomerID = M_CustomerID
                WHERE (`F_Payment2Number` LIKE ? OR `F_Payment2Note` LIKE ? OR M_CustomerName LIKE ?)
                AND `F_Payment2IsActive` = 'Y'
                AND F_Payment2Date BETWEEN ? AND ?
                AND ((F_Payment2T_JournalID = ? AND ? <> 0) OR ? = 0)", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate'])), $j_id, $j_id, $j_id]);
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
        $r = $this->db->query("CALL sp_payment2_save(?,?,?)", [
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
        $wh = "(`F_Payment2ID` = ?)";
        if ($jid != 0)
            $wh = "(`F_Payment2T_JournalID` = ?)";

        $r = $this->db->query("SELECT F_Payment2ID payment_id, F_Payment2Date payment_date, F_Payment2Number payment_number, 
                            M_CustomerName customer_name, M_CustomerID customer_id,
                            '' city_name,
                            F_Payment2Total payment_total, F_Payment2Note payment_note,
                            F_Payment2Post payment_post,
                            F_Payment2A_BankAccountID bank_account_id,
                            F_Payment2M_BankID	bank_id,	
                            F_Payment2GiroDate	giro_date,
                            F_Payment2GiroNumber giro_number,
                            F_Payment2TransferDate transfer_date,
                            CONCAT('[',GROUP_CONCAT(JSON_OBJECT('amount', F_Payment2DetailAmount, 
                                                                'invoice', JSON_OBJECT('invoice_date', DATE_FORMAT(L_InvoiceDate, '%d-%m-%Y'), 'invoice_duedate', DATE_FORMAT(L_InvoiceDueDate, '%d-%m-%Y'), 'invoice_id', L_InvoiceID, 'invoice_note', 'note', 'invoice_number', L_InvoiceNumber,'invoice_paid', L_InvoicePaid, 'invoice_total', L_InvoiceTotal, 'invoice_unpaid', L_InvoiceUnpaid), 
                                                                'post', F_Payment2DetailPost, 
                                                                'disc', F_Payment2DetailIsDisc,
                                                                'is_retur', F_Payment2DetailIsRetur,
                                                                'retur', IF(L_ReturID IS NOT NULL, JSON_OBJECT('retur_id', IFNULL(L_ReturID,0), 'retur_date', DATE_FORMAT(L_ReturDate, '%d-%m-%Y'), 'retur_number', L_ReturNumber, 'retur_total_qty', 0, 'retur_total', L_ReturTotal, 'note', L_ReturNote, 'retur_used', 0, 'retur_unused', 0), null)) ORDER BY F_Payment2DetailID SEPARATOR ','), ']') details,
                            CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('\"', '', '\"') SEPARATOR ','), ']') types
                        FROM `{$this->table_name}`
                        JOIN m_customer ON F_Payment2A_CustomerID = M_CustomerID
--                        LEFT JOIN erp.m_city ON A_CustomerM_CityID = M_CityID
                        LEFT JOIN f_payment2detail ON F_Payment2ID = F_Payment2DetailF_Payment2ID
                            AND F_Payment2DetailIsActive = 'Y'
--                        LEFT JOIN erp.m_paymenttype ON F_Payment2M_PaymentTypeID = M_PaymentTypeID
                        LEFT JOIN l_invoice ON F_Payment2DetailL_InvoiceID = L_InvoiceID
                        LEFT JOIN l_retur ON F_Payment2DetailL_ReturID = L_ReturID
                            AND F_Payment2DetailIsRetur = 'Y' AND L_ReturConfirm = 'Y'
                        WHERE {$wh}", [$jid==0?$id:$jid])
            ->row();
            $r->details = json_decode($r->details);
            foreach ($r->details as $k => $v)
            {
                $r->details[$k]->retur = json_decode($v->retur);
            }
        return $r;
    }
}

?>