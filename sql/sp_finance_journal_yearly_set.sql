DROP PROCEDURE `sp_finance_journal_yearly_set`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_journal_yearly_set` (IN `pyear` char(4), IN `pmonth` char(2), IN `jdata` text)
BEGIN

DECLARE balance_start DOUBLE;
DECLARE balance_end DOUBLE;
DECLARE profit DOUBLE;
DECLARE prive DOUBLE;
DECLARE jid INTEGER;
DECLARE jnextid INTEGER;
DECLARE dnext DATE;

SET jid = (SELECT T_JournalYearlyID FROM t_journalyearly WHERE T_JournalYearlyYear = pyear AND T_JournalYearlyMonth = pmonth AND T_JournalYearlyIsActive = "Y" LIMIT 1);
SET dnext = (SELECT DATE_ADD(CONCAT(pyear, '-', pmonth, '-01'), INTERVAL 1 MONTH));
SET jnextid = (SELECT T_JournalYearlyID FROM t_journalyearly WHERE T_JournalYearlyYear = year(dnext) AND T_JournalYearlyMonth = date_format(dnext, '%m') AND T_JournalYearlyIsActive = "Y");

SET balance_start = JSON_UNQUOTE(JSON_EXTRACT(jdata, '$.balance_start'));
SET profit = JSON_UNQUOTE(JSON_EXTRACT(jdata, '$.profit'));
SET prive = JSON_UNQUOTE(JSON_EXTRACT(jdata, '$.prive'));

IF jid IS NULL THEN
    IF balance_start IS NULL THEN SET balance_start = 0; END IF;
    IF profit IS NULL THEN SET profit = 0; END IF;
    IF prive IS NULL THEN SET prive = 0; END IF;

    SET balance_end = balance_start + profit;

    INSERT INTO t_journalyearly(T_JournalYearlyYear,
    T_JournalYearlyMonth,
    T_JournalYearlyBalanceStart,
    T_JournalYearlyProfit,
    T_JournalYearlyPrive,
    T_JournalYearlyBalanceEnd)
    SELECT pyear, pmonth, balance_start, profit, prive, balance_end;

    SET jid = (SELECT LAST_INSERT_ID());

    INSERT INTO t_journalyearly(T_JournalYearlyYear,
    T_JournalYearlyMonth,
    T_JournalYearlyBalanceStart,
    T_JournalYearlyProfit,
    T_JournalYearlyPrive,
    T_JournalYearlyBalanceEnd)
    SELECT year(dnext), date_format(dnext, '%m'), balance_end, 0, 0, balance_end;

    SET jnextid = (SELECT LAST_INSERT_ID());
ELSE
    IF balance_start IS NOT NULL THEN
        UPDATE t_journalyearly SET T_JournalYearlyBalanceStart = balance_start
        WHERE T_JournalYearlyID = jid;
    END IF;

    IF profit IS NOT NULL THEN
        UPDATE t_journalyearly SET T_JournalYearlyProfit = profit
        WHERE T_JournalYearlyID = jid;
    END IF;

    IF prive IS NOT NULL THEN
        UPDATE t_journalyearly SET T_JournalYearlyPrive = prive
        WHERE T_JournalYearlyID = jid;
    END IF;

    UPDATE t_journalyearly SET T_JournalYearlyBalanceEnd = T_JournalYearlyBalanceStart + T_JournalYearlyProfit - T_JournalYearlyPrive
    WHERE T_JournalYearlyID = jid;
    
    SET balance_end = (SELECT T_JournalYearlyBalanceEnd FROM t_journalyearly WHERE T_JournalYearlyID = jid);

    IF jnextid IS NULL THEN
        INSERT INTO t_journalyearly(T_JournalYearlyYear,
        T_JournalYearlyMonth,
        T_JournalYearlyBalanceStart,
        T_JournalYearlyProfit,
        T_JournalYearlyBalanceEnd)
        SELECT year(dnext), date_format(dnext, '%m'), balance_end, 0, balance_end;
    ELSE
        UPDATE t_journalyearly SET T_JournalYearlyBalanceStart = balance_end, T_JournalYearlyBalanceEnd = balance_end + T_JournalYearlyProfit - T_JournalYearlyPrive
        WHERE T_JournalYearlyID = jnextid;
    END IF;
    
END IF;

SELECT "OK" status, JSON_OBJECT("journal_id", jid, "journal_next_id", jnextid) data;

END;;
DELIMITER ;