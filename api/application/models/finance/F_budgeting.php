<?php

class F_budgeting extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_budgeting";
        $this->table_key = "F_BudgetingID";
    }

    function save ( $d, $uid )
    {
        $r = $this->db->query("CALL sp_finance_budgeting_save(?,?)", [
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
    
    function search( $d )
    {
        $diff = $d['sdate'] == '2023-01-01' ? [0, 2] : [1, 1];
        $sdate = strtotime("-{$diff[0]} month", strtotime($d['sdate']));
        $edate = strtotime("+{$diff[1]} month", strtotime($d['sdate']));
        // $sdate = strtotime("2023-01-01");
        // $edate = strtotime("2023-12-01");

        $r = $this->db->query(
                "SELECT *, IF(F_BudgetingID IS NOT NULL, CONCAT('[', GROUP_CONCAT(
                        JSON_OBJECT('date', CONCAT(F_BudgetingYear,'-',F_BudgetingMonth), 'budget', F_BudgetingBudget, 'actual', IFNULL(jactual, 0) ) ORDER BY F_BudgetingYear, F_BudgetingMonth), ']'), '[]') budgets,
                        IF(F_BudgetingID IS NOT NULL,
                            CONCAT('[', GROUP_CONCAT(CONCAT('\"', F_BudgetingYear,'-',F_BudgetingMonth, '\"') ORDER BY F_BudgetingYear, F_BudgetingMonth), ']'), '[]') budget_dates FROM (
                    
                    SELECT a.*, m_accountgroup.*, IFNULL(T_AccountLastDate, '') last_date,
                    IFNULL(T_AccountLastBalance, 0) last_balance,
                    IFNULL(M_AccountGroupName, '') group_name,
                    IFNULL(b.M_AccountCode, '') parent_code,
                    IF(b.M_AccountCode IS NULL, 0, 1) level,
                    IFNULL(pay.M_AccountMapReffID, 0) pay_id,
                    IFNULL(pay.M_AccountMapA_BankAccountID, 0) bank_account_id,
                    false parent,
                    IFNULL(M_AccountMapType, '') map_type,
                    IFNULL(M_AccountMapReffID, 0) map_ref,
                
                    a.M_AccountID account_id, a.M_AccountCode account_code, a.M_AccountName account_name,
                    a.M_AccountCredit balance_credit,
                    a.M_AccountDebit balance_debit,
                    a.M_AccountLastCredit last_credit,
                    a.M_AccountLastDebit last_debit,
                    a.M_AccountType account_side,
                    a.M_AccountPos account_pos
                    FROM `m_account` a
                    LEFT JOIN t_accountlast ON T_AccountLastM_AccountID = a.M_AccountID
                        AND T_AccountLastIsActive = 'Y'
                    LEFT JOIN m_accountgroup ON a.M_AccountM_AccountGroupID =M_AccountGroupID
                    LEFT JOIN m_account b ON b.M_AccountIsActive = 'Y' AND a.M_AccountCode LIKE CONCAT(b.M_AccountCode,'%') AND b.M_AccountCode <> a.M_AccountCode
                    LEFT JOIN m_accountmap pay ON M_AccountMapIsActive = 'Y' AND M_AccountMapType LIKE 'PAY%' AND M_AccountMapM_AccountID = a.M_AccountID
                    WHERE a.`M_AccountName` LIKE ?
                    AND a.`M_AccountIsActive` = 'Y'
                    ORDER BY a.M_AccountCode) x
                LEFT JOIN f_budgeting ON F_BudgetingM_AccountID = account_id
                    AND F_BudgetingMonth BETWEEN ? AND ?
                    AND F_BudgetingYear BETWEEN ? AND ?
                    AND F_BudgetingIsActive = 'Y'

                LEFT JOIN (
                    SELECT M_AccountID jacc_id, date_format(T_JournalDate, '%m') jmonth,
                        IF(M_AccountPos = 'D', SUM(T_JournalDetailDebit - T_JournalDetailCredit),
                        SUM(T_JournalDetailCredit - T_JournalDetailDebit)) jactual
                    FROM t_journaldetail 
                    JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
                    JOIN t_journal ON T_JournalDetailt_journalid = T_JournalID
                        AND T_JournalDate BETWEEN ? AND ? AND T_JournalIsActive = 'Y'
                    WHERE T_JournalDetailIsACtive = 'Y' GROUP BY T_JournalDetailM_AccountID, left(T_JournalDate, 7)
                ) j ON jacc_id = account_id and f_budgetingmonth = jmonth

                GROUP BY account_id
                ORDER BY account_code ASC", [$d['search'], date('m', $sdate), date('m', $edate), date('Y', $sdate), date('Y', $edate), date('Y-m-d', $sdate), date('Y-m-d', $edate)]);

        $sa = [];
        $s = $this->db->query(
            "SELECT M_AccountID jacc_id, date_format(T_JournalDate, '%Y-%m') jmonth,
                    IF(M_AccountPos = 'D', SUM(T_JournalDetailDebit - T_JournalDetailCredit),
                    SUM(T_JournalDetailCredit - T_JournalDetailDebit)) jactual
                FROM t_journaldetail 
                JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
                JOIN t_journal ON T_JournalDetailt_journalid = T_JournalID
                    AND T_JournalDate BETWEEN ? AND ? AND T_JournalIsActive = 'Y'
                WHERE T_JournalDetailIsACtive = 'Y' 
            GROUP BY T_JournalDetailM_AccountID, left(T_JournalDate, 7)
            ORDER BY m_accountcode, left(T_JournalDate, 7)
            ", [date('Y-m-d', $sdate), date('Y-m-d', $edate)]);
        if ($s) $sa = $s->result_array();

        if ($r)
        {
            

            $r = $r->result_array();
            foreach ($r as $k => $v) { 
                $r[$k]['budgets'] = (array) json_decode($v['budgets']);
                $r[$k]['budget_dates'] = (array) json_decode($v['budget_dates']); 
            }
            
            if ($edate >= $sdate)
            {
                $tdate = $sdate;
                while ($tdate <= $edate)
                {
                    foreach ($r as $k => $v)
                    {   
                        if (array_search(date('Y-m', $tdate), $v['budget_dates']) === false)
                            $r[$k]['budgets'][] = ['date'=>date('Y-m', $tdate), 'budget'=>0, 'actual'=>0];
                    }
                    $tdate = strtotime("+1 month", $tdate);
                }
            }
            
            foreach ($r as $k => $v)
            { 
                foreach ($sa as $l => $w)
                { 
                    foreach ($v['budgets'] as $m => $n)
                    { 
                        $n  = (array) $n;
                        $w = (array) $w;
                        
                        if ($n['date'] == $w['jmonth'] && $v['account_id'] == $w['jacc_id']) {
                            // echo "{$n['date']}-{$w['jmonth']}-{$v['account_id']}/";
                            $n['actual'] = $w['jactual'];
                        }
                        $v['budgets'][$m] = $n;
                    }
                    $r[$k] = $v;
                }
            }


            $lx['records'] = $r;
            $lx['q'] = $this->db->last_query();

            // $l['query'] = $this->db->last_query();
        }
            
        return $lx;
    }

    function search2( $d )
    {
        $r = $this->db->query("SELECT a.M_AccountCode acc_code, a.M_AccountName acc_name, a.M_AccountParent acc_parent,
                b.M_AccountCode bcc_code, b.M_AccountName bcc_name, b.M_AccountParent bcc_parent,
                ga.M_AccountGroupCode ga_code, ga.M_AccountGroupName ga_name
                FROM m_account a
                JOIN m_accountgroup ga on a.m_accountm_accountgroupid = ga.m_accountgroupid

                JOIN m_accountreport ON ((M_AccountReportM_AccountID = M_AccountID AND M_AccountReportM_AccountID <> 0) 
                    OR (M_AccountReportM_AccountGroupID = ga.M_AccountGroupID AND M_AccountReportM_AccountGroupID <> 0))
                    AND M_ACcountReportType LIKE 'BUDGETING.%' AND M_AccountReportIsActive = 'Y'

                LEFT JOIN m_account b on a.m_accountcode like concat(b.m_accountcode, '%') 
                    and a.m_accountcode <> b.m_accountcode
                    and b.m_accountisactive = 'Y'

                WHERE a.M_AccountIsActive = 'Y'
                ORDER BY a.m_accountcode ASC");

        if ($r)
        {
            $r = $r->result_array();

            $l['records'] = $r;
            // $l['query'] = $this->db->last_query();
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

    function save_budget($d, $uid)
    {
        $a=$d['account']; 
        $y=$d['year']; 
        $m=$d['month']; 
        $b=$d['budget'];

        $this->db->query("INSERT INTO f_budgeting (F_BudgetingM_AccountID, F_BudgetingYear, F_BudgetingMonth, F_BudgetingBudget)
            SELECT * FROM (SELECT ?, ?, ?, ?) AS tmp
            WHERE NOT EXISTS (
                SELECT F_BudgetingID FROM f_budgeting 
                WHERE F_BudgetingM_AccountID = ? AND F_BudgetingYear = ? AND F_BudgetingMonth = ? AND F_BudgetingIsActive = 'Y'
            ) LIMIT 1;", [$a, $y, $m, $b, $a, $y, $m]);

        $r = $this->db->query("SELECT F_BudgetingID as id FROM f_budgeting 
            WHERE F_BudgetingM_AccountID = ? AND F_BudgetingYear = ? AND F_BudgetingMonth = ? AND F_BudgetingIsActive = 'Y'", [$a, $y, $m])->row();
        
        $this->db->query("UPDATE f_budgeting SET F_BudgetingBudget = ?
            WHERE F_BudgetingID = ?", [$b, $r->id]);

        return ["budgeting_id", $r->id];
    }

    function copy_to($d, $uid = 0)
    {
        $r = $this->db->query("CALL sp_finance_budgeting_copy_to(?, ?, ?)", [
            $d['year'], $d['month'], $uid
        ])
        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
}