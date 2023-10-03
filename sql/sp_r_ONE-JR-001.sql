BEGIN

DECLARE acc INTEGER;
DECLARE acc_payable INTEGER;
DECLARE acc_disc INTEGER;

set acc = (SELECT M_AccountID FROM m_account WHERE M_AccountCode = "1-1000101");
set acc_payable = (SELECT M_AccountID FROM m_account WHERE M_AccountCode = "1-10100");
set acc_disc = (SELECT M_AccountID FROM m_account WHERE M_AccountCode = "4-40100");

select t_journalid, t_journaldate, t_journaltype,
sum(if(t_journaldetailm_accountid=acc, t_journaldetaildebit, 0)) "cash",
sum(if(t_journaldetailm_accountid=acc_payable, t_journaldetailcredit, 0)) "payable",
sum(if(t_journaldetailm_accountid=acc_disc, t_journaldetaildebit, 0)) "disc",
sum(if(t_journaldetailm_accountid<>acc_disc and t_journaldetailm_accountid<>acc and t_journaldetailm_accountid<>acc_payable, t_journaldetailcredit, 0)) "others",
group_concat(if(t_journaldetailm_accountid<>acc_disc and t_journaldetailm_accountid<>acc and t_journaldetailm_accountid<>acc_payable, m_accountname, '') separator '') "account_name",
t_journalrefnote journal_ref_note
from t_journal
join t_journaldetail on t_journalid = t_journaldetailt_journalid and t_journaldetailisactive = "Y"
join m_account on t_journaldetailm_accountid = m_accountid
where t_journalaccounts like concat("%[",acc,"-D]%")
and t_journalisactive = "Y"
and t_journaldate between sdate and edate
group by t_journalid
order by t_journaldate asc;

END