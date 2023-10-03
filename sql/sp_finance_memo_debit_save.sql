DROP PROCEDURE IF EXISTS `sp_finance_memo_debit_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_memo_debit_save` (IN `memo_id` int, IN `hdata` text, IN `uid` int)
BEGIN

DECLARE memo_date DATE;
DECLARE memo_number VARCHAR(50);
DECLARE memo_vendor INTEGER;
DECLARE memo_bill INTEGER DEFAULT 0;
DECLARE memo_amount DOUBLE;
DECLARE memo_note VARCHAR(255);
DECLARE memo_tags VARCHAR(155);
DECLARE memo_account INTEGER;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4) DEFAULT "J.19";
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
SET memo_vendor = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_vendor"));
SET memo_bill = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_bill"));
SET memo_amount = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_amount"));
SET memo_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_note"));
SET memo_tags = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_tags"));
SET memo_account = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo_account"));

IF memo_bill IS NULL THEN SET memo_bill = 0; END IF;
IF memo_account IS NULL THEN SET memo_account = 0; END IF;

SET account_dp_id = (SELECT fn_master_get_account_id("ACC.DP"));

IF memo_id = 0 THEN
    SET memo_number = (SELECT fn_numbering("FMEMOD"));

    INSERT INTO f_memodebit(
        F_MemoDebitDate, 
        F_MemoDebitNumber,
        F_MemoDebitM_VendorID,
        F_MemoDebitF_BillID,
        F_MemoDebitM_AccountID,
        F_MemoDebitAmount,
        F_MemoDebitNote,
        F_MemoDebitTags)
    SELECT memo_date, memo_number, memo_vendor, memo_bill, memo_account, memo_amount, memo_note, memo_tags;

    SET memo_id = (SELECT LAST_INSERT_ID());
ELSE

    UPDATE f_memodebit
    SET F_MemoDebitDate = memo_date, F_MemoDebitAmount = memo_amount, F_MemoDebitNote = memo_note, F_MemoDebitTags = memo_tags, F_MemoDebitM_AccountID = memo_account
    WHERE F_MemoDebitID = memo_id;

    SELECT F_MemoDebitNumber, F_MemoDebitM_VendorID INTO memo_number, memo_vendor from f_memodebit WHERE F_MemoDebitID = memo_id;
    SET journal_id = (SELECT F_MemoDebitT_JournalID from f_memodebit WHERE F_MemoDebitID = memo_id);
END IF;

-- JOURNAL
SET ledger = (SELECT CONCAT("Memo Debit ", M_VendorName, " #", memo_number) FROM m_vendor WHERE M_VendorID = memo_vendor);

SET adata = CONCAT(JSON_OBJECT("account", memo_account, "debit", 0, "credit", memo_amount, "ledger_note", ledger), ",",
            JSON_OBJECT("account", account_dp_id, "debit", memo_amount, "credit", 0, "ledger_note", ledger));
SET adata = CONCAT("[", adata, "]");

CALL sp_journal_save_notrans(journal_id, memo_date, memo_number, memo_note, adata, journal_type, account_dp_id );

IF journal_id = 0 THEN
    SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalType = journal_type AND T_JournalDate = memo_date AND (T_JournalIsActive = "Y" OR T_JournalIsActive = "T")
                    AND T_JournalMainM_AccountID = account_dp_id);
END IF;

UPDATE f_memodebit SET F_MemoDebitT_JournalID = journal_id WHERE F_MemoDebitID = memo_id;

SELECT "OK" as status, JSON_OBJECT("memo_id", memo_id, "memo_number", memo_number, "journal_id", journal_id) as data;
COMMIT;

END
;;
DELIMITER ;