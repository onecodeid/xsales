<?php

class F_bill extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_bill";
        $this->table_key = "F_BillID";
    }

    function search( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['vendor_id'])?$d['vendor_id']:0;
        $lunas = isset($d['lunas'])?" AND F_BillLunas='{$d['lunas']}'" : "";

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        $duedate = isset($d['duedate'])?date("y-m-d", strtotime($d['duedate'])):'2029-01-01';
        
        $r = $dbiv->query(
                "SELECT F_BillID bill_id, F_BillNumber bill_number, DATE_FORMAT(F_BillDate, '%d-%m-%Y') bill_date, 
                DATE_FORMAT(F_BillDueDate, '%d-%m-%Y') bill_due_date, datediff(now(), F_BillDueDate) bill_past_due,
                F_BillTotal bill_total, F_BillGrandTotal bill_grand_total, F_BillPaid bill_paid, F_BillUnpaid bill_unpaid,
                IFNULL(F_BillNote, '') bill_note, M_VendorName vendor_name, F_BillDiscount bill_disc, F_BillDiscountRp bill_discrp,
                M_VendorID vendor_id, F_BillLunas bill_lunas, F_BillDp bill_dp,

                IFNULL(T_JournalID, 0) journal_id,
                IFNULL(T_JournalDate, '0000-00-00') journal_date,
                IFNULL(T_JournalNote, '') journal_note,
                IFNULL(T_JournalReceipt, '') journal_receipt,

                IFNULL(M_TermID, 0) term_id, 
                IFNULL(M_TermName, '') term_name
                
                FROM `{$this->table_name}`
                JOIN m_vendor ON F_BillM_VendorID = M_VendorID

                LEFT JOIN f_billdetail ON F_BillID = F_BillDetailF_BillID
                LEFT JOIN t_journal ON F_BillT_JournalID = T_JournalID
                LEFT JOIN m_term ON F_BillM_TermID = M_TermID
                   
                WHERE (`F_BillNumber` LIKE ? OR M_VendorName LIKE ?)
                AND `F_BillIsActive` = 'Y'
                AND ((F_BillM_VendorID = ? AND ? > 0) OR ? = 0)  
                AND F_BillDate BETWEEN ? AND ?
                AND F_BillDueDate <= ? 
                {$lunas}
                GROUP BY F_BillID      
                ORDER BY F_BillDate DESC, F_BIllNumber DESC       
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $sdate, $edate, $duedate]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $details = $this->db->query("SELECT fn_finance_bill_detail(?) x", [$v['bill_id']])->row();
                $accs = $this->db->query("SELECT fn_journal_detail(?) y", [$v['journal_id']])->row();
                $dps = $this->db->query("SELECT fn_bill_dp(?) x", [$v['bill_id']])->row();
                
                $r[$k]['details'] = json_decode($details->x);
                $r[$k]['accounts'] = json_decode($accs->y);
                $r[$k]['bill_dps'] = json_decode($dps->x);
                $r[$k]['payments'] = $this->get_payment_history($v['bill_id']);
            }
                // $r[$k]['items'] = json_decode($v['items']);
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_vendor ON F_BillM_VendorID = M_VendorID
            
            WHERE (`F_BillNumber` LIKE ? OR M_VendorName LIKE ?)
            AND ((F_BillM_VendorID = ? AND ? > 0) OR ? = 0)
            AND F_BillDate BETWEEN ? AND ?
            AND F_BillDueDate <= ? 
            {$lunas}
            AND `F_BillIsActive` = 'Y'", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $sdate, $edate, $duedate]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_old( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['vendor_id'])?$d['vendor_id']:0;
        $edits = isset($d['edits']) ? "OR FIND_IN_SET(F_BillID, '{$d['edits']}')" : "";

        $r = $dbiv->query(
                "SELECT F_BillID bill_id, F_BillNumber bill_number, F_BillDate bill_date, F_BillTotal bill_total,
                0 bill_paid, 0 bill_unpaid,
                F_BillDone bill_note,
                F_BillNote bill_note
                FROM `{$this->table_name}`
                WHERE `F_BillNumber` LIKE ?
                AND `F_BillIsActive` = 'Y'
                AND (`F_BillDone` = 'N' {$edits})
                AND ((F_BillM_VendorID = ? AND ? > 0) OR ? = 0)
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $supplier, $supplier, $supplier]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `F_BillNumber` LIKE ?
            AND (`F_BillLunas` = 'N' {$edits})
            AND ((F_BillM_VendorID = ? AND ? > 0) OR ? = 0)
            AND `F_BillIsActive` = 'Y'", [$d['search'], $supplier, $supplier, $supplier]);
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
        $d['lunas'] = "N";
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function save_create ( $d, $id = 0, $uid = 0 )
    {
        $r = $this->db->query("CALL sp_purchase_receive_bill_create_new(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function delete ( $id, $uid )
    {
        $r = $this->db->query("CALL sp_bill_delete(?,?)", [
        // $r = $this->db->query("CALL sp_purchase_receive_bill_delete(?,?)", [
                        $id,
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function save ( $d, $id = 0, $uid = 0 )
    {
        $r = $this->db->query("CALL sp_bill_save(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function get_payment_history($id)
    {
        $r = $this->db->query("SELECT F_BillPay2ID pay_id, F_BillPay2Date pay_date, F_BillPay2Number pay_number, F_BillPay2Total pay_total 
                FROM f_billpay2 WHERE F_BillPay2F_BillID = ? AND F_BillPay2IsActive = 'Y' ORDER BY F_BillPay2Number DESC", [$id])
                ->result_array();

        if (!$r) return [];
        return $r;
    }

    function header_stats()
    {
        $r = $this->db->query("CALL sp_header_stats('PURCHASE')")->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return json_decode($r->stats);
    }
}

?>