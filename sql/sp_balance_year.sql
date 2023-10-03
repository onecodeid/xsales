BEGIN

DECLARE balanceid INTEGER;
DECLARE minyear INTEGER;
DECLARE startyear INTEGER DEFAULT 2022;

DECLARE open_debit DOUBLE DEFAULT 0;
DECLARE open_credit DOUBLE DEFAULT 0;
DECLARE close_debit DOUBLE DEFAULT 0;
DECLARE close_credit DOUBLE DEFAULT 0;
DECLARE total_debit DOUBLE DEFAULT 0;
DECLARE total_credit DOUBLE DEFAULT 0;

SET minyear = (
    SELECT MIN(T_BalanceYear)
    FROM t_balance
    WHERE T_BalanceYear < pyear AND T_BalanceIsActive = "Y" AND T_BalanceM_AccountID = paccount
        AND T_BalanceIsLock = "Y"
);

IF minyear IS NULL THEN 
    SET minyear = startyear; 
    SELECT 0, 0 INTO open_credit, open_debit;

ELSE
    SET balanceid = (SELECT T_BalanceID FROM t_balance WHERE T_BalanceYear = minyear AND T_BalanceIsActive = "Y" AND T_BalanceM_AccountID = paccount);
    
    SELECT T_BalanceCloseCredit, T_BalanceCloseDebit 
        INTO open_credit, open_debit
        FROM t_balance 
        WHERE T_BalanceID = balanceid;

    SET minyear = minyear + 1;
END IF;

WHILE pyear >= minyear DO
    SET balanceid = (SELECT T_BalanceID FROM t_balance WHERE T_BalanceYear = minyear AND T_BalanceIsActive = "Y" AND T_BalanceM_AccountID = paccount);
    
    IF balanceid IS NULL THEN
        INSERT INTO t_balance(
            T_BalanceM_AccountID,
            T_BalanceYear,
            T_BalanceOpenCredit,
            T_BalanceOpenDebit)
        SELECT paccount, minyear, open_credit, open_debit;

        SET balanceid = (SELECT LAST_INSERT_ID());
        
    ELSE
        UPDATE t_balance
        SET T_BalanceOpenCredit = open_credit, T_BalanceOpenDebit = open_debit
        WHERE T_BalanceID = balanceid;

    END IF;

    -- GET TOTAL TRANSACTION FROM JOURNAL
    SELECT SUM(T_JournalDetailCredit), SUM(T_JournalDetailDebit)
        INTO total_credit, total_debit
    FROM t_journaldetail
    JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID
        AND LEFT(T_JournalDate, 4) = minyear
    WHERE T_JournalDetailIsActive = "Y" AND T_JournalDetailM_AccountID = paccount;

    IF total_credit IS NULL THEN SET total_credit = 0; END IF;
    IF total_debit IS NULL THEN SET total_debit = 0; END IF;
    -- END OF GET TOTAL

    SET close_credit = open_credit + total_credit;
    SET close_debit = open_debit + total_debit;

    IF close_credit >= close_debit THEN
        SET close_credit = close_credit - close_debit;
        SET close_debit = 0;
    ELSE
        SET close_debit = close_debit - close_credit;
        SET close_credit = 0;
    END IF;

    -- UPDATE CLOSE BALANCE
    UPDATE t_balance
    SET T_BalanceCloseCredit = close_credit, T_BalanceCloseDebit = close_debit
    WHERE T_BalanceID = balanceid;

    -- SET TO OPEN BALANCE
    SET open_credit = close_credit;
    SET open_debit = close_debit;

    SET minyear = minyear + 1;
END WHILE;

END