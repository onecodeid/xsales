DROP FUNCTION `fn_finance_get_balance_monthly`;
DELIMITER ;;
CREATE FUNCTION `fn_finance_get_balance_monthly` (`sdate` date, `edate` date) RETURNS varchar(255) CHARACTER SET 'utf8mb4'
COLLATE utf8mb4_general_ci
BEGIN

DECLARE account_balance_id INTEGER;
DECLARE account_profit_id INTEGER;
DECLARE account_prive_id INTEGER;

DECLARE balance DOUBLE;
DECLARE profit DOUBLE;
DECLARE profits DOUBLE DEFAULT 0;
DECLARE prive DOUBLE;
DECLARE bdate DATE;
DECLARE profits_period VARCHAR(100) DEFAULT "";

SET account_balance_id = (SELECT fn_master_get_account_id("ACC.BALANCE"));
SET account_profit_id = (SELECT fn_master_get_account_id("ACC.PROFITLOSS"));
SET account_prive_id = (SELECT fn_master_get_account_id("ACC.PRIVE"));
SET bdate = DATE_SUB(sdate, INTERVAL 1 MONTH);

SET balance = (SELECT SUM(T_JournalDetailCredit - T_JournalDetailDebit)
            FROM t_journaldetail
            JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID 
                AND T_JournalDate BETWEEN DATE_FORMAT(sdate, "%Y-01-01") AND edate AND T_JournalIsActive = "Y"
            WHERE T_JournalDetailISActive = "Y" AND T_JournalDetailM_AccountID = account_balance_id);

SET prive = (SELECT SUM(T_JournalDetailDebit - T_JournalDetailCredit)
            FROM t_journaldetail
            JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID 
                AND T_JournalDate BETWEEN DATE_FORMAT(sdate, "%Y-01-01") AND edate AND T_JournalIsActive = "Y"
            WHERE T_JournalDetailISActive = "Y" AND T_JournalDetailM_AccountID = account_prive_id);

SET profit = (SELECT SUM(T_JournalCloseDetailCredit - T_JournalCloseDetailDebit)
            FROM t_journalclosedetail
            JOIN t_journalclose ON T_JournalCloseDetailT_JournalCloseID = T_JournalCloseID 
                AND T_JournalCloseDate BETWEEN sdate AND edate AND T_JournalCloseIsActive = "Y"
            WHERE T_JournalCloseDetailISActive = "Y" AND T_JournalCloseDetailM_AccountID = account_profit_id);

IF month(sdate) > 1 THEN
    SET profits = (SELECT SUM(T_JournalCloseDetailCredit - T_JournalCloseDetailDebit)
            FROM t_journalclosedetail
            JOIN t_journalclose ON T_JournalCloseDetailT_JournalCloseID = T_JournalCloseID 
                AND T_JournalCloseDate BETWEEN DATE_FORMAT(sdate, "%Y-01-01") AND sdate AND T_JournalCloseIsActive = "Y"
            WHERE T_JournalCloseDetailISActive = "Y" AND T_JournalCloseDetailM_AccountID = account_profit_id);
END IF;

IF month(bdate) = 1 THEN SET profits_period = DATE_FORMAT(bdate, "%M"); END IF;
IF month(bdate) > 1 THEN SET profits_period = CONCAT("Jan - ", DATE_FORMAT(bdate, "%b")); END IF;

RETURN JSON_OBJECT("balance", balance, "profit", profit, "profits", profits, "profits_period", profits_period, "prive", IFNULL(prive, 0));

END;;
DELIMITER ;