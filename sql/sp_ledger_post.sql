BEGIN

DECLARE finished INTEGER DEFAULT 0;
DECLARE journal_id INTEGER;
DECLARE main_account_id INTEGER;
DECLARE journal_credit DOUBLE;
DECLARE journal_debit DOUBLE;
DECLARE ledger_id INTEGER;

DECLARE journal_cursor
    CURSOR FOR
        SELECT DISTINCT(T_JournalID) journal_id, T_JournalMainM_AccountID,
            T_JournalDetailCredit, T_JournalDetailDebit
        FROM t_journaldetail
        JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID
        WHERE T_JournalDetailM_AccountID = account_id
        AND T_JournalDetailIsActive = "Y"
        AND T_JournalDetailPost = "N"
        AND YEAR(T_JournalDate) = pyear AND MONTH(T_JournalDate) = pmonth
        ORDER BY T_JournalDate, T_JournalID;

-- declare NOT FOUND handler
DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET finished = 1;

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

INSERT INTO t_ledger(T_LedgerYear,
                T_LedgerMonth,
                T_LedgerM_AccountID)
SELECT pyear, pmonth, account_id;
SET ledger_id = (SELECT LAST_INSERT_ID());

OPEN journal_cursor;

getJournal: LOOP
    FETCH journal_cursor INTO journal_id, main_account_id, journal_credit, journal_debit;
    IF finished = 1 THEN
        LEAVE getJournal;
    END IF;

    -- action
    IF main_account_id = account_id THEN
        INSERT INTO t_ledgerdetail(
            T_LedgerDetailT_LedgerID,
            T_LedgerDetailDate,
            T_LedgerDetailM_AccountID,
            T_LedgerDetailCredit,
            T_LedgerDetailDebit)
        SELECT ledger_id, T_JournalDate, T_JournalDetailM_AccountID, T_JournalDetailDebit, T_JournalDetailCredit
        FROM t_journaldetail
        JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID
        WHERE T_JournalDetailT_JournalID = journal_id
        AND T_JournalDetailIsActive = "Y" ANd T_JournalDetailM_AccountID <> account_id;
    ELSE
        INSERT INTO t_ledgerdetail(
            T_LedgerDetailT_LedgerID,
            T_LedgerDetailDate,
            T_LedgerDetailM_AccountID,
            T_LedgerDetailCredit,
            T_LedgerDetailDebit)
        SELECT ledger_id, T_JournalDate, T_JournalDetailM_AccountID, T_JournalDetailDebit, T_JournalDetailCredit
        FROM t_journaldetail
        JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID
        WHERE T_JournalDetailT_JournalID = journal_id
        AND T_JournalDetailIsActive = "Y" 
        ANd T_JournalDetailCredit = journal_debit AND T_JournalDetailDebit = journal_credit;
    END IF;
END LOOP getJournal;

CLOSE journal_cursor;

SELECT "OK" status, JSON_OBJECT("ledger_id", ledger_id) data;
COMMIT;

END