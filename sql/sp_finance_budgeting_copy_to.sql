DROP PROCEDURE `sp_finance_budgeting_copy_to`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_budgeting_copy_to` (IN `pyear` char(4), IN `pmonth` char(2), IN `uid` int)
BEGIN

DECLARE prev_date DATE;
DECLARE prev_month CHAR(2);
DECLARE prev_year CHAR(4);

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

SET prev_date = DATE_SUB(CONCAT(pyear,"-",pmonth,"-01"), INTERVAL 1 MONTH);
SET prev_month = DATE_FORMAT(prev_date, "%m");
SET prev_year = DATE_FORMAT(prev_date, "%Y");

UPDATE f_budgeting a
JOIN f_budgeting b ON a.F_BudgetingM_AccountID = b.F_BudgetingM_AccountID
    AND b.F_BudgetingYear = prev_year AND b.F_BudgetingMonth = prev_month AND b.F_BudgetingIsActive = 'Y'
SET a.F_BudgetingBudget = b.F_BudgetingBudget
WHERE a.F_BudgetingYear = pyear AND a.F_BudgetingMonth = pmonth AND a.F_BudgetingIsActive = "Y";

UPDATE f_budgeting a
LEFT JOIN f_budgeting b ON a.F_BudgetingM_AccountID = b.F_BudgetingM_AccountID
    AND b.F_BudgetingYear = prev_year AND b.F_BudgetingMonth = prev_month AND b.F_BudgetingIsActive = 'Y'
SET a.F_BudgetingBudget = 0
WHERE a.F_BudgetingYear = pyear AND a.F_BudgetingMonth = pmonth AND a.F_BudgetingIsActive = "Y" AND b.F_BudgetingID IS NULL;

INSERT INTO f_budgeting (F_BudgetingM_AccountID, F_BudgetingYear, F_BudgetingMonth, F_BudgetingBudget)
SELECT b.F_BudgetingM_AccountID, pyear, pmonth, b.F_BudgetingBudget
FROM f_budgeting b 
WHERE b.F_BudgetingYear = prev_year AND b.F_BudgetingMonth = prev_month AND b.F_BudgetingIsActive = 'Y'
    AND b.F_BudgetingM_AccountID NOT IN (
        SELECT F_BudgetingM_AccountID
        FROM f_budgeting
        WHERE F_BudgetingYear = pyear AND F_BudgetingMonth = pmonth AND F_BudgetingIsActive = "Y"
    );

SELECT "OK" as status, JSON_OBJECT("year", pyear, "month", pmonth) as data;

COMMIT;

END;;
DELIMITER ;