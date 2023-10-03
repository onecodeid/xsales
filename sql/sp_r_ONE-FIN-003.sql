DROP PROCEDURE `sp_r_ONE-FIN-003`;
DELIMITER ;;
CREATE PROCEDURE `sp_r_ONE-FIN-003` (IN `sdate` date, IN `edate` date)
BEGIN

SELECT M_AccountID account_id, M_AccountName account_name, M_AccountCode account_code,
SUM(T_JournalDetailDebit) journal_debit, SUM(T_JournalDetailCredit) journal_credit,
SUM(IF(M_AccountType = 'A', T_JournalDetailDebit - T_JournalDetailCredit, T_JournalDetailCredit - T_JournalDetailDebit)) journal_balance,
M_ACcountReportType report_type
FROM t_journaldetail
JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalIsActive = "Y" AND T_JournalDate BETWEEN sdate AND edate
JOIN m_accountreport ON 
((M_AccountReportM_AccountID = T_JournalDetailM_AccountID AND M_AccountReportM_AccountID <> 0) OR 
(M_AccountReportM_AccountGroupID = M_AccountM_AccountGroupID AND M_AccountReportM_AccountGroupID <> 0))
AND M_ACcountReportType LIKE "INCOME.%" AND M_AccountReportIsActive = "Y"

WHERE T_JournalDetailIsActive = "Y"
GROUP BY M_AccountID
ORDER BY FIELD(report_type, "INCOME.SALES", "INCOME.HPP", "INCOME.EXPENSE", "INCOME.OTHER"), account_code;

END;;
DELIMITER ;