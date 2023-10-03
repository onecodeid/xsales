DROP PROCEDURE `sp_balance_set`;
DELIMITER ;;
CREATE PROCEDURE `sp_balance_set` (IN `account_id` int, IN `pyear` char(4), IN `jdata` varchar(2000))
BEGIN

DECLARE balance_id INTEGER;
DECLARE open_credit DOUBLE;
DECLARE open_debit DOUBLE;
DECLARE close_credit DOUBLE;
DECLARE close_debit DOUBLE;

DECLARE account_modal_id INTEGER;
DECLARE journal_id INTEGER;
DECLARE adata VARCHAR(2000);
DECLARE bnumber VARCHAR(25);
DECLARE actype CHAR(1);

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

SET account_modal_id = (SELECT fn_master_get_account_id("ACC.BALANCE"));

SET open_credit = JSON_UNQUOTE(JSON_EXTRACT(jdata, '$.open_credit'));
SET open_debit = JSON_UNQUOTE(JSON_EXTRACT(jdata, '$.open_debit'));
SET close_credit = JSON_UNQUOTE(JSON_EXTRACT(jdata, '$.close_credit'));
SET close_debit = JSON_UNQUOTE(JSON_EXTRACT(jdata, '$.close_debit'));
SET actype = (SELECT M_AccountType FROM m_account WHERE M_AccountID = account_id);

IF open_credit IS NULL THEN SET open_credit = 0; ENd IF;
IF open_debit IS NULL THEN SET open_debit = 0; ENd IF;
IF close_credit IS NULL THEN SET close_credit = 0; ENd IF;
IF close_debit IS NULL THEN SET close_debit = 0; ENd IF;

SET balance_id = (SELECT T_BalanceID FROM t_balance WHERE T_BalanceM_AccountID = account_id AND T_BalanceYear = pyear AND T_BalanceIsActive = "Y");

-- IF actype = 'P' THEN
--    SET adata = JSON_ARRAY(JSON_OBJECT("account", account_modal_id, "debit", open_debit, "credit", open_credit), JSON_OBJECT("account", account_id, "debit", open_credit, "credit", open_debit));
-- ELSE
    SET adata = JSON_ARRAY(JSON_OBJECT("account", account_modal_id, "debit", open_credit, "credit", open_debit), JSON_OBJECT("account", account_id, "debit", open_debit, "credit", open_credit));
-- END IF;

IF balance_id IS NULL THEN
    SET bnumber = (SELECT fn_numbering('BAL'));
    INSERT INTO t_balance(T_BalanceNumber, T_BalanceM_AccountID, T_BalanceYear, T_BalanceOpenCredit, T_BalanceOpenDebit, T_BalanceCloseCredit, T_BalanceCloseDebit) SELECT bnumber, account_id, pyear, open_credit, open_debit, close_credit, close_debit;
    SET balance_id = (SELECT LAST_INSERT_ID());

    CALL sp_journal_save_notrans(0, CONCAT(pyear, "-01-01"), "", "", adata, "J.30", account_id );
    SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalMainM_AccountID = account_id AND T_JournalIsActive = "Y");

    UPDATE t_balance SET T_BalanceT_JournalID = journal_id WHERE T_BalanceID = balance_id;
ELSE
    UPDATE t_balance SET T_BalanceOpenCredit = open_credit, T_BalanceOpenDebit = open_debit, T_BalanceCloseCredit = close_credit, T_BalanceCloseDebit = close_debit WHERe T_BalanceID = balance_id;

    SET journal_id = (SELECT T_BalanceT_JournalID FROM t_balance WHERE T_BalanceID = balance_id);
    SET bnumber = (SELECT T_BalanceNumber FROM t_balance WHERE T_BalanceID = balance_id);
    CALL sp_journal_save_notrans(journal_id, CONCAT(pyear, "-01-01"), bnumber, "", adata, "J.30", account_id );

    IF (journal_id = 0) THEN
        SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalMainM_AccountID = account_id AND T_JournalIsActive = "Y");
        UPDATE t_balance SET T_BalanceT_JournalID = journal_id WHERE T_BalanceID = balance_id;
    END IF;
END IF;

SELECT "OK" status, JSON_OBJECT("balance_id", balance_id) data;

COMMIT;

END;;
DELIMITER ;