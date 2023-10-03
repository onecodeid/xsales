DROP PROCEDURE `sp_r_ONE-FIN-009`;
DELIMITER ;;
CREATE PROCEDURE `sp_r_ONE-FIN-009` (IN `pyear` char(4))

BEGIN

SELECT T_JournalYearlyID journal_id, T_JournalYearlyBalanceStart balance_start, T_JournalYearlyBalanceEnd balance_end,
T_JournalYearlyProfit profit, T_JournalYearlyPrive prive
FROM t_journalyearly
WHERE T_JournalYearlyYear = pyear;

END;;
DELIMITER ;