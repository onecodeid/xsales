<?php

class F_cash extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_cash";
        $this->table_key = "F_CashID";
    }

    function save ( $d, $id = 0, $uid = 0)
    {
        $r = $this->db->query("CALL sp_finance_cash_save(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        isset($d['jdata'])?$d['jdata']:"[]",
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function save_batch ( $d, $uid = 0)
    {
        $r = $this->db->query("CALL sp_finance_cash_save_batch(?,?)", [
                        $d['hdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        if ($r->status == "OK") $r->data = json_decode($r->data);
        return $r;
    }

    function search( $d )
    {
        if (!isset($d['page'])) $d['page'] = 1;
        if (!isset($d['search'])) $d['search'] = '%';
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $md5 = isset($d['md5']) ? $d['md5'] : '';

        $acc_id = $d['account_id'];
        $wh_account = $acc_id != 0 ? "AND (F_CashFromM_AccountID = {$acc_id} OR F_CashToM_AccountID = {$acc_id})" : "";
        
        // import query
        $import = isset($d['import']) ? 'JOIN t_journal ON F_CashT_JournalID = T_JournalID AND T_JournalT_ImportID <> 0' : '';

        $r = $this->db->query(
                "SELECT F_CashID cash_id, F_CashDate cash_date, F_CashNumber cash_number,
                F_CashAmount cash_amount, F_CashDisc cash_disc, F_CashDiscRp cash_discrp, 
                F_CashSubTotal cash_subtotal,
                    F_CashTotal cash_total, F_CashNote cash_note, F_CashMemo cash_memo,
                    F_CashFrom cash_from, IFNULL(F_CashReceipt, '') cash_receipt, '' cash_img,
                    IFNULL(F_CashTags, '[]') cash_tags, F_CashMd5 cash_md5,
                    M_CashTypeName cash_type_name, M_CashTypeCode cash_type_code,
                    IFNULL(M_TaxID, 0) tax_id, IFNULL(M_TaxName, '') tax_name, IFNULL(F_CashM_TaxAmount, 0) tax_amount,
                    IFNULL(aca.M_AccountID, 0) from_account_id, IFNULL(aca.M_AccountName, '') from_account_name,
                    IFNULL(acb.M_AccountID, 0) to_account_id, IFNULL(acb.M_AccountName, '') to_account_name,

                    CONCAT('[', GROUP_CONCAT(
                        IF (F_CashDetailID IS NULL, NULL,
                        JSON_OBJECT('account', acc.M_AccountID, 'debit', F_CashDetailDebit, 'credit', F_CashDetailCredit) )
                    ), ']') details
                FROM `{$this->table_name}`
                JOIN m_cashtype ON F_CashM_CashTypeID = M_CashTypeID
                {$import}
                LEFT JOIN m_account aca ON F_CashFromM_AccountID = aca.M_AccountID
                LEFT JOIN m_account acb ON F_CashToM_AccountID = acb.M_AccountID
                LEFT JOIN m_tax ON F_CashM_TaxID = M_TaxID
                
                LEFT JOIN f_cashdetail ON F_CashDetailF_CashID = F_CashID AND F_CashDetailIsActive = 'Y'
                LEFT JOIN m_account acc ON F_cashDetailM_AccountID = acc.M_AccountID

                WHERE (`F_CashNumber` LIKE ? OR F_CashNote LIKE ? OR F_CashMemo LIKE ?)
                AND `F_CashIsActive` = 'Y'
                AND F_CashDate BETWEEN ? AND ?
                AND ((F_CashMd5 = ? AND ? <> '') OR ? = '')
                {$wh_account}
                GROUP BY F_CashID
                ORDER BY F_CashNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate'])), $md5, $md5, $md5]);

        if ($r)
        {
            $r = $r->result_array();

            foreach ($r as $k => $v)
            {
                if ($v['cash_receipt'] != '' && file_exists(base_url()."../".$v['cash_receipt']))
                {
                    $r[$k]['cash_img'] = "data:image/jpg;base64,".base64_encode(file_get_contents(base_url()."../".$v['cash_receipt']));
                }

                if ($v['details'] == null) $r[$k]['details'] = [];
                else $r[$k]['details'] = json_decode($v['details']);
            }
            $l['records'] = $r;
            // $l['query'] = $this->db->last_query();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_cashtype ON F_CashM_CashTypeID = M_CashTypeID
            {$import}
            WHERE (`F_CashNumber` LIKE ? OR F_CashNote LIKE ? OR F_CashMemo LIKE ?)
                AND `F_CashIsActive` = 'Y'
                AND F_CashDate BETWEEN ? AND ?
                AND ((F_CashMd5 = ? AND ? <> '') OR ? = '')
                {$wh_account}
                ", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate'])), $md5, $md5, $md5]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_id( $id )
    {
        $r = $this->db->query(
                "SELECT F_CashID cash_id, F_CashDate cash_date, F_CashNumber cash_number,
                    F_CashTotal cash_total, F_CashNote cash_note, F_CashMemo cash_memo,
                    F_CashAmount cash_amount, F_CashDisc cash_disc, F_CashDiscRp cash_discrp, 
                    F_CashSubTotal cash_subtotal,
                    F_CashFrom cash_from, IFNULL(F_CashReceipt, '') cash_receipt, '' cash_img,
                    IFNULL(F_CashTags, '[]') cash_tags, F_CashMd5 cash_md5,
                    M_CashTypeName cash_type_name, M_CashTypeCode cash_type_code,
                    IFNULL(M_TaxID, 0) tax_id, IFNULL(M_TaxName, '') tax_name, IFNULL(F_CashM_TaxAmount, 0) tax_amount,
                    IFNULL(aca.M_AccountID, 0) from_account_id, IFNULL(aca.M_AccountName, '') from_account_name,
                    IFNULL(acb.M_AccountID, 0) to_account_id, IFNULL(acb.M_AccountName, '') to_account_name,

                    CONCAT('[', GROUP_CONCAT(
                        IF (F_CashDetailID IS NULL, NULL,
                        JSON_OBJECT('account', acc.M_AccountID, 'debit', F_CashDetailDebit, 'credit', F_CashDetailCredit) )
                    ), ']') details
                FROM `{$this->table_name}`
                JOIN m_cashtype ON F_CashM_CashTypeID = M_CashTypeID
                LEFT JOIN m_account aca ON F_CashFromM_AccountID = aca.M_AccountID
                LEFT JOIN m_account acb ON F_CashToM_AccountID = acb.M_AccountID
                LEFT JOIN m_tax ON F_CashM_TaxID = M_TaxID
                LEFT JOIN f_cashdetail ON F_CashDetailF_CashID = F_CashID AND F_CashDetailIsActive = 'Y'
                LEFT JOIN m_account acc ON F_cashDetailM_AccountID = acc.M_AccountID
                WHERE (`F_CashID` = ?)
                AND `F_CashIsActive` = 'Y'
                GROUP BY F_CashID", [$id]);
        if ($r)
        {
            $r = $r->row();
            if ($r->cash_receipt != '')
            {
                $r->cash_img = "data:image/jpg;base64,".base64_encode(file_get_contents(base_url()."../".$r->cash_receipt));
            }
            
            if ($r->details == null) $r->details = [];
            else $r->details = json_decode($r->details);

            return $r;
        }

        return false;
    }

    function save_img($id, $uri)
    {
        $x = $this->db->set("F_CashReceipt", $uri)
                ->where("F_CashID", $id)
                ->update($this->table_name);

        return $x;
    }

    function delete($id, $uid = 0)
    {
        $r = $this->db->query("CALL sp_finance_cash_delete(?, ?)", [
            $id, 
            $uid
        ])
        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
}