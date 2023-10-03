DROP PROCEDURE `sp_r_ONE-FIN-005`;
DELIMITER ;;
CREATE PROCEDURE `sp_r_ONE-FIN-005` (IN `sdate` date, IN `edate` date)
BEGIN

DECLARE years VARCHAR(4);

set years = year(edate);
set sdate = DATE_FORMAT(sdate, "%Y-01-01");

-- CALL sp_balance_auto_set(years);

-- SELECT > 0
SELECT *,
(IF(account_type = "A", b_debit - b_credit + j_debit - j_credit, b_credit - b_debit + j_credit - j_debit) - accum_amount) journal_balance FROM (

select a.m_accountid account_id, a.m_accountcode account_code, a.m_accountname account_name,
b.m_accountcode parent_code, b.m_accountname parent_name, count(b.m_accountid) level,
m_accountgroupcode group_code, m_accountgroupname group_name, 
sum(IFNULL(j_debit, 0)) j_debit, sum(IFNULL(j_credit, 0)) j_credit,
0 b_credit, 
0 b_debit,
IFNULL(accum_amount, 0) accum_amount,
-- sum(ifnull(t_balanceopencredit, 0)) b_credit, 
-- sum(ifnull(t_balanceopendebit, 0)) b_debit,
M_AccountReportType report_type, M_AccountReportTitle report_title,
a.M_AccountType account_type

from m_account a
join m_accountgroup on a.m_accountm_accountgroupid = m_accountgroupid

JOIN m_accountreport ON ((M_AccountReportM_AccountID = M_AccountID AND M_AccountReportM_AccountID <> 0) OR (M_AccountReportM_AccountGroupID = M_AccountGroupID AND M_AccountReportM_AccountGroupID <> 0))
AND M_ACcountReportType LIKE "BALANCE.%" AND M_AccountReportIsActive = "Y"

left join m_account b on a.m_accountcode like concat(b.m_accountcode, '%') 
and a.m_accountcode <> b.m_accountcode
and b.m_accountisactive = "Y"

left join (
select t_journaldetailm_accountid jacc_id, sum(t_journaldetaildebit) j_debit, sum(t_journaldetailcredit) j_credit
from t_journaldetail
join t_journal on t_journaldetailt_journalid = t_journalid
and t_journalisactive = "Y" and t_journaldate between sdate and edate
-- and t_journaltype <> "J.30"
where t_journaldetailisactive = "Y"
group by t_journaldetailm_accountid
) j on jacc_id = a.m_accountid

left join (
select m_accumulationm_accountid accum_acc_id, sum(t_journaldetailcredit-t_journaldetaildebit) accum_amount
from t_journaldetail
join t_journal on t_journaldetailt_journalid = t_journalid
and t_journalisactive = "Y" and t_journaldate between sdate and edate
join m_accumulation on t_journaldetailm_accountid = m_accumulationaccm_accountid
-- and t_journaltype <> "J.30"
where t_journaldetailisactive = "Y"
group by m_accumulationaccm_accountid
) accum on accum_acc_id = a.m_accountid

left join t_balance on t_balancem_accountid = a.m_accountid and t_balanceisactive = "Y" and t_balanceyear = years

where a.m_accountisactive = "Y"
group by left(a.m_accountcode, 7)
order by a.m_accountcode asc) xyz
WHERE j_debit > 0 OR j_credit > 0;

END;;
DELIMITER ;