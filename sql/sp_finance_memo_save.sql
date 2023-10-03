DROP PROCEDURE `sp_finance_memo_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_memo_save` (IN `memo_id` int, IN `hdata` text, IN `uid` int)
BEGIN

DECLARE memo_date DATE;
DECLARE memo_number VARCHAR(50);
DECLARE memo_customer INTEGER;
DECLARE memo_invoice INTEGER DEFAULT 0;
DECLARE memo_amount DOUBLE;
DECLARE memo_note VARCHAR(255);
DECLARE memo_tags VARCHAR(155);
DECLARE memo_account INTEGER;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4) DEFAULT "J.17";
DECLARE account_dp_id INTEGER;
DECLARE adata TEXT DEFAULT "";
DECLARE ledger TEXT DEFAULT "";

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME, @clm = COLUMN_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name, @clm as column_name;
ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME, @clm = COLUMN_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name, @clm as column_name;

ROLLBACK;
END;

START TRANSACTION;

SET memo_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_date"));
SET memo_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_customer"));
SET memo_invoice = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_invoice"));
SET memo_amount = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_amount"));
SET memo_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_note"));
SET memo_tags = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_tags"));
SET memo_account = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_account"));

IF memo_invoice IS NULL THEN SET memo_invoice = 0; END IF;
IF memo_account IS NULL THEN SET memo_account = 0; END IF;

SET account_dp_id = (SELECT fn_master_get_account_id("ACC.INCOME.PREPAID"));

IF memo_id = 0 THEN
    SET memo_number = (SELECT fn_numbering("FMEMO"));

    INSERT INTO f_memo(
        F_MemoDate, 
        F_MemoNumber,
        F_MemoM_CustomerID,
        F_MemoL_InvoiceID,
        F_MemoM_AccountID,
        F_MemoAmount,
        F_MemoNote,
        F_MemoTags)
    SELECT memo_date, memo_number, memo_customer, memo_invoice, memo_account, memo_amount, memo_note, memo_tags;

    SET memo_id = (SELECT LAST_INSERT_ID());
ELSE

    UPDATE f_memo
    SET F_MemoDate = memo_date, F_MemoAmount = memo_amount, F_MemoNote = memo_note, F_MemoTags = memo_tags, F_MemoM_AccountID = memo_account
    WHERE F_MemoID = memo_id;

    SET memo_number = (SELECT F_MemoNumber from f_memo WHERE F_MemoID = memo_id);
    SET journal_id = (SELECT F_MemoT_JournalID from f_memo WHERE F_MemoID = memo_id);
END IF;

-- JOURNAL
SET ledger = (SELECT CONCAT("Memo Kredit ", M_CustomerName, " #", memo_number) FROM m_customer WHERE M_CustomerID = memo_customer);

SET adata = CONCAT(JSON_OBJECT("account", memo_account, "debit", memo_amount, "credit", 0, "ledger_note", ledger), ",",
            JSON_OBJECT("account", account_dp_id, "debit", 0, "credit", memo_amount, "ledger_note", ledger));
SET adata = CONCAT("[", adata, "]");

CALL sp_journal_save_notrans(journal_id, memo_date, memo_number, memo_note, adata, journal_type, account_dp_id );

IF journal_id = 0 THEN
    SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalType = journal_type AND T_JournalDate = memo_date AND (T_JournalIsActive = "Y" OR T_JournalIsActive = "T")
                    AND T_JournalMainM_AccountID = account_dp_id);
END IF;

UPDATE f_memo SET F_MemoT_JournalID = journal_id WHERE F_MemoID = memo_id;

SELECT "OK" as status, JSON_OBJECT("memo_id", memo_id, "memo_number", memo_number, "journal_id", journal_id) as data;
COMMIT;

END
;;
DELIMITER ;