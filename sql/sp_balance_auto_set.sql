BEGIN

DECLARE open_credit DOUBLE;
DECLARE open_debit DOUBLE;
DECLARE close_credit DOUBLE;
DECLARE close_debit DOUBLE;

DECLARE account_modal_id INTEGER;
DECLARE account_id INTEGER;
DECLARE journal_id INTEGER;
DECLARE adata VARCHAR(2000);
DECLARE bnumber VARCHAR(25);
DECLARE account_type CHAR(1);
DECLARE balance_id INTEGER;
DECLARE balance_debit DOUBLE;
DECLARE balance_credit DOUBLE;

DECLARE finished INTEGER DEFAULT 0;
DECLARE d_cursor
    CURSOR FOR
SELECT M_AccountID, M_AccountType, T_BalanceID, IFNULL(jdebit,0), IFNULL(jcredit,0)
FROM m_account
LEFT JOIN t_balance ON T_BalanceM_AccountID = M_AccountID AND T_BalanceIsActive = "Y" AND T_BalanceYear = pyear
LEFT JOIN (
    SELECT SUM(T_JournalDetailDebit) jdebit, SUM(T_JournalDetailCredit) jcredit, T_JournalDetailM_AccountID jacc
    FROM t_journaldetail
    JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID
        AND T_JournalIsActive = "Y"
        AND year(T_JournalDate) = (pyear - 1)
    WHERE T_JournalDetailIsActive = "Y"
    GROUP BY T_JournalDetailM_AccountID
) j on jacc = M_AccountID
WHERE M_AccountIsActive = "Y"
ORDER BY M_AccountCode;

-- DECLARE EXIT HANDLER FOR SQLEXCEPTION
-- BEGIN

-- GET DIAGNOSTICS CONDITION 1
-- @p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
-- SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

-- ROLLBACK;
-- END;

-- DECLARE EXIT HANDLER FOR SQLWARNING
-- BEGIN

-- GET DIAGNOSTICS CONDITION 1
-- @p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
-- SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

-- ROLLBACK;
-- END;

-- START TRANSACTION;

SET account_modal_id = (SELECT fn_master_get_account_id("ACC.BALANCE"));

OPEN d_cursor;
getD: LOOP

    FETCH d_cursor INTO account_id, account_type, balance_id, balance_debit, balance_credit;

    IF finished = 1 THEN
        LEAVE getD;
    END IF;

    IF balance_id IS NULL THEN
        SET bnumber = (SELECT fn_numbering('BAL'));

        INSERT INTO t_balance(T_BalanceNumber, T_BalanceM_AccountID, T_BalanceYear, T_BalanceOpenCredit, T_BalanceOpenDebit, T_BalanceCloseCredit, T_BalanceCloseDebit) 
        SELECT bnumber, account_id, pyear, balance_credit, balance_debit, 0, 0;
    ELSE
        UPDATE t_balance SET T_BalanceOpenCredit = balance_credit, T_BalanceOpenDebit = balance_debit WHERe T_BalanceID = balance_id;
    END IF;

END LOOP getD;
CLOSE d_cursor;


-- COMMIT;

END