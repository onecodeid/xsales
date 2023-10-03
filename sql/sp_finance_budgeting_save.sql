DROP PROCEDURE `sp_finance_budgeting_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_budgeting_save` (IN `jdata` text, IN `uid` text)
BEGIN

DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;
DECLARE tmp TEXT;
DECLARE account INTEGER;
DECLARE budget DOUBLE;
DECLARE datex VARCHAR(10);
DECLARE date_month VARCHAR(2);
DECLARE date_year VARCHAR(4);

DECLARE b_id INTEGER;

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

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
    SET tmp = JSON_EXTRACT(jdata, CONCAT("$[", n ,"]"));
    SET n = n + 1;

    SET account = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.account"));
    SET budget = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.budget"));
    SET datex = CONCAT(JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.date")), "-01");
    SET date_month = DATE_FORMAT(datex, "%m");
    SET date_year = DATE_FORMAT(datex, "%Y");

    SET b_id = (SELECT F_BudgetingID FROM f_budgeting WHERE F_BudgetingMonth = date_month AND F_BudgetingYear = date_year 
                AND F_BudgetingM_AccountID = account AND F_BudgetingIsActive = "Y");
    IF b_id IS NULL THEN
        INSERT INTO f_budgeting(F_BudgetingMonth, F_BudgetingYear, F_BudgetingM_AccountID, F_BudgetingBudget)
        SELECT date_month, date_year, account, budget;

        SET b_id = (SELECT LAST_INSERT_ID());
    ELSE
        UPDATE f_budgeting SET F_BudgetingBudget = budget WHERE F_BudgetingID = b_id;
    END IF;
END WHILE;

SELECT "OK" as status, JSON_OBJECT("year", date_year) as data;

COMMIT;

END;;
DELIMITER ;