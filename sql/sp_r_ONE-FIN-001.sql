BEGIN

DECLARE years VARCHAR(4);

set years = year(edate);

CALL sp_balance_auto_set(years);

select a.m_accountid account_id, a.m_accountcode account_code, a.m_accountname account_name,
b.m_accountcode parent_code, count(b.m_accountid) level,
m_accountgroupcode group_code, m_accountgroupname group_name, j_debit, j_credit,
ifnull(t_balanceopencredit, 0) b_credit, ifnull(t_balanceopendebit, 0) b_debit

from m_account a
join m_accountgroup on a.m_accountm_accountgroupid = m_accountgroupid
left join m_account b on a.m_accountcode like concat(b.m_accountcode, '%') 
and a.m_accountcode <> b.m_accountcode
and b.m_accountisactive = "Y"

left join (
select t_journaldetailm_accountid jacc_id, sum(t_journaldetaildebit) j_debit, sum(t_journaldetailcredit) j_credit
from t_journaldetail
join t_journal on t_journaldetailt_journalid = t_journalid
and t_journalisactive = "Y" and t_journaldate between sdate and edate
where t_journaldetailisactive = "Y"
group by t_journaldetailm_accountid
) j on jacc_id = a.m_accountid

left join t_balance on t_balancem_accountid = a.m_accountid and t_balanceisactive = "Y" and t_balanceyear = years

where a.m_accountisactive = "Y"
group by a.m_accountid
order by a.m_accountcode asc;

END