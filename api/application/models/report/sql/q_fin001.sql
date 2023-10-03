SELECT group_id, group_code, group_name,
CONCAT('[', GROUP_CONCAT(JSON_OBJECT('account_id', account_id, 'account_code', account_code, 'account_name', account_name, 'account_pos', account_pos,
    'parent_code', parent_code, 'level', level, 'group_id', group_id, 'group_name', group_name,
    'j_debit', j_debit, 'j_credit', j_credit, 'b_credit', b_credit, 'b_debit', b_debit,
    'trans_debit', trans_debit, 'trans_credit', trans_credit) ORDER BY account_code ASC), ']') details
FROM (

select a.m_accountid account_id, a.m_accountcode account_code, a.m_accountname account_name,
b.m_accountcode parent_code, count(b.m_accountid) level, a.M_AccountPos account_pos,
m_accountgroupcode group_code, m_accountgroupname group_name, M_AccountGroupID group_id,
IFNULL(j_debit, 0) j_debit, IFNULL(j_credit, 0) j_credit,
(balance_credit + trans_credit) b_credit, (balance_debit + trans_debit) b_debit,
trans_debit, trans_credit

from m_account a
join m_accountgroup on a.m_accountm_accountgroupid = m_accountgroupid
left join m_account b on a.m_accountcode like concat(b.m_accountcode, '%') 
and a.m_accountcode <> b.m_accountcode
and b.m_accountisactive = "Y"

left join (
select t_journaldetailm_accountid jacc_id, sum(t_journaldetaildebit) j_debit, sum(t_journaldetailcredit) j_credit
from t_journaldetail
join t_journal on t_journaldetailt_journalid = t_journalid
and t_journalisactive = "Y" and t_journaldate between ? and ?
and t_journaltype <> "J.30"
where (T_JournalDetailIsActive = 'Y' OR T_JournalDetailIsActive = 'A')
group by t_journaldetailm_accountid
) j on jacc_id = a.m_accountid

LEFT JOIN (
        SELECT IFNULL(SUM(IF(T_JournalType = 'J.30', T_JournalDetailDebit, 0)), 0) balance_debit, IFNULL(SUM(IF(T_JournalType = 'J.30', T_JournalDetailCredit, 0)), 0) balance_credit, 
            IFNULL(SUM(IF(T_JournalType <> 'J.30' AND T_JournalDate <> ?, T_JournalDetailDebit, 0)), 0) trans_debit, 
            IFNULL(SUM(IF(T_JournalType <> 'J.30' AND T_JournalDate <> ?, T_JournalDetailCredit, 0)), 0) trans_credit,
            M_AccountID baccount_id, M_AccountCode baccount_code, M_AccountName baccount_name, M_AccountType baccount_type
        FROM t_journaldetail
        JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalDate >= CONCAT(?, '-01-01') AND T_JournalDate <= ? AND (T_JournalIsActive = 'Y' OR T_JournalIsActive = 'A')
        JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
        WHERE (T_JournalDetailIsActive = 'Y' OR T_JournalDetailIsActive = 'A')
        GROUP BY T_JournalDetailM_AccountID
    ) blc  ON baccount_id = a.M_AccountID

-- left join t_balance on t_balancem_accountid = a.m_accountid and t_balanceisactive = "Y" and t_balanceyear = ?

where a.m_accountisactive = "Y" and a.m_accountname like ?
group by a.m_accountid
order by a.m_accountcode asc) xyz
WHERE j_debit > 0 OR j_credit > 0 OR b_debit > 0 OR b_credit > 0


GROUP BY group_id
ORDER BY group_code ASC;