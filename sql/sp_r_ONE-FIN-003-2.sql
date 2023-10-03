DROP PROCEDURE `sp_r_ONE-FIN-003-2`;
DELIMITER ;;
CREATE PROCEDURE `sp_r_ONE-FIN-003-2` (IN `sdate` date, IN `edate` date)
BEGIN

SELECT M_ACcountReportTypeName sub_type, M_AccountReportTypeSubTitle sub_title,
    IF (account_id IS NULL, '[]', 
        CONCAT('[', GROUP_CONCAT(
            JSON_OBJECT('account_id', account_id, 'account_name', account_name, 'account_code', account_code,
            'journal_debit', journal_debit, 'journal_credit', journal_credit, 
            'journal_balance', IF(M_AccountReportTypeSide = 'D', 0 - journal_balance, journal_balance), 'report_type', report_type)
        ), ']') 
    ) details,
    SUM(IF(M_AccountReportTypeSide = 'D', 0 - journal_balance, journal_balance)) sub_total
FROM m_accountreporttype
LEFT JOIN (
    SELECT M_AccountID account_id, M_AccountName account_name, M_AccountCode account_code,
    SUM(T_JournalDetailDebit) journal_debit, SUM(T_JournalDetailCredit) journal_credit,
    SUM(T_JournalDetailCredit - T_JournalDetailDebit) journal_balance,
    ABS(SUM(T_JournalDetailCredit - T_JournalDetailDebit)) journal_balance_abs,
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
    ORDER BY FIELD(report_type, "INCOME.SALES", "INCOME.HPP", "INCOME.EXPENSE", "INCOME.OTHER"), account_code) x ON report_type = M_AccountReportTypeName

WHERE M_AccountReportTypeGroup = 'INCOME'
    
GROUP BY M_AccountReportTypeName
ORDER BY M_AccountReportTypeSort, account_code;

-- SET PROFIT LOSS CLOSING JOURNAL
CALL sp_journal_close_set2("PROFITLOSS.M", edate, 0);

END;;
DELIMITER ;

call `sp_r_ONE-FIN-003-2`('2023-01-01', '2023-01-31');
select m_accountid id, m_accountcode, m_accountname, m_accounttype from m_account where M_AccountCode like "5-50%" and m_accountisactive = "Y";
select t_journaldate, t_journaldebit, t_journalcredit
from t_journaldetail join t_journal on T_JournalDetailT_JournalID = T_JournalID and T_JournalDate between "2023-01-01" and "2023-01-31" and T_JournalIsActive = "Y"
where T_JournalDetailIsActive = "Y" and T_JournalDetailM_AccountID = 46;
