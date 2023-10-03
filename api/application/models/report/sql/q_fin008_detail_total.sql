SELECT count(t_journalid) as n
FROM t_journaldetail
JOIN t_journal on t_journaldetailt_journalid = t_journalid 
and t_journaldate between ? and ? and t_journalisactive = "Y"
and t_journalcashflow = ?
JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID and M_AccountM_AccountGroupID = 1
where t_journaldetailisactive = "Y" and (m_accountname like ? or t_journaldetailledgernote like ? or t_journalnumber like ?)