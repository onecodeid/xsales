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

SET journal_id = (SELECT F_MemoT_JournalID FROM f_memo WHERE F_MemoID = id);
UPDATE f_memo SET F_MemoIsActive = "N" WHERE F_MemoID = id;

UPDATE t_journaldetail SET T_JournalDetailIsActive = "N" WHERE T_JournalDetailT_JournalID = journal_id;
UPDATE t_journal SET T_JournalIsActive = "N" WHERE T_journalID = journal_id;

SELECT "OK" as status, JSON_OBJECT("memo_id", id, "journal_id", journal_id) as data;
COMMIT;

END