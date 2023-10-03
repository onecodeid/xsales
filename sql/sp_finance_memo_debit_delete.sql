DROP PROCEDURE `sp_finance_memo_debit_delete`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_memo_debit_delete` (IN `id` int, IN `uid` int)
BEGIN

DECLARE journal_id INTEGER;

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

SET journal_id = (SELECT F_MemoDebitT_JournalID FROM f_memodebit WHERE F_MemoDebitID = id);
UPDATE f_memodebit SET F_MemoDebitIsActive = "N" WHERE F_MemoDebitID = id;

UPDATE t_journaldetail SET T_JournalDetailIsActive = "N" WHERE T_JournalDetailT_JournalID = journal_id;
UPDATE t_journal SET T_JournalIsActive = "N" WHERE T_journalID = journal_id;

SELECT "OK" as status, JSON_OBJECT("memo_id", id, "journal_id", journal_id) as data;
COMMIT;

END;;
DELIMITER ;