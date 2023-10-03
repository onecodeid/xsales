DROP PROCEDURE `sp_journal_close_set2`;
DELIMITER ;;
CREATE PROCEDURE `sp_journal_close_set2` (IN `what` varchar(100), IN `pdate` date, IN `amount` double)
BEGIN

DECLARE sdate DATE;
DECLARE edate DATE;

DECLARE account_profitloss_id INTEGER;
DECLARE account_income_id INTEGER;
DECLARE account_earning_retained_id INTEGER;
DECLARE account_cost_id INTEGER;
DECLARE journal_id INTEGER;
DECLARE adata TEXT;

DECLARE amount_earning_retained DOUBLE;
DECLARE amount_cost DOUBLE;
DECLARE amount_income DOUBLE;
DECLARE amount_profit DOUBLE;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;
ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN
GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;
ROLLBACK;
END;

START TRANSACTION;

SET account_profitloss_id = (SELECT fn_master_get_account_id("ACC.PROFITLOSS"));
SET account_income_id = (SELECT fn_master_get_account_id("ACC.INCOME"));
SET account_earning_retained_id = (SELECT fn_master_get_account_id("ACC.EARNING.RETAINED"));
SET account_cost_id = (SELECT fn_master_get_account_id("ACC.COST"));

IF what = 'PROFITLOSS.M' THEN
    
    SET sdate = DATE_FORMAT(pdate, "%Y-%m-01");
    SET edate = LAST_DAY(sdate);
    SET journal_id = (SELECT T_JournalCloseID FROM t_journalclose WHERE T_JournalCloseType = "J.80" AND T_JournalCloseIsActive = "Y" AND T_JournalCloseDate = pdate);

    SET amount_income = (SELECT SUM(T_JournalDetailCredit - T_JournalDetailDebit)
        FROM t_journaldetail
        JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
        JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalIsActive = "Y" AND T_JournalDate BETWEEN sdate AND edate
        JOIN m_accountreport ON 
        ((M_AccountReportM_AccountID = T_JournalDetailM_AccountID AND M_AccountReportM_AccountID <> 0) OR 
        (M_AccountReportM_AccountGroupID = M_AccountM_AccountGroupID AND M_AccountReportM_AccountGroupID <> 0))
        AND (M_ACcountReportType = "INCOME.SALES" OR M_ACcountReportType = "INCOME.HPP" OR M_ACcountReportType = "INCOME.OTHER") AND M_AccountReportIsActive = "Y"
        WHERE T_JournalDetailIsActive = "Y");

    SET amount_cost = (SELECT SUM(T_JournalDetailDebit - T_JournalDetailCredit)
        FROM t_journaldetail
        JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
        JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalIsActive = "Y" AND T_JournalDate BETWEEN sdate AND edate
        JOIN m_accountreport ON 
        ((M_AccountReportM_AccountID = T_JournalDetailM_AccountID AND M_AccountReportM_AccountID <> 0) OR 
        (M_AccountReportM_AccountGroupID = M_AccountM_AccountGroupID AND M_AccountReportM_AccountGroupID <> 0))
        AND (M_ACcountReportType = "INCOME.EXPENSE" OR M_ACcountReportType = "INCOME.EXPENSE.OTHER") AND M_AccountReportIsActive = "Y"
        WHERE T_JournalDetailIsActive = "Y");

    SET amount_profit = amount_income - amount_cost;

    IF journal_id IS NULL THEN SET journal_id = 0; END IF;
    SET adata = CONCAT("[", JSON_OBJECT("account", account_income_id, "debit", amount_income, "credit", 0), ",",
            JSON_OBJECT("account", account_cost_id, "debit", 0, "credit", amount_cost), ",",
            JSON_OBJECT("account", account_profitloss_id, "debit", 0, "credit", amount_profit), "]");

    CALL sp_journal_close_save_notrans(journal_id, pdate, '', 'Ikhtisar Laba Rugi', adata, "J.80", account_profitloss_id );
    
    IF journal_id = 0 THEN
        SET journal_id = (SELECT MAX(T_JournalCloseID) FROM t_journalclose 
            WHERE T_JournalCloseReceipt = '' AND T_JournalCloseIsActive = "Y" ANd T_JournalCloseType = "J.80" AND T_JournalCloseDate = pdate);
    END IF;

    SELECT "OK" status, JSON_OBJECT("journal_id", journal_id) data;
END IF;

COMMIT;

END;;
DELIMITER ;