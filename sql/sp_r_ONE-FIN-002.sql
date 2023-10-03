BEGIN

DECLARE years CHAR(4);
DECLARE bdebit DOUBLE;
DECLARE bcredit DOUBLE;
DECLARE tdebit DOUBLE;
DECLARE tcredit DOUBLE;

SET years = year(sdate);


SELECT IFNULL(T_BalanceOpenDebit, 0) balance_debit, IFNULL(T_BalanceOpenCredit, 0) balance_credit, IFNULL(trans_debit, 0) trans_debit, IFNULL(trans_credit, 0) trans_credit,
M_AccountCode account_code, M_AccountName account_name, M_AccountType account_type
FROM t_balance
JOIN m_account ON t_balancem_accountid = m_accountid
LEFT JOIN (

SELECT T_JournalDetailM_AccountID acc_id, IFNULL(SUM(T_JournalDetailDebit), 0) trans_debit, IFNULL(SUM(T_JournalDetailCredit), 0) trans_credit
FROM t_journaldetail
JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalDate >= CONCAT(years, "-01-01") AND T_JournalDate < sdate AND T_JournalIsActive = "Y"
WHERE T_JournalDetailIsActive = "Y"

GROUP BY T_JournalDetailM_AccountID

) x on acc_id = t_balancem_accountid
WHERE T_BalanceIsActive = "Y" AND T_BalanceYear = years
AND ((T_BalanceM_AccountID = account_id AND account_id <> 0) OR account_id = 0);

SELECT T_JournalType journal_type, IFNULL(M_JournalTypeName, '') journal_type_name, T_JournalDate journal_date, T_journalNumber journal_number,
T_JournalDetailID detail_id, T_JournalDetailLedgerNote ledger_note,
T_JournalDetailDebit journal_debit, T_JournalDetailCredit journal_credit,
M_AccountCode account_code, M_AccountName account_name, M_AccountType account_type, M_AccountGroupName group_name, M_AccountGroupCode group_code
FROM t_journaldetail
JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalDate BETWEEN sdate AND edate AND T_JournalIsActive = "Y" AND T_JournalType <> "J.30"
JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
LEFT JOIN m_accountgroup ON M_AccountM_AccountGroupID = M_AccountGroupID
LEFT JOIN m_journaltype ON T_JournalType = M_JournalTypeCode AND M_JournalTypeIsActive = "Y"
WHERE T_JournalDetailIsActive = "Y"
AND ((T_JournalDetailM_AccountID = account_id AND account_id <> 0) OR account_id = 0)
ORDER BY M_AccountCode, T_JournalDate ASC, T_JournalNumber ASC;

END